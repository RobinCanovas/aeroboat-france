<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Config;
use App\Core\Db;
use App\Core\Lang;

/** Pages éditoriales : gabarits app/views/pages/{clé}.php. */
final class PageController extends Controller
{
    public static function home(array $params = []): void
    {
        self::display('pages/home', self::meta(Lang::t('meta.site_name') . ' · ' . Lang::t('meta.tagline'), Lang::t('meta.default_description'), null, false), [
            'posts' => self::latestPosts(3),
            'partners' => self::publishedPartners(),
        ]);
    }

    public static function project(array $params = []): void
    {
        self::display('pages/project', self::meta(Lang::t('project.hero.title'), Lang::t('project.hero.lead')));
    }

    public static function team(array $params = []): void
    {
        $members = Db::all('SELECT * FROM {t:team_members} WHERE published = 1 ORDER BY sort_order, name');
        foreach ($members as &$m) {
            if ((int) $m['photo_consent'] !== 1) {
                $m['photo'] = null;
            }
        }
        unset($m);
        self::display('pages/team', self::meta(Lang::t('team.hero.title'), Lang::t('team.hero.lead')), ['members' => $members]);
    }

    public static function partners(array $params = []): void
    {
        $byCategory = [];
        foreach (self::publishedPartners() as $p) {
            $byCategory[(string) $p['category']][] = $p;
        }
        self::display('pages/partners', self::meta(Lang::t('partners.hero.title'), Lang::t('partners.hero.lead')), ['partnersByCategory' => $byCategory]);
    }

    public static function legal(array $params = []): void
    {
        self::display('pages/legal', self::meta(Lang::t('footer.legal.legal')), ['org' => self::org()]);
    }

    public static function privacy(array $params = []): void
    {
        self::display('pages/privacy', self::meta(Lang::t('footer.legal.privacy')), ['org' => self::org()]);
    }

    public static function cookies(array $params = []): void
    {
        self::display('pages/cookies', self::meta(Lang::t('footer.legal.cookies')), ['org' => self::org()]);
    }

    /** Variables ORG_* (clé sans préfixe, en minuscules) ; les valeurs vides sont omises. */
    public static function org(): array
    {
        $keys = ['NAME', 'LEGAL_FORM', 'RNA', 'ADDRESS', 'PUBLISHER', 'EMAIL', 'PHONE', 'CITY', 'HOST_NAME', 'HOST_ADDRESS', 'HOST_PHONE'];
        $out = [];
        foreach ($keys as $k) {
            $v = trim((string) Config::env('ORG_' . $k, ''));
            if ($v !== '') {
                $out[strtolower($k)] = $v;
            }
        }
        return $out;
    }
}
