<?php
declare(strict_types=1);

namespace App\Core;

/** Routes publiques localisées (app/routes.php). Génération d'URL absolues et résolution chemin → route. */
final class Router
{
    private static ?array $routes = null;
    private static ?string $currentKey = null;
    private static array $currentParams = [];
    /** @var array<string,array> paramètres propres à chaque langue (ex. slug FR / EN d'un article) */
    private static array $altParams = [];

    public static function routes(): array
    {
        if (self::$routes === null) {
            $routes = require Config::root() . '/app/routes.php';
            self::$routes = is_array($routes) ? $routes : [];
        }
        return self::$routes;
    }

    /** URL absolue (avec APP_URL) d'une route localisée. */
    public static function url(string $key, array $params = [], ?string $lang = null): string
    {
        $lang ??= Lang::current();
        $route = self::routes()[$key] ?? null;
        if ($route === null || !isset($route[$lang])) {
            throw new \InvalidArgumentException('Route inconnue : ' . $key);
        }
        $path = (string) preg_replace_callback('/\{([a-z_]+)\}/', static function (array $m) use ($params, $key): string {
            if (!isset($params[$m[1]]) || (string) $params[$m[1]] === '') {
                throw new \InvalidArgumentException("Paramètre {$m[1]} manquant pour la route $key");
            }
            return rawurlencode((string) $params[$m[1]]);
        }, (string) $route[$lang]);
        $used = [];
        preg_match_all('/\{([a-z_]+)\}/', (string) $route[$lang], $mm);
        $used = $mm[1];
        $query = array_diff_key($params, array_flip($used));
        $url = Config::get('app.url') . '/' . $lang . '/' . $path;
        if ($query !== []) {
            $url .= '?' . http_build_query($query);
        }
        return $url;
    }

    /**
     * Résout un chemin sans base (« /fr/projet »).
     * @return array{key:string,lang:string,params:array,action:string,methods:array}|null
     */
    public static function match(string $path): ?array
    {
        if (!preg_match('#^/(fr|en)(?:/(.*))?$#', $path, $m)) {
            return null;
        }
        $lang = $m[1];
        $rest = rtrim($m[2] ?? '', '/');
        foreach (self::routes() as $key => $route) {
            $pattern = (string) ($route[$lang] ?? '');
            $regex = '#^' . preg_replace('/\\\\\{([a-z_]+)\\\\\}/', '(?P<$1>[^/]+)', preg_quote($pattern, '#')) . '$#u';
            if (preg_match($regex, $rest, $pm)) {
                $params = array_filter($pm, 'is_string', ARRAY_FILTER_USE_KEY);
                return [
                    'key' => (string) $key,
                    'lang' => $lang,
                    'params' => $params,
                    'action' => (string) $route['action'],
                    'methods' => (array) ($route['methods'] ?? ['GET']),
                ];
            }
        }
        return null;
    }

    public static function setCurrent(?string $key, array $params = []): void
    {
        self::$currentKey = $key;
        self::$currentParams = $params;
        self::$altParams = [];
    }

    public static function current(): ?string
    {
        return self::$currentKey;
    }

    /** Paramètres spécifiques à une langue pour alternates() (ex. ['en' => ['slug' => 'my-post']]). */
    public static function setAlternateParams(string $lang, array $params): void
    {
        self::$altParams[$lang] = $params;
    }

    /** @return array<string,string> ['fr' => url, 'en' => url] pour la route courante */
    public static function alternates(): array
    {
        if (self::$currentKey === null) {
            $out = [];
            foreach (Lang::SUPPORTED as $l) {
                $out[$l] = self::url('home', [], $l);
            }
            return $out;
        }
        $out = [];
        foreach (Lang::SUPPORTED as $l) {
            try {
                $out[$l] = self::url(self::$currentKey, self::$altParams[$l] ?? self::$currentParams, $l);
            } catch (\InvalidArgumentException $e) {
                $out[$l] = self::url('home', [], $l);
            }
        }
        return $out;
    }
}
