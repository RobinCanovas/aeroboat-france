<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Config;
use App\Core\Db;
use App\Core\Lang;
use App\Core\Response;
use App\Core\Router;

/** sitemap.xml (avec hreflang) et robots.txt (Disallow: / en version de test). */
final class SeoController extends Controller
{
    public static function sitemap(array $params = []): void
    {
        $entries = [];
        foreach (Router::routes() as $key => $route) {
            if (str_contains((string) $route['fr'], '{')) {
                continue;
            }
            $entries[] = [
                'fr' => Router::url((string) $key, [], 'fr'),
                'en' => Router::url((string) $key, [], 'en'),
                'lastmod' => null,
            ];
        }
        try {
            $posts = Db::all('SELECT slug_fr, slug_en, updated_at FROM {t:posts} WHERE ' . self::visibleSql() . ' ORDER BY id', ['now' => Db::now()]);
        } catch (\Throwable $e) {
            $posts = [];
        }
        foreach ($posts as $p) {
            $entries[] = [
                'fr' => Router::url('news_show', ['slug' => $p['slug_fr']], 'fr'),
                'en' => Router::url('news_show', ['slug' => $p['slug_en']], 'en'),
                'lastmod' => substr((string) $p['updated_at'], 0, 10),
            ];
        }
        $x = static fn (string $s): string => htmlspecialchars($s, ENT_XML1 | ENT_QUOTES, 'UTF-8');
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n"
            . '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">' . "\n";
        foreach ($entries as $e) {
            foreach (Lang::SUPPORTED as $l) {
                $xml .= '  <url><loc>' . $x($e[$l]) . '</loc>';
                if ($e['lastmod']) {
                    $xml .= '<lastmod>' . $x($e['lastmod']) . '</lastmod>';
                }
                foreach (Lang::SUPPORTED as $alt) {
                    $xml .= '<xhtml:link rel="alternate" hreflang="' . $alt . '" href="' . $x($e[$alt]) . '"/>';
                }
                $xml .= '<xhtml:link rel="alternate" hreflang="x-default" href="' . $x($e['fr']) . '"/>';
                $xml .= "</url>\n";
            }
        }
        $xml .= '</urlset>' . "\n";
        Response::text($xml, 200, 'application/xml');
    }

    public static function robots(array $params = []): void
    {
        if (Config::isTest()) {
            Response::text("User-agent: *\nDisallow: /\n");
        }
        Response::text("User-agent: *\nDisallow: " . Config::basePath() . "/admin\n\nSitemap: " . url('/sitemap.xml') . "\n");
    }
}
