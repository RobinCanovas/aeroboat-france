<?php
declare(strict_types=1);

/** Règles de rôle : admin = tout ; editor = articles, équipe, partenaires, modules. Comptes : pas d'auto-désactivation ni de dernier admin retiré. */
return static function (): array {
    require_once ROOT . '/app/Admin/Policy.php';
    require_once ROOT . '/app/Admin/Router.php';
    $P = \App\Admin\Policy::class;
    $errors = [];

    $editorOk = ['dashboard', 'profile', 'posts', 'team', 'partners', 'modules'];
    $adminOnly = ['applications', 'messages', 'users', 'purge'];
    foreach (array_merge($editorOk, $adminOnly) as $area) {
        if (!$P::allowed('admin', $area)) {
            $errors[] = "admin doit accéder à « $area »";
        }
    }
    foreach ($editorOk as $area) {
        if (!$P::allowed('editor', $area)) {
            $errors[] = "editor doit accéder à « $area »";
        }
    }
    foreach ($adminOnly as $area) {
        if ($P::allowed('editor', $area)) {
            $errors[] = "editor ne doit pas accéder à « $area »";
        }
    }
    if ($P::allowed(null, 'dashboard') || $P::allowed('visiteur', 'dashboard') || $P::allowed('admin', 'zone-inconnue')) {
        $errors[] = 'rôle absent/inconnu ou zone inconnue : accès refusé attendu';
    }

    // Comptes
    $adminRow = ['id' => 1, 'role' => 'admin', 'active' => 1];
    if ($P::accountChangeError(1, $adminRow, 'admin', 0, 2) === null) {
        $errors[] = 'se désactiver soi-même doit être refusé';
    }
    if ($P::accountChangeError(2, $adminRow, 'editor', 1, 1) === null) {
        $errors[] = 'retirer le rôle du dernier admin doit être refusé';
    }
    if ($P::accountChangeError(2, $adminRow, 'admin', 0, 1) === null) {
        $errors[] = 'désactiver le dernier admin doit être refusé';
    }
    if ($P::accountChangeError(2, $adminRow, 'editor', 1, 2) !== null) {
        $errors[] = 'retirer le rôle d\'un admin quand un autre reste actif doit être permis';
    }
    if ($P::accountChangeError(1, $adminRow, 'admin', 1, 1) !== null) {
        $errors[] = 'enregistrer un admin sans changement doit être permis';
    }
    if ($P::accountChangeError(1, ['id' => 3, 'role' => 'editor', 'active' => 1], 'editor', 0, 1) !== null) {
        $errors[] = 'désactiver un rédacteur doit être permis';
    }
    if ($P::accountChangeError(1, $adminRow, 'root', 1, 2) === null) {
        $errors[] = 'rôle inconnu refusé attendu';
    }

    // Analyse du chemin /admin* (avec et sans base)
    $R = \App\Admin\Router::class;
    $paths = [
        ['/admin', '', ''], ['/admin/', '', ''], ['/admin/articles/3/edit?x=1', '', '/articles/3/edit'],
        ['/test/admin/candidatures/', '/test', '/candidatures'], ['/test/admin', '/test', ''], ['/administrateur', '', null], ['/fr/projet', '', null],
    ];
    foreach ($paths as [$uri, $base, $want]) {
        if ($R::path($uri, $base) !== $want) {
            $errors[] = "chemin « $uri » (base « $base ») : attendu " . var_export($want, true) . ', obtenu ' . var_export($R::path($uri, $base), true);
        }
    }
    return $errors;
};
