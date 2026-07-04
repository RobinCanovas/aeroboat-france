<?php
declare(strict_types=1);

/**
 * Fonctions globales du contrat (CONTRACTS §4), utilisables dans les vues.
 */

use App\Core\Config;
use App\Core\Csrf;
use App\Core\Lang;
use App\Core\Request;
use App\Core\Router;
use App\Core\Security;

if (!function_exists('e')) {
    /** Échappement HTML (attributs compris). */
    function e($s): string
    {
        if ($s === null || is_array($s)) {
            return '';
        }
        return htmlspecialchars((string) $s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    /** Texte traduit brut (à échapper avec e() à la sortie). */
    function t(string $key, array $params = []): string
    {
        return Lang::t($key, $params);
    }

    /** HTML de confiance des clés *_html (paramètres échappés). Toute autre clé est échappée. */
    function t_html(string $key, array $params = []): string
    {
        if (!str_ends_with($key, '_html')) {
            return e(Lang::t($key, $params));
        }
        return Lang::t($key, array_map('e', $params));
    }

    function route(string $key, array $params = [], ?string $lang = null): string
    {
        return Router::url($key, $params, $lang);
    }

    /** Chemin interne (« /admin », « img/x.webp ») → URL absolue avec APP_URL. Un chemin déjà préfixé par la base n'est pas doublé. */
    function url(string $path = ''): string
    {
        if (preg_match('#^https?://#i', $path)) {
            return $path;
        }
        $base = Config::basePath();
        $path = '/' . ltrim($path, '/');
        if ($base !== '' && ($path === $base || str_starts_with($path, $base . '/'))) {
            $path = substr($path, strlen($base)) ?: '/';
        }
        return Config::get('app.url') . ($path === '/' ? '/' : $path);
    }

    /** Fichier de public/ (« img/logo-512.webp », « uploads/team/x.webp ») → URL absolue. */
    function asset(string $path): string
    {
        return url(ltrim($path, '/'));
    }

    /** Balises Vite : serveur de dev si VITE_DEV=1, sinon public/assets/.vite/manifest.json. */
    function vite_tags(string $entry): string
    {
        $entry = ltrim($entry, '/');
        $nonce = e(Security::nonce());
        if (Config::env('VITE_DEV', '0') === '1') {
            $origin = e(Security::viteOrigin());
            return '<script type="module" nonce="' . $nonce . '" src="' . $origin . '/@vite/client"></script>' . "\n"
                . '<script type="module" nonce="' . $nonce . '" src="' . $origin . '/' . e($entry) . '"></script>';
        }
        static $manifest = null;
        if ($manifest === null) {
            $file = Config::root() . '/public/assets/.vite/manifest.json';
            $manifest = is_file($file) ? (json_decode((string) file_get_contents($file), true) ?: []) : [];
        }
        $chunk = $manifest[$entry] ?? null;
        if (!is_array($chunk)) {
            return '<!-- vite: entrée absente ' . e($entry) . ' -->';
        }
        $html = [];
        $css = [];
        $preload = [];
        $walk = static function (array $c, bool $root) use (&$walk, &$css, &$preload, $manifest): void {
            foreach ($c['css'] ?? [] as $f) {
                $css[$f] = true;
            }
            foreach ($c['imports'] ?? [] as $imp) {
                if (isset($manifest[$imp]['file']) && !isset($preload[$manifest[$imp]['file']])) {
                    $preload[$manifest[$imp]['file']] = true;
                    $walk($manifest[$imp], false);
                }
            }
        };
        $walk($chunk, true);
        foreach (array_keys($css) as $f) {
            $html[] = '<link rel="stylesheet" href="' . e(asset('assets/' . $f)) . '">';
        }
        $file = (string) ($chunk['file'] ?? '');
        if (str_ends_with($file, '.css')) {
            $html[] = '<link rel="stylesheet" href="' . e(asset('assets/' . $file)) . '">';
        } elseif ($file !== '') {
            foreach (array_keys($preload) as $f) {
                $html[] = '<link rel="modulepreload" href="' . e(asset('assets/' . $f)) . '">';
            }
            $html[] = '<script type="module" nonce="' . $nonce . '" src="' . e(asset('assets/' . $file)) . '"></script>';
        }
        return implode("\n", $html);
    }

    /**
     * <img srcset> depuis public/img/manifest.json (slug → largeurs). Attributs style / on* refusés (CSP).
     * Par défaut : loading="lazy", decoding="async" ; surcharger avec $attrs (ex. ['loading' => 'eager', 'class' => 'hero__img']).
     */
    function img_tag(string $slug, string $alt, string $sizes = '100vw', array $attrs = []): string
    {
        static $manifest = null;
        if ($manifest === null) {
            $file = Config::root() . '/public/img/manifest.json';
            $manifest = is_file($file) ? (json_decode((string) file_get_contents($file), true) ?: []) : [];
        }
        $variants = $manifest[$slug] ?? null;
        if (!is_array($variants) || $variants === []) {
            return '';
        }
        ksort($variants, SORT_NUMERIC);
        $srcset = [];
        $largest = null;
        foreach ($variants as $w => $v) {
            $srcset[] = asset('img/' . $slug . '-' . $w . '.webp') . ' ' . (int) ($v['w'] ?? $w) . 'w';
            $largest = $v + ['key' => $w];
        }
        $first = array_key_first($variants);
        $base = [
            'src' => asset('img/' . $slug . '-' . $first . '.webp'),
            'srcset' => implode(', ', $srcset),
            'sizes' => $sizes,
            'width' => (string) (int) ($largest['w'] ?? 0),
            'height' => (string) (int) ($largest['h'] ?? 0),
            'alt' => $alt,
            'loading' => 'lazy',
            'decoding' => 'async',
        ];
        foreach ($attrs as $k => $v) {
            $k = strtolower((string) $k);
            if (!preg_match('/^[a-z][a-z0-9\-]*$/', $k) || $k === 'style' || str_starts_with($k, 'on')) {
                continue;
            }
            if ($v === null || $v === false) {
                unset($base[$k]);
                continue;
            }
            $base[$k] = $v === true ? $k : (string) $v;
        }
        if (count($variants) === 1) {
            unset($base['srcset'], $base['sizes']);
        }
        $html = '<img';
        foreach ($base as $k => $v) {
            $html .= ' ' . $k . '="' . e($v) . '"';
        }
        return $html . '>';
    }

    /** Champ localisé : $row[field_lang], repli sur _fr. */
    function loc(array $row, string $field): string
    {
        $v = $row[$field . '_' . Lang::current()] ?? null;
        if ($v === null || $v === '') {
            $v = $row[$field . '_fr'] ?? '';
        }
        return (string) $v;
    }

    /** Jeton HMAC + pot de miel « website » (masqué par l'attribut hidden, hors parcours clavier). */
    function csrf_field(string $form): string
    {
        $id = 'website-' . preg_replace('/[^a-z0-9_-]/i', '', $form);
        return '<input type="hidden" name="_token" value="' . e(Csrf::formToken($form)) . '">'
            . '<div class="form-hp" hidden><label for="' . e($id) . '">' . e(t('forms.honeypot_label')) . '</label>'
            . '<input type="text" name="website" id="' . e($id) . '" value="" tabindex="-1" autocomplete="off"></div>';
    }

    /** Jeton de session (admin) : champ « _token », à vérifier avec Csrf::verifySession(Request::input('_token')). */
    function csrf_session_field(): string
    {
        return '<input type="hidden" name="_token" value="' . e(Csrf::sessionToken()) . '">';
    }

    function csp_nonce(): string
    {
        return Security::nonce();
    }

    /** Valeur soumise (POST) pour préremplir un formulaire après erreur ; « » sinon. */
    function old(string $k): string
    {
        if (Request::method() !== 'POST' || in_array($k, ['_token', 'website'], true)) {
            return '';
        }
        $v = $_POST[$k] ?? '';
        return is_string($v) ? $v : '';
    }

    function lang(): string
    {
        return Lang::current();
    }

    function is_test(): bool
    {
        return Config::isTest();
    }
}
