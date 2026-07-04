<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Markdown maison, sûr par construction : tout le texte source est échappé, seules les balises produites ici sortent.
 * Blocs : titres (# → h2 … ###### → h6), listes à puces / numérotées, citations, blocs de code ```, règle ---, paragraphes.
 * En ligne : `code`, **gras**, *italique* / _italique_, [lien](url), ![image](chemin relatif).
 * Liens : http(s), mailto, chemins relatifs ou ancres uniquement ; images : chemins relatifs uniquement.
 */
final class Markdown
{
    public static function render(string $md): string
    {
        $md = str_replace(["\r\n", "\r"], "\n", $md);
        $md = (string) preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $md);
        return self::blocks(explode("\n", $md));
    }

    /** @param string[] $lines */
    private static function blocks(array $lines): string
    {
        $out = [];
        $n = count($lines);
        $i = 0;
        while ($i < $n) {
            $line = $lines[$i];
            $trim = trim($line);
            if ($trim === '') {
                $i++;
                continue;
            }
            if (preg_match('/^```\s*([A-Za-z0-9_+-]*)\s*$/', $trim, $m)) {
                $code = [];
                $i++;
                while ($i < $n && !preg_match('/^```\s*$/', trim($lines[$i]))) {
                    $code[] = $lines[$i];
                    $i++;
                }
                $i++;
                $cls = $m[1] !== '' ? ' class="language-' . e(strtolower($m[1])) . '"' : '';
                $out[] = '<pre><code' . $cls . '>' . e(implode("\n", $code)) . '</code></pre>';
                continue;
            }
            if (preg_match('/^(#{1,6})\s+(.+?)\s*#*\s*$/', $trim, $m)) {
                $level = max(2, strlen($m[1]));
                $out[] = "<h$level>" . self::inline($m[2]) . "</h$level>";
                $i++;
                continue;
            }
            if (preg_match('/^(?:-{3,}|\*{3,}|_{3,})$/', str_replace(' ', '', $trim))) {
                $out[] = '<hr>';
                $i++;
                continue;
            }
            if (str_starts_with($trim, '>')) {
                $quote = [];
                while ($i < $n && str_starts_with(trim($lines[$i]), '>')) {
                    $quote[] = (string) preg_replace('/^\s*>\s?/', '', $lines[$i]);
                    $i++;
                }
                $out[] = '<blockquote>' . self::blocks($quote) . '</blockquote>';
                continue;
            }
            if (preg_match('/^\s*([-*+]|\d{1,9}[.)])\s+/', $line, $m)) {
                $ordered = !in_array($m[1], ['-', '*', '+'], true);
                $re = $ordered ? '/^\s*\d{1,9}[.)]\s+(.*)$/' : '/^\s*[-*+]\s+(.*)$/';
                $items = [];
                while ($i < $n && trim($lines[$i]) !== '') {
                    if (preg_match($re, $lines[$i], $im)) {
                        $items[] = $im[1];
                    } elseif ($items !== [] && preg_match('/^\s+\S/', $lines[$i])) {
                        $items[count($items) - 1] .= ' ' . trim($lines[$i]);
                    } else {
                        break;
                    }
                    $i++;
                }
                $tag = $ordered ? 'ol' : 'ul';
                $out[] = "<$tag>" . implode('', array_map(static fn ($it) => '<li>' . self::inline($it) . '</li>', $items)) . "</$tag>";
                continue;
            }
            $para = [];
            while ($i < $n && trim($lines[$i]) !== '' && !self::startsBlock($lines[$i])) {
                $para[] = trim($lines[$i]);
                $i++;
            }
            if ($para === []) {
                $para[] = $trim;
                $i++;
            }
            $out[] = '<p>' . implode("<br>\n", array_map([self::class, 'inline'], $para)) . '</p>';
        }
        return implode("\n", $out);
    }

    private static function startsBlock(string $line): bool
    {
        $t = trim($line);
        return (bool) preg_match('/^(#{1,6}\s|```|>|[-*+]\s|\d{1,9}[.)]\s)/', $t)
            || (bool) preg_match('/^(?:-{3,}|\*{3,}|_{3,})$/', str_replace(' ', '', $t));
    }

    /** Analyse en ligne sur le texte brut ; chaque segment non reconnu est échappé. */
    public static function inline(string $s): string
    {
        $pattern = '/(?<code>`+)(?<codetext>.+?)\k<code>'
            . '|!\[(?<alt>[^\]\n]*)\]\((?<src>[^()\s]+)\)'
            . '|\[(?<text>[^\]\n]+)\]\((?<href>[^()\s]+)\)'
            . '|\*\*(?<bold>[^\s*](?:.*?[^\s*])?)\*\*'
            . '|(?<![\w*])\*(?<em>[^\s*](?:.*?[^\s*])?)\*(?![\w*])'
            . '|(?<!\w)_(?<em2>[^\s_](?:.*?[^\s_])?)_(?!\w)/u';
        $out = '';
        $pos = 0;
        $len = strlen($s);
        while ($pos < $len && preg_match($pattern, $s, $m, PREG_OFFSET_CAPTURE | PREG_UNMATCHED_AS_NULL, $pos)) {
            $start = (int) $m[0][1];
            $out .= e(substr($s, $pos, $start - $pos));
            $pos = $start + strlen((string) $m[0][0]);
            if ($m['code'][0] !== null) {
                $out .= '<code>' . e(trim((string) $m['codetext'][0])) . '</code>';
            } elseif ($m['src'][0] !== null) {
                $src = self::imageSrc((string) $m['src'][0]);
                $out .= $src === null
                    ? e((string) $m['alt'][0])
                    : '<img src="' . e($src) . '" alt="' . e((string) $m['alt'][0]) . '" loading="lazy" decoding="async">';
            } elseif ($m['href'][0] !== null) {
                $href = self::linkHref((string) $m['href'][0]);
                $label = self::inline((string) $m['text'][0]);
                $out .= $href === null ? $label : '<a href="' . e($href) . '" rel="noopener noreferrer">' . $label . '</a>';
            } elseif ($m['bold'][0] !== null) {
                $out .= '<strong>' . self::inline((string) $m['bold'][0]) . '</strong>';
            } elseif ($m['em'][0] !== null) {
                $out .= '<em>' . self::inline((string) $m['em'][0]) . '</em>';
            } else {
                $out .= '<em>' . self::inline((string) $m['em2'][0]) . '</em>';
            }
        }
        return $out . e(substr($s, $pos));
    }

    /** URL de lien autorisée, ou null. */
    public static function linkHref(string $url): ?string
    {
        $url = trim($url);
        if ($url === '' || preg_match('/[\x00-\x20\x7F\\\\]/', $url)) {
            return null;
        }
        if (preg_match('/^([a-z][a-z0-9+.\-]*):/i', $url, $m)) {
            return in_array(strtolower($m[1]), ['http', 'https', 'mailto'], true) ? $url : null;
        }
        if (str_starts_with($url, '//')) {
            return null;
        }
        // Chemin relatif : un « : » avant tout « / ? # » serait interprété comme un schéma.
        $first = strcspn($url, '/?#');
        if (strpos(substr($url, 0, $first), ':') !== false) {
            return null;
        }
        return self::withBase($url);
    }

    /** Source d'image : chemins relatifs au site uniquement. */
    public static function imageSrc(string $src): ?string
    {
        $src = trim($src);
        if ($src === '' || str_starts_with($src, '//') || preg_match('/[\x00-\x20\x7F\\\\:]/', $src)) {
            return null;
        }
        return self::withBase($src);
    }

    /** Préfixe les chemins absolus du site (« /uploads/x.webp ») par la base (« /test »). */
    private static function withBase(string $path): string
    {
        $base = Config::basePath();
        if ($base !== '' && str_starts_with($path, '/') && $path !== $base && !str_starts_with($path, $base . '/')) {
            return $base . $path;
        }
        return $path;
    }
}
