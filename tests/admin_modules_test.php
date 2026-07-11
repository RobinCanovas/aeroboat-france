<?php
declare(strict_types=1);

/** Découverte des modules (contrat §7) : glob des fichiers module.php, filtrage par rôle, entrées invalides ignorées. */
return static function (): array {
    require_once ROOT . '/app/Admin/ModuleLoader.php';
    $L = \App\Admin\ModuleLoader::class;
    $errors = [];

    // Modules réels
    $all = $L::discover(ROOT . '/app/Modules');
    $keys = array_column($all, 'key');
    if (!in_array('calculator', $keys, true)) {
        $errors[] = 'le module Calculator doit être découvert';
    }
    foreach ($all as $m) {
        if (!str_starts_with($m['route'], '/admin/modules/')) {
            $errors[] = 'route de module hors /admin/modules/ : ' . $m['key'];
        }
        if (!class_exists($m['controller']) && !is_file(ROOT . '/app/' . str_replace(['App\\', '\\'], ['', '/'], $m['controller']) . '.php')) {
            $errors[] = 'contrôleur introuvable pour le module ' . $m['key'];
        }
    }
    $calc = $L::find(ROOT . '/app/Modules', 'calculator');
    if ($calc === null || $calc['roles'] !== ['admin', 'editor'] || $calc['method'] !== 'index') {
        $errors[] = 'définition du Calculator inattendue';
    }
    if ($L::find(ROOT . '/app/Modules', 'inexistant') !== null) {
        $errors[] = 'un module inconnu ne doit pas être trouvé';
    }

    // Fixtures : rôles, clés invalides, définitions incomplètes, tri par libellé
    $dir = sys_get_temp_dir() . '/abf_mod_' . bin2hex(random_bytes(4));
    $make = static function (string $name, string $php) use ($dir): void {
        mkdir($dir . '/' . $name, 0777, true);
        file_put_contents($dir . '/' . $name . '/module.php', $php);
    };
    $def = static fn (string $key, string $label, string $roles): string => "<?php return ['key'=>'$key','label'=>'$label','icon'=>'x','route'=>'/admin/modules/$key','roles'=>$roles,'controller'=>'X\\\\C','method'=>'index'];";
    $make('Zeta', $def('zeta', 'Zeta', "['admin']"));
    $make('Alpha', $def('alpha', 'Alpha', "['admin','editor']"));
    $make('Mauvais', "<?php return ['key'=>'sans-label'];");
    $make('CleInvalide', $def('Cle Invalide!', 'X', "['admin']"));
    $make('PasUnTableau', '<?php return 42;');

    $adm = array_column($L::discover($dir, 'admin'), 'key');
    $edi = array_column($L::discover($dir, 'editor'), 'key');
    $none = $L::discover($dir, 'visiteur');
    if ($adm !== ['alpha', 'zeta']) {
        $errors[] = 'admin doit voir alpha puis zeta (tri par libellé), obtenu ' . json_encode($adm);
    }
    if ($edi !== ['alpha']) {
        $errors[] = 'editor ne doit voir que alpha, obtenu ' . json_encode($edi);
    }
    if ($none !== []) {
        $errors[] = 'un rôle inconnu ne voit aucun module';
    }

    foreach (glob($dir . '/*/module.php') ?: [] as $f) {
        @unlink($f);
        @rmdir(dirname($f));
    }
    @rmdir($dir);
    return $errors;
};
