<?php
declare(strict_types=1);

namespace App\Admin;

use DateTimeImmutable;
use DateTimeZone;

/** Messages de contact et demandes d'exercice de droits (RGPD, réponse sous un mois). */
final class MessagesController
{
    public const RIGHTS_TYPES = [
        'access' => 'Accès',
        'rectify' => 'Rectification',
        'erase' => 'Effacement',
        'restrict' => 'Limitation',
        'object' => 'Opposition',
        'portability' => 'Portabilité',
    ];

    /** Date limite légale : un mois après réception. */
    public static function deadline(string $createdUtc): ?DateTimeImmutable
    {
        try {
            return (new DateTimeImmutable($createdUtc, new DateTimeZone('UTC')))->modify('+1 month');
        } catch (\Exception) {
            return null;
        }
    }

    /** @param list<string> $rest */
    public static function handle(Context $ctx, array $rest): Reply
    {
        if ($rest === []) {
            return self::index($ctx);
        }
        if (count($rest) === 2 && ctype_digit($rest[0])) {
            $id = (int) $rest[0];
            $row = Store::one('SELECT * FROM {t:messages} WHERE id = ?', [$id]);
            if ($row === null) {
                return Ui::error($ctx, 404, 'Ce message n\'existe pas.');
            }
            if ($rest[1] === 'handled' && $ctx->isPost()) {
                $done = $ctx->p('handled') === '1' ? 1 : 0;
                Store::update('messages', $id, ['handled' => $done]);
                Flash::add('ok', $done === 1 ? 'Message marqué comme traité.' : 'Message remis en attente.');
                return Reply::redirect(self::back($ctx));
            }
            if ($rest[1] === 'delete') {
                if ($ctx->isPost()) {
                    Store::delete('messages', $id);
                    Flash::add('ok', 'Message supprimé.');
                    return Reply::redirect(Ui::u('/messages'));
                }
                return Ui::page($ctx, 'Confirmer la suppression', 'confirm', [
                    'message' => 'Supprimer définitivement le message de « ' . $row['name'] . ' » ? Cette action est irréversible.',
                    'action' => Ui::u('/messages/' . $id . '/delete'),
                    'cancel' => Ui::u('/messages'),
                    'button' => 'Supprimer définitivement',
                ], 'messages');
            }
        }
        return Ui::error($ctx, 404, 'Page introuvable.');
    }

    private static function back(Context $ctx): string
    {
        $kind = $ctx->p('kind');
        $state = $ctx->p('state');
        $qs = [];
        if (in_array($kind, ['contact', 'rights'], true)) {
            $qs['kind'] = $kind;
        }
        if (in_array($state, ['open', 'done'], true)) {
            $qs['state'] = $state;
        }
        return Ui::u('/messages') . ($qs === [] ? '' : '?' . http_build_query($qs));
    }

    private static function index(Context $ctx): Reply
    {
        $kind = in_array($ctx->q('kind'), ['contact', 'rights'], true) ? $ctx->q('kind') : '';
        $state = in_array($ctx->q('state'), ['open', 'done'], true) ? $ctx->q('state') : '';
        $type = $kind === 'rights' && isset(self::RIGHTS_TYPES[$ctx->q('type')]) ? $ctx->q('type') : '';
        $where = [];
        $params = [];
        if ($type !== '') {
            $where[] = 'request_type = ?';
            $params[] = $type;
        }
        if ($kind !== '') {
            $where[] = 'kind = ?';
            $params[] = $kind;
        }
        if ($state !== '') {
            $where[] = 'handled = ?';
            $params[] = $state === 'done' ? 1 : 0;
        }
        $w = $where === [] ? '' : ' WHERE ' . implode(' AND ', $where);
        $rows = Store::all('SELECT * FROM {t:messages}' . $w . ' ORDER BY handled ASC, (CASE WHEN kind = \'rights\' THEN 0 ELSE 1 END) ASC, created_at ASC, id ASC LIMIT 200', $params);

        $now = new DateTimeImmutable('now', new DateTimeZone('UTC'));
        foreach ($rows as &$m) {
            $m['deadline'] = null;
            $m['days_left'] = null;
            if ($m['kind'] === 'rights') {
                $d = self::deadline((string) $m['created_at']);
                if ($d !== null) {
                    $m['deadline'] = $d->setTimezone(new DateTimeZone('Europe/Paris'))->format('d/m/Y');
                    $m['days_left'] = (int) floor(($d->getTimestamp() - $now->getTimestamp()) / 86400);
                }
            }
        }
        unset($m);
        return Ui::page($ctx, 'Messages', 'messages/list', [
            'rows' => $rows, 'type' => $type, 'kind' => $kind, 'state' => $state, 'rightsTypes' => self::RIGHTS_TYPES,
        ], 'messages');
    }
}
