<?php
declare(strict_types=1);

/** Routes FR/EN : génération (base /test), résolution, aller-retour, alternates, chemins de base, .env. */
return static function (): array {
    require_once ROOT . '/app/bootstrap.php';
    $errors = [];
    App\Core\Config::set('APP_URL', 'https://example.test/test/');
    $R = App\Core\Router::class;

    $expect = [
        ['home', [], 'fr', 'https://example.test/test/fr/'],
        ['project', [], 'fr', 'https://example.test/test/fr/projet'],
        ['project', [], 'en', 'https://example.test/test/en/project'],
        ['rights', [], 'en', 'https://example.test/test/en/exercise-my-rights'],
        ['news_show', ['slug' => 'mon-article'], 'fr', 'https://example.test/test/fr/actualites/mon-article'],
        ['news', ['page' => 2], 'en', 'https://example.test/test/en/news?page=2'],
    ];
    foreach ($expect as [$key, $params, $lang, $url]) {
        $got = $R::url($key, $params, $lang);
        if ($got !== $url) {
            $errors[] = "url($key, $lang) = $got (attendu $url)";
        }
    }

    // Aller-retour : chaque route, chaque langue.
    foreach ($R::routes() as $key => $route) {
        foreach (['fr', 'en'] as $lang) {
            $params = str_contains((string) $route[$lang], '{slug}') ? ['slug' => 'abc-1'] : [];
            $path = substr($R::url((string) $key, $params, $lang), strlen('https://example.test/test'));
            $m = $R::match($path);
            if ($m === null || $m['key'] !== $key || $m['lang'] !== $lang || ($params && $m['params'] !== $params)) {
                $errors[] = "match($path) ne retrouve pas $key";
            }
        }
    }
    foreach (['/fr/inconnu', '/de/project', '/en/projet', '/fr/actualites/a/b', '/fr/../app'] as $bad) {
        if ($R::match($bad) !== null) {
            $errors[] = "match($bad) devrait échouer";
        }
    }
    if (($R::match('/fr/projet/')['key'] ?? '') !== 'project') {
        $errors[] = 'slash final non toléré par match()';
    }

    $R::setCurrent('news_show', ['slug' => 'article-fr']);
    $R::setAlternateParams('en', ['slug' => 'post-en']);
    $alt = $R::alternates();
    if (($alt['fr'] ?? '') !== 'https://example.test/test/fr/actualites/article-fr' || ($alt['en'] ?? '') !== 'https://example.test/test/en/news/post-en') {
        $errors[] = 'alternates() incorrect : ' . json_encode($alt);
    }
    $R::setCurrent(null);

    // Base de chemin et helpers
    if (App\Core\Config::basePath() !== '/test') {
        $errors[] = 'basePath() incorrect';
    }
    $_SERVER['REQUEST_URI'] = '/test/en/project?x=1';
    if (App\Core\Request::path() !== '/en/project') {
        $errors[] = 'Request::path() ne retire pas la base';
    }
    if (url('/admin') !== 'https://example.test/test/admin' || url('/test/admin') !== 'https://example.test/test/admin' || asset('img/a.webp') !== 'https://example.test/test/img/a.webp') {
        $errors[] = 'url()/asset() incorrects';
    }
    $_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'de-DE,en;q=0.8,fr;q=0.5';
    if (App\Core\Request::preferredLang() !== 'en') {
        $errors[] = 'Accept-Language mal interprété';
    }
    $_SERVER['HTTP_ACCEPT_LANGUAGE'] = '';
    if (App\Core\Request::preferredLang() !== 'fr') {
        $errors[] = 'langue par défaut ≠ fr';
    }

    // Chargeur .env
    $env = App\Core\Config::parse("A=1 # commentaire\nB=\"x # y\"\nC=a#b\n# rien\nD='z'\nE=\n export F=ok");
    if ($env !== ['A' => '1', 'B' => 'x # y', 'C' => 'a#b', 'D' => 'z', 'E' => '', 'F' => 'ok']) {
        $errors[] = 'parse .env incorrect : ' . json_encode($env);
    }

    // img_tag : srcset, dimensions, attributs dangereux ignorés
    $img = img_tag('lake-alpine', 'Lac "alpin"', '100vw', ['onclick' => 'x', 'style' => 'x', 'class' => 'hero']);
    if (!str_contains($img, 'srcset=') || !str_contains($img, 'width="1400"') || str_contains($img, 'onclick') || str_contains($img, 'style=') || !str_contains($img, '&quot;alpin&quot;')) {
        $errors[] = 'img_tag() incorrect : ' . $img;
    }
    if (e('<a href="x">\'') !== '&lt;a href=&quot;x&quot;&gt;&#039;') {
        $errors[] = 'e() incorrect';
    }
    App\Core\Config::set('APP_URL', null);
    return $errors;
};
