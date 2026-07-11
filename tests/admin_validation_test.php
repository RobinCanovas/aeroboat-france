<?php
declare(strict_types=1);

/** Validation des champs, slugs, dates Paris/UTC, accès sûr aux CV. */
return static function (): array {
    foreach (['Slug', 'Time', 'Validator', 'CvFiles'] as $f) {
        require_once ROOT . "/app/Admin/$f.php";
    }
    $V = \App\Admin\Validator::class;
    $errors = [];

    // Slugs
    $S = \App\Admin\Slug::class;
    if ($S::make('Équipe & Vol n°2 : été 2026 !') !== 'equipe-vol-n-2-ete-2026') {
        $errors[] = 'slug : ' . $S::make('Équipe & Vol n°2 : été 2026 !');
    }
    if ($S::make('Œuvre — ÇA') !== 'oeuvre-ca' || $S::make('***') !== '') {
        $errors[] = 'slug : translittération ou chaîne vide';
    }
    if (!$S::isValid('mon-article-2') || $S::isValid('Mon article') || $S::isValid('-x') || $S::isValid('a--b')) {
        $errors[] = 'Slug::isValid incorrect';
    }

    // Heure de Paris → UTC (été +2 h, hiver +1 h)
    $T = \App\Admin\Time::class;
    if ($T::parisToUtc('2026-07-01T12:30') !== '2026-07-01 10:30:00') {
        $errors[] = 'Paris été → UTC : ' . var_export($T::parisToUtc('2026-07-01T12:30'), true);
    }
    if ($T::parisToUtc('2026-01-15 12:00') !== '2026-01-15 11:00:00') {
        $errors[] = 'Paris hiver → UTC : ' . var_export($T::parisToUtc('2026-01-15 12:00'), true);
    }
    if ($T::utcToParisInput('2026-07-01 10:30:00') !== '2026-07-01T12:30') {
        $errors[] = 'UTC → Paris (champ) : ' . $T::utcToParisInput('2026-07-01 10:30:00');
    }
    foreach (['', 'demain', '2026-13-40T10:00', '2026-02-30T10:00', '2026-07-01'] as $bad) {
        if ($T::parisToUtc($bad) !== null) {
            $errors[] = "date invalide acceptée : « $bad »";
        }
    }

    // Validator
    $fields = [
        'title' => ['type' => 'text', 'label' => 'Titre', 'required' => true, 'max' => 10],
        'mail' => ['type' => 'email', 'label' => 'Mail'],
        'site' => ['type' => 'url', 'label' => 'Site'],
        'rank' => ['type' => 'int', 'label' => 'Ordre', 'min' => 0, 'max' => 100, 'default' => 0],
        'role' => ['type' => 'select', 'label' => 'Rôle', 'options' => ['admin' => 'A', 'editor' => 'E']],
        'ok' => ['type' => 'bool', 'label' => 'Ok'],
        'when' => ['type' => 'datetime', 'label' => 'Quand'],
    ];
    [, $e] = $V::run($fields, []);
    if (!isset($e['title']) || count($e) !== 1) {
        $errors[] = 'seul « title » doit être obligatoire : ' . implode(',', array_keys($e));
    }
    [$v, $e] = $V::run($fields, ['title' => '  Bonjour  ', 'mail' => 'a@b.fr', 'site' => 'https://exemple.fr/x', 'rank' => '5', 'role' => 'editor', 'ok' => '1', 'when' => '2026-07-01T12:00']);
    if ($e !== [] || $v['title'] !== 'Bonjour' || $v['rank'] !== 5 || $v['ok'] !== 1) {
        $errors[] = 'saisie valide refusée ou mal normalisée : ' . json_encode($e);
    }
    $badInputs = [
        ['title' => 'trop long pour dix'], ['title' => 'x', 'mail' => 'pas-un-mail'],
        ['title' => 'x', 'site' => 'javascript:alert(1)'], ['title' => 'x', 'site' => 'ftp://exemple.fr'],
        ['title' => 'x', 'rank' => '101'], ['title' => 'x', 'rank' => '1.5'], ['title' => 'x', 'rank' => 'abc'],
        ['title' => 'x', 'role' => 'root'], ['title' => 'x', 'when' => 'hier'], ['title' => ['tableau']],
        ['title' => "a\0b"],
    ];
    foreach ($badInputs as $i => $in) {
        [, $e] = $V::run($fields, $in);
        if ($e === []) {
            $errors[] = 'entrée invalide acceptée #' . $i . ' : ' . json_encode($in);
        }
    }
    [$v] = $V::run($fields, ['title' => 'x']);
    if ($v['ok'] !== 0 || $v['rank'] !== 0) {
        $errors[] = 'case décochée = 0, entier vide = valeur par défaut';
    }

    // Mots de passe
    if ($V::passwordError('court') === null || $V::passwordError('douzecaracteres', 'autre-chose') === null) {
        $errors[] = 'mot de passe court ou confirmation différente acceptés';
    }
    if ($V::passwordError('un mot de passe solide', 'un mot de passe solide') !== null) {
        $errors[] = 'mot de passe valide refusé';
    }

    // CV : noms validés, pas d'évasion de dossier
    $C = \App\Admin\CvFiles::class;
    $dir = sys_get_temp_dir() . '/abf_cv_' . bin2hex(random_bytes(4));
    mkdir($dir);
    file_put_contents($dir . '/abc123.pdf', '%PDF-1.4');
    file_put_contents(dirname($dir) . '/secret.txt', 'x');
    if ($C::resolve($dir, 'abc123.pdf') === null) {
        $errors[] = 'CV valide non résolu';
    }
    foreach (['../secret.txt', '..\\secret.txt', 'a/b.pdf', '.htaccess', 'absent.pdf', '', 'abc123.pdf/../../x', "abc\0.pdf", str_repeat('a', 101)] as $evil) {
        if ($C::resolve($dir, $evil) !== null) {
            $errors[] = 'nom de CV dangereux accepté : ' . json_encode($evil);
        }
    }
    if (!$C::delete($dir, 'abc123.pdf') || is_file($dir . '/abc123.pdf')) {
        $errors[] = 'suppression de CV';
    }
    @unlink(dirname($dir) . '/secret.txt');
    @rmdir($dir);

    return $errors;
};
