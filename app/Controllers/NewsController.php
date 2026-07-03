<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Db;
use App\Core\Lang;
use App\Core\Markdown;
use App\Core\Request;
use App\Core\Response;
use App\Core\Router;

/** Actualités : liste paginée (news/index) et article (news/show). */
final class NewsController extends Controller
{
    public const PER_PAGE = 9;

    public static function index(array $params = []): void
    {
        $total = self::countPosts();
        $pages = max(1, (int) ceil($total / self::PER_PAGE));
        $raw = Request::input('page', '1');
        $page = is_string($raw) && ctype_digit($raw) ? (int) $raw : 0;
        if ($page < 1 || $page > $pages) {
            ErrorController::notFound();
            return;
        }
        $posts = self::latestPosts(self::PER_PAGE, ($page - 1) * self::PER_PAGE);
        $meta = self::meta(Lang::t('news.hero.title'), Lang::t('news.hero.lead'));
        if ($page > 1) {
            $meta['canonical'] = Router::url('news', ['page' => $page]);
        }
        self::display('news/index', $meta, ['posts' => $posts, 'page' => $page, 'pages' => $pages]);
    }

    public static function show(array $params = []): void
    {
        $slug = (string) ($params['slug'] ?? '');
        $lang = Lang::current();
        $other = $lang === 'fr' ? 'en' : 'fr';
        $sql = 'SELECT * FROM {t:posts} WHERE slug_%s = :slug AND ' . self::visibleSql();
        $post = Db::one(sprintf($sql, $lang), ['slug' => $slug, 'now' => Db::now()]);
        if ($post === null) {
            // Slug de l'autre langue : redirection vers l'URL canonique de cette langue.
            $alt = Db::one(sprintf($sql, $other), ['slug' => $slug, 'now' => Db::now()]);
            if ($alt !== null && $alt['slug_' . $lang] !== $slug) {
                Response::redirect(Router::url('news_show', ['slug' => $alt['slug_' . $lang]]), 301);
            }
            ErrorController::notFound();
            return;
        }
        Router::setAlternateParams('fr', ['slug' => $post['slug_fr']]);
        Router::setAlternateParams('en', ['slug' => $post['slug_en']]);
        $cover = !empty($post['cover']) ? self::coverPath((string) $post['cover']) : null;
        $meta = self::meta(loc($post, 'title'), loc($post, 'excerpt'), $cover);
        self::display('news/show', $meta, [
            'post' => $post,
            'bodyHtml' => Markdown::render(loc($post, 'body')),
            'page' => 'news_show',
        ]);
    }

    /** Couverture : chemin relatif à public/ (« uploads/… » ou « img/… »). */
    private static function coverPath(string $cover): string
    {
        $cover = ltrim($cover, '/');
        return (str_starts_with($cover, 'uploads/') || str_starts_with($cover, 'img/')) ? $cover : 'uploads/' . $cover;
    }
}
