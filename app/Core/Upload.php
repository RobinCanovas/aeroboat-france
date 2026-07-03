<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Téléversements. Les exceptions UploadError portent une clé de langue (forms.errors.*) comme message.
 * - image() : type réel vérifié par GD, ré-encodage WebP (EXIF et métadonnées supprimés), redimensionnement, nom aléatoire.
 * - pdf()   : signature %PDF-, 5 Mo max, stocké hors web dans storage/cv/.
 */
final class Upload
{
    public const IMAGE_MAX_BYTES = 10 * 1024 * 1024;
    public const IMAGE_MAX_PIXELS = 40000000;
    public const PDF_MAX_BYTES = 5 * 1024 * 1024;

    public static function uploadsDir(): string
    {
        return Config::root() . '/public/uploads';
    }

    public static function cvDir(): string
    {
        return Config::root() . '/storage/cv';
    }

    /** @return string chemin relatif à public/uploads/ (ex. « team/3f…a9.webp ») */
    public static function image(array $file, string $subdir, int $maxWidth = 1600): string
    {
        $tmp = self::checkFile($file, self::IMAGE_MAX_BYTES, 'forms.errors.image_too_large');
        if (!function_exists('imagewebp')) {
            throw new UploadError('forms.errors.generic');
        }
        $subdir = trim(strtolower($subdir), '/');
        if (!preg_match('#^[a-z0-9_-]+(?:/[a-z0-9_-]+)*$#', $subdir)) {
            throw new \InvalidArgumentException('Sous-dossier invalide.');
        }
        $info = @getimagesize($tmp);
        if ($info === false || $info[0] < 1 || $info[1] < 1 || $info[0] * $info[1] > self::IMAGE_MAX_PIXELS) {
            throw new UploadError('forms.errors.image_invalid');
        }
        $src = null;
        switch ($info[2]) {
            case IMAGETYPE_JPEG:
                $src = @imagecreatefromjpeg($tmp);
                break;
            case IMAGETYPE_PNG:
                $src = @imagecreatefrompng($tmp);
                break;
            case IMAGETYPE_WEBP:
                $src = function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($tmp) : false;
                break;
            case IMAGETYPE_GIF:
                $src = @imagecreatefromgif($tmp);
                break;
        }
        if (!$src instanceof \GdImage) {
            throw new UploadError('forms.errors.image_invalid');
        }
        if ($info[2] === IMAGETYPE_JPEG) {
            $src = self::orient($src, $tmp);
        }
        if (!imageistruecolor($src)) {
            imagepalettetotruecolor($src);
        }
        $w = imagesx($src);
        $h = imagesy($src);
        $maxWidth = max(16, $maxWidth);
        if ($w > $maxWidth) {
            $nh = max(1, (int) round($h * $maxWidth / $w));
            $dst = imagecreatetruecolor($maxWidth, $nh);
            imagealphablending($dst, false);
            imagesavealpha($dst, true);
            imagecopyresampled($dst, $src, 0, 0, 0, 0, $maxWidth, $nh, $w, $h);
            $src = $dst;
        } else {
            imagealphablending($src, false);
            imagesavealpha($src, true);
        }
        $dir = self::uploadsDir() . '/' . $subdir;
        if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) {
            throw new UploadError('forms.errors.generic');
        }
        $name = bin2hex(random_bytes(16)) . '.webp';
        $ok = imagewebp($src, $dir . '/' . $name, 82);
        if (!$ok) {
            throw new UploadError('forms.errors.generic');
        }
        return $subdir . '/' . $name;
    }

    /** @return string nom du fichier dans storage/cv/ */
    public static function pdf(array $file): string
    {
        $tmp = self::checkFile($file, self::PDF_MAX_BYTES, 'forms.errors.pdf_too_large');
        $fh = fopen($tmp, 'rb');
        $head = $fh ? (string) fread($fh, 1024) : '';
        if ($fh) {
            fclose($fh);
        }
        if (!str_starts_with($head, '%PDF-')) {
            throw new UploadError('forms.errors.pdf_invalid');
        }
        if (function_exists('finfo_open')) {
            $fi = finfo_open(FILEINFO_MIME_TYPE);
            $mime = $fi ? (string) finfo_file($fi, $tmp) : '';
            if ($mime !== '' && $mime !== 'application/pdf') {
                throw new UploadError('forms.errors.pdf_invalid');
            }
        }
        $dir = self::cvDir();
        if (!is_dir($dir)) {
            mkdir($dir, 0700, true);
        }
        $name = bin2hex(random_bytes(16)) . '.pdf';
        $dest = $dir . '/' . $name;
        $moved = is_uploaded_file($tmp) ? move_uploaded_file($tmp, $dest) : (PHP_SAPI === 'cli' && copy($tmp, $dest));
        if (!$moved) {
            throw new UploadError('forms.errors.generic');
        }
        @chmod($dest, 0600);
        return $name;
    }

    private static function checkFile(array $file, int $maxBytes, string $tooLargeKey): string
    {
        $err = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);
        if ($err === UPLOAD_ERR_INI_SIZE || $err === UPLOAD_ERR_FORM_SIZE) {
            throw new UploadError($tooLargeKey);
        }
        if ($err !== UPLOAD_ERR_OK) {
            throw new UploadError('forms.errors.generic');
        }
        $tmp = (string) ($file['tmp_name'] ?? '');
        // Hors CLI, seul un fichier réellement téléversé est accepté.
        if ($tmp === '' || !is_file($tmp) || (!is_uploaded_file($tmp) && PHP_SAPI !== 'cli')) {
            throw new UploadError('forms.errors.generic');
        }
        $size = (int) filesize($tmp);
        if ($size <= 0) {
            throw new UploadError('forms.errors.generic');
        }
        if ($size > $maxBytes) {
            throw new UploadError($tooLargeKey);
        }
        return $tmp;
    }

    private static function orient(\GdImage $img, string $file): \GdImage
    {
        if (!function_exists('exif_read_data')) {
            return $img;
        }
        $exif = @exif_read_data($file);
        $o = is_array($exif) ? (int) ($exif['Orientation'] ?? 1) : 1;
        $angle = [3 => 180, 6 => -90, 8 => 90][$o] ?? 0;
        if ($angle !== 0) {
            $rot = imagerotate($img, $angle, 0);
            if ($rot instanceof \GdImage) {
                return $rot;
            }
        }
        return $img;
    }
}
