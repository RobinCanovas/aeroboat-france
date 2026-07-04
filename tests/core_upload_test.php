<?php
declare(strict_types=1);

/** Upload : image ré-encodée en WebP redimensionnée (si GD), faux fichiers refusés, PDF vérifié. Nettoie ses fichiers. */
return static function (): array {
    require_once ROOT . '/app/bootstrap.php';
    $errors = [];
    $tmpDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'abf-up-' . bin2hex(random_bytes(4));
    mkdir($tmpDir);
    $created = [];
    $fileArr = static fn (string $path): array => ['name' => basename($path), 'type' => 'x', 'tmp_name' => $path, 'error' => UPLOAD_ERR_OK, 'size' => filesize($path)];

    try {
        if (function_exists('imagewebp') && function_exists('imagejpeg')) {
            $im = imagecreatetruecolor(2000, 1000);
            imagefill($im, 0, 0, (int) imagecolorallocate($im, 30, 90, 160));
            $jpg = $tmpDir . '/photo.jpg';
            imagejpeg($im, $jpg, 85);
            $rel = App\Core\Upload::image($fileArr($jpg), 'test-core', 800);
            $out = App\Core\Upload::uploadsDir() . '/' . $rel;
            $created[] = $out;
            $info = @getimagesize($out);
            if (!preg_match('#^test-core/[a-f0-9]{32}\.webp$#', $rel) || $info === false || $info[2] !== IMAGETYPE_WEBP || $info[0] !== 800 || $info[1] !== 400) {
                $errors[] = 'image non ré-encodée / redimensionnée : ' . $rel;
            }
            if ($info !== false && str_contains((string) file_get_contents($out), 'Exif')) {
                $errors[] = 'métadonnées EXIF conservées';
            }
            $fake = $tmpDir . '/shell.jpg';
            file_put_contents($fake, "<?php echo 'x'; ?>");
            try {
                App\Core\Upload::image($fileArr($fake), 'test-core');
                $errors[] = 'faux JPEG (PHP) accepté';
            } catch (App\Core\UploadError $e) {
            }
            try {
                App\Core\Upload::image($fileArr($jpg), '../evil');
                $errors[] = 'sous-dossier ../ accepté';
            } catch (InvalidArgumentException $e) {
            }
        }

        $pdf = $tmpDir . '/cv.pdf';
        file_put_contents($pdf, "%PDF-1.4\n1 0 obj<<>>endobj\ntrailer<<>>\n%%EOF\n");
        $name = App\Core\Upload::pdf($fileArr($pdf));
        $created[] = App\Core\Upload::cvDir() . '/' . $name;
        if (!preg_match('/^[a-f0-9]{32}\.pdf$/', $name) || !is_file(App\Core\Upload::cvDir() . '/' . $name)) {
            $errors[] = 'PDF non stocké : ' . $name;
        }
        $notPdf = $tmpDir . '/cv2.pdf';
        file_put_contents($notPdf, '<html>pas un pdf</html>');
        try {
            App\Core\Upload::pdf($fileArr($notPdf));
            $errors[] = 'faux PDF accepté';
        } catch (App\Core\UploadError $e) {
            if ($e->getMessage() !== 'forms.errors.pdf_invalid') {
                $errors[] = 'clé d\'erreur PDF inattendue';
            }
        }
        $big = $tmpDir . '/big.pdf';
        file_put_contents($big, '%PDF-' . str_repeat('0', App\Core\Upload::PDF_MAX_BYTES));
        try {
            App\Core\Upload::pdf($fileArr($big));
            $errors[] = 'PDF > 5 Mo accepté';
        } catch (App\Core\UploadError $e) {
            if ($e->getMessage() !== 'forms.errors.pdf_too_large') {
                $errors[] = 'clé d\'erreur taille inattendue';
            }
        }
        try {
            App\Core\Upload::pdf(['tmp_name' => '', 'error' => UPLOAD_ERR_PARTIAL]);
            $errors[] = 'téléversement partiel accepté';
        } catch (App\Core\UploadError $e) {
        }
    } catch (Throwable $e) {
        $errors[] = get_class($e) . ' : ' . $e->getMessage();
    } finally {
        foreach ($created as $f) {
            @unlink($f);
        }
        @rmdir(App\Core\Upload::uploadsDir() . '/test-core');
        array_map('unlink', glob($tmpDir . '/*') ?: []);
        @rmdir($tmpDir);
    }
    return $errors;
};
