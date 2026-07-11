<?php
declare(strict_types=1);

namespace App\Admin;

use App\Core\Config;
use DateTimeImmutable;
use DateTimeZone;

/** Purge RGPD : messages de plus de 12 mois, candidatures de plus de 24 mois (CV compris). */
final class PurgeController
{
    public const MESSAGES_MONTHS = 12;
    public const APPLICATIONS_MONTHS = 24;

    /** @return array{messages:string,applications:string} seuils UTC « Y-m-d H:i:s » */
    public static function cutoffs(?DateTimeImmutable $now = null): array
    {
        $now ??= new DateTimeImmutable('now', new DateTimeZone('UTC'));
        return [
            'messages' => $now->modify('-' . self::MESSAGES_MONTHS . ' months')->format(Time::FMT_DB),
            'applications' => $now->modify('-' . self::APPLICATIONS_MONTHS . ' months')->format(Time::FMT_DB),
        ];
    }

    public static function handle(Context $ctx): Reply
    {
        $cut = self::cutoffs();
        if ($ctx->isPost()) {
            if ($ctx->p('confirm') !== '1') {
                Flash::add('error', 'Cochez la case de confirmation pour lancer la purge.');
                return Reply::redirect(Ui::u('/rgpd'));
            }
            $apps = Store::all('SELECT id, cv_file FROM {t:applications} WHERE created_at < ?', [$cut['applications']]);
            $cvs = 0;
            foreach ($apps as $a) {
                if (CvFiles::delete(ApplicationsController::cvDir(), (string) ($a['cv_file'] ?? ''))) {
                    $cvs++;
                }
            }
            Store::run('DELETE FROM {t:applications} WHERE created_at < ?', [$cut['applications']]);
            $msgCount = Store::count('SELECT COUNT(*) FROM {t:messages} WHERE created_at < ?', [$cut['messages']]);
            Store::run('DELETE FROM {t:messages} WHERE created_at < ?', [$cut['messages']]);

            self::log(count($apps), $cvs, $msgCount, $ctx->userId());
            Flash::add('ok', 'Purge effectuée : ' . $msgCount . ' message(s), ' . count($apps) . ' candidature(s) et ' . $cvs . ' CV supprimés.');
            return Reply::redirect(Ui::u('/rgpd'));
        }
        return Ui::page($ctx, 'Purge RGPD', 'purge', [
            'messages' => Store::count('SELECT COUNT(*) FROM {t:messages} WHERE created_at < ?', [$cut['messages']]),
            'applications' => Store::count('SELECT COUNT(*) FROM {t:applications} WHERE created_at < ?', [$cut['applications']]),
            'months' => ['messages' => self::MESSAGES_MONTHS, 'applications' => self::APPLICATIONS_MONTHS],
        ], 'rgpd');
    }

    /** Journal : nombres uniquement, aucune donnée personnelle. */
    private static function log(int $applications, int $cvs, int $messages, int $userId): void
    {
        $dir = rtrim((string) Config::root(), '/\\') . '/storage/logs';
        $line = gmdate('Y-m-d H:i:s') . ' purge-rgpd user=' . $userId . ' messages=' . $messages . ' candidatures=' . $applications . ' cv=' . $cvs . "\n";
        @file_put_contents($dir . '/admin.log', $line, FILE_APPEND | LOCK_EX);
    }
}
