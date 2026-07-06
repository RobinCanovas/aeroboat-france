<?php
declare(strict_types=1);

/**
 * Petits utilitaires d'affichage pour les vues publiques (aucune logique métier).
 * Chargé par `require_once` depuis le gabarit et les vues qui en ont besoin.
 * Ne dépend que du contrat : t(), e(), asset(), url(), lang() et App\Core\Lang::has().
 */

if (!function_exists('ui_has')) {
    /** La clé de langue existe-t-elle ? (sans journaliser de clé manquante) */
    function ui_has(string $key): bool
    {
        return \App\Core\Lang::has($key);
    }
}

if (!function_exists('ui_list')) {
    /**
     * Lit une liste d'objets de langue (home.cards, home.timeline.items…) :
     * `ui_list('home.cards', ['title','text'])` => [['title'=>…,'text'=>…], …]
     * S'arrête au premier index absent. Aucune clé n'est inventée : tout vient de fr.php / en.php.
     *
     * @param list<string> $fields
     * @return list<array<string,string>>
     */
    function ui_list(string $key, array $fields, int $max = 24): array
    {
        $rows = [];
        for ($i = 0; $i < $max; $i++) {
            if (!ui_has($key . '.' . $i . '.' . $fields[0])) {
                break;
            }
            $row = [];
            foreach ($fields as $f) {
                $k = $key . '.' . $i . '.' . $f;
                $row[$f] = ui_has($k) ? t($k) : '';
            }
            $rows[] = $row;
        }
        return $rows;
    }
}

if (!function_exists('ui_img_full')) {
    /** URL de la plus grande variante WebP d'un slug de public/img/manifest.json (cible des vignettes). */
    function ui_img_full(string $slug): string
    {
        static $manifest = null;
        if ($manifest === null) {
            $file = dirname(__DIR__, 2) . '/public/img/manifest.json';
            $json = is_file($file) ? json_decode((string) file_get_contents($file), true) : null;
            $manifest = is_array($json) ? $json : [];
        }
        $widths = array_map('intval', array_keys($manifest[$slug] ?? []));
        if ($widths === []) {
            return asset('img/' . $slug . '.webp');
        }
        sort($widths);
        return asset('img/' . $slug . '-' . end($widths) . '.webp');
    }
}

if (!function_exists('ui_upload')) {
    /** URL d'un fichier téléversé (chemin relatif à public/uploads/). */
    function ui_upload(string $path): string
    {
        return url('/uploads/' . ltrim($path, '/'));
    }
}

if (!function_exists('ui_is_slug')) {
    /** Une valeur de base (photo, logo) est un slug d'image du manifeste si elle n'a ni « / » ni extension. */
    function ui_is_slug(string $v): bool
    {
        return $v !== '' && !str_contains($v, '/') && !str_contains($v, '.');
    }
}

if (!function_exists('ui_date')) {
    /** Date lisible selon la langue (le texte « Publié le » vient de t('common.published_on')). */
    function ui_date(?string $utc): string
    {
        if ($utc === null || $utc === '') {
            return '';
        }
        $ts = strtotime($utc . ' UTC');
        if ($ts === false) {
            return '';
        }
        return lang() === 'fr' ? date('d/m/Y', $ts) : date('j M Y', $ts);
    }
}

if (!function_exists('ui_status')) {
    /** Normalise un statut de timeline. */
    function ui_status(string $s): string
    {
        return in_array($s, ['done', 'current', 'planned'], true) ? $s : 'planned';
    }
}

if (!function_exists('ui_icon')) {
    /** Icônes SVG inline (décoratives : aria-hidden). Aucun fichier externe. */
    function ui_icon(string $name): string
    {
        $paths = [
            'done' => '<circle cx="12" cy="12" r="9" fill="none" stroke="currentColor" stroke-width="2"/><path d="M8 12.5l2.6 2.6L16 9.6" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>',
            'current' => '<circle cx="12" cy="12" r="9" fill="none" stroke="currentColor" stroke-width="2"/><path d="M12 7v5l3.2 2" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>',
            'planned' => '<circle cx="12" cy="12" r="9" fill="none" stroke="currentColor" stroke-width="2" stroke-dasharray="3.2 3.2"/><circle cx="12" cy="12" r="2" fill="currentColor"/>',
            'arrow' => '<path d="M5 12h14M13 6l6 6-6 6" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>',
            'alert' => '<circle cx="12" cy="12" r="9" fill="none" stroke="currentColor" stroke-width="2"/><path d="M12 7.5v5.5M12 16.2v.3" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"/>',
            'instagram' => '<rect x="3.5" y="3.5" width="17" height="17" rx="5" fill="none" stroke="currentColor" stroke-width="2"/><circle cx="12" cy="12" r="4" fill="none" stroke="currentColor" stroke-width="2"/><circle cx="17.2" cy="6.8" r="1.2" fill="currentColor"/>',
            'linkedin' => '<rect x="3.5" y="3.5" width="17" height="17" rx="3" fill="none" stroke="currentColor" stroke-width="2"/><path d="M8 10.5V16M8 7.7v.1M11.5 16v-5.5M11.5 13c0-1.6 1-2.6 2.4-2.6s2.1 1 2.1 2.6V16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>',
        ];
        $p = $paths[$name] ?? '';
        return '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">' . $p . '</svg>';
    }
}

if (!function_exists('ui_status_badge')) {
    /** Pastille de statut : icône + texte (jamais la couleur seule). */
    function ui_status_badge(string $status): string
    {
        $s = ui_status($status);
        return '<span class="status status--' . $s . '">' . ui_icon($s) . '<span>' . e(t('common.status.' . $s)) . '</span></span>';
    }
}

if (!function_exists('ui_initials')) {
    function ui_initials(string $name): string
    {
        $out = '';
        foreach (preg_split('/[\s\-]+/u', trim($name)) ?: [] as $part) {
            if ($part !== '') {
                $out .= mb_strtoupper(mb_substr($part, 0, 1));
            }
            if (mb_strlen($out) >= 2) {
                break;
            }
        }
        return $out;
    }
}
