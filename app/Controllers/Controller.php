<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Config;
use App\Core\Db;
use App\Core\Lang;
use App\Core\Response;
use App\Core\Router;
use App\Core\View;

/** Outils communs aux contrôleurs publics. */
abstract class Controller
{
    public const DEFAULT_IMAGE = 'img/water-run-1600.webp';

    /**
     * Métadonnées passées à toutes les vues : title, description, canonical, robots, image, alternates.
     */
    public static function meta(string $title, ?string $description = null, ?string $image = null, bool $suffix = true): array
    {
        $current = Router::current();
        $canonical = $current !== null ? (Router::alternates()[Lang::current()] ?? url('/')) : url('/');
        $desc = trim((string) ($description ?? ''));
        if ($desc === '') {
            $desc = Lang::t('meta.default_description');
        }
        if (mb_strlen($desc) > 300) {
            $desc = rtrim(mb_substr($desc, 0, 297)) . '…';
        }
        return [
            'title' => $suffix ? $title . Lang::t('meta.title_suffix') : $title,
            'description' => $desc,
            'canonical' => $canonical,
            'robots' => Config::isTest() ? 'noindex, nofollow' : 'index, follow',
            'image' => asset($image !== null && $image !== '' ? $image : self::DEFAULT_IMAGE),
            'alternates' => Router::alternates(),
        ];
    }

    /**
     * Rendu d'une vue publique avec $meta, $page (clé de route) et $routeKey (toujours la clé de route).
     * Exception du contrat : dans news/index, $page est le numéro de page (la clé de route reste dans $routeKey).
     */
    protected static function display(string $tpl, array $meta, array $data = [], int $code = 200): void
    {
        $data['meta'] = $meta;
        $data['routeKey'] = Router::current() ?? '';
        $data['page'] ??= $data['routeKey'];
        Response::html(View::render($tpl, $data), $code);
    }

    /** Condition SQL de visibilité d'un article (CONTRACTS §3), paramètre :now. */
    public static function visibleSql(): string
    {
        return "(status = 'published' OR (status = 'scheduled' AND publish_at IS NOT NULL AND publish_at <= :now))";
    }

    public static function latestPosts(int $limit, int $offset = 0): array
    {
        return Db::all(
            'SELECT * FROM {t:posts} WHERE ' . self::visibleSql()
            . ' ORDER BY COALESCE(publish_at, created_at) DESC, id DESC LIMIT ' . max(1, $limit) . ' OFFSET ' . max(0, $offset),
            ['now' => Db::now()]
        );
    }

    public static function countPosts(): int
    {
        $r = Db::one('SELECT COUNT(*) AS n FROM {t:posts} WHERE ' . self::visibleSql(), ['now' => Db::now()]);
        return (int) ($r['n'] ?? 0);
    }

    /** Partenaires publiés ; logo masqué sans consentement. */
    public static function publishedPartners(): array
    {
        $rows = Db::all('SELECT * FROM {t:partners} WHERE published = 1 ORDER BY sort_order, name');
        foreach ($rows as &$r) {
            if ((int) $r['logo_consent'] !== 1) {
                $r['logo'] = null;
            }
        }
        return $rows;
    }
}
