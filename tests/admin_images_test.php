<?php
declare(strict_types=1);

/** Suppression sûre des images téléversées : motif strict, confinement realpath, seed jamais supprimé. */
return static function (): array {
    require_once ROOT . '/app/Admin/ImageFiles.php';
    $I = \App\Admin\ImageFiles::class;
    $errors = [];

    $hex = str_repeat('a1', 16);
    foreach (["team/$hex.webp", "partners/$hex.webp", "posts/$hex.webp"] as $ok) {
        if (!$I::validValue($ok)) {
            $errors[] = "valeur légitime refusée : $ok";
        }
    }
    // Noms de base du seed, traversées, mauvais dossiers : jamais valides.
    foreach (['', 'water-run', 'partner-ionis', 'img/water-run-800.webp', "../team/$hex.webp", "team/../$hex.webp", "team/../../x/$hex.webp",
        "misc/$hex.webp", "team/$hex.png", "team/$hex.webp/", "/team/$hex.webp", "team\$hex.webp", "team/ABCD.webp", "team/$hex.webp\0"] as $bad) {
        if ($I::validValue($bad)) {
            $errors[] = 'valeur dangereuse acceptée : ' . json_encode($bad);
        }
    }
    if ($I::validValue(null)) {
        $errors[] = 'null ne doit pas être valide';
    }

    $tmp = sys_get_temp_dir() . '/abf_img_' . bin2hex(random_bytes(4));
    $uploads = $tmp . '/uploads';
    mkdir($uploads . '/team', 0775, true);
    mkdir($tmp . '/outside', 0775, true);
    $file = $uploads . "/team/$hex.webp";
    file_put_contents($file, 'x');
    $seedLike = $tmp . '/seed-water-run.webp';
    file_put_contents($seedLike, 'x');
    file_put_contents($tmp . "/outside/$hex.webp", 'x');

    if ($I::resolve($uploads, "team/$hex.webp") === null) {
        $errors[] = 'fichier existant non résolu';
    }
    if ($I::resolve($uploads, "partners/$hex.webp") !== null) {
        $errors[] = 'fichier absent résolu';
    }
    if ($I::delete($uploads, 'water-run') || $I::delete($uploads, "team/../../outside/$hex.webp") || !is_file($tmp . "/outside/$hex.webp")) {
        $errors[] = 'suppression hors périmètre ou nom du seed';
    }
    if (!$I::delete($uploads, "team/$hex.webp") || is_file($file)) {
        $errors[] = 'le fichier téléversé aurait dû être supprimé';
    }
    if ($I::delete($uploads, "team/$hex.webp")) {
        $errors[] = 'double suppression : doit retourner false';
    }
    if ($I::delete($uploads, null) || $I::delete($uploads, '')) {
        $errors[] = 'valeur vide : rien à supprimer';
    }

    // Dossier de sous-dossier remplacé par un lien : un chemin sortant du dossier d'uploads est refusé.
    $link = $uploads . '/posts';
    if (@symlink($tmp . '/outside', $link)) {
        if ($I::delete($uploads, "posts/$hex.webp") || !is_file($tmp . "/outside/$hex.webp")) {
            $errors[] = 'un lien symbolique permet de sortir du dossier d\'uploads';
        }
        @unlink($link);
    }

    // Nettoyage
    foreach ([$seedLike, $tmp . "/outside/$hex.webp"] as $f) {
        @unlink($f);
    }
    @rmdir($uploads . '/team');
    @rmdir($uploads);
    @rmdir($tmp . '/outside');
    @rmdir($tmp);

    // Décomptes de la purge du Core et type « restrict ».
    require_once ROOT . '/app/Admin/PurgeController.php';
    $c = \App\Admin\PurgeController::count(['cvs' => '3', 'messages' => 2], ['cv', 'cvs']);
    if ($c !== 3 || \App\Admin\PurgeController::count([], ['x']) !== 0 || \App\Admin\PurgeController::count(['x' => -4], ['x']) !== 0) {
        $errors[] = 'décompte de purge inattendu';
    }
    $src = (string) file_get_contents(ROOT . '/app/Admin/MessagesController.php');
    if (!str_contains($src, "'restrict' => 'Limitation'")) {
        $errors[] = 'type de demande « restrict » absent de l\'admin';
    }
    return $errors;
};
