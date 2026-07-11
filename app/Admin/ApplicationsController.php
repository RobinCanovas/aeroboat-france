<?php
declare(strict_types=1);

namespace App\Admin;

use App\Core\Config;

/** Candidatures : liste filtrable, fiche, statut, notes, CV (route protégée), export CSV. */
final class ApplicationsController
{
    private const PER_PAGE = 25;

    /** @param list<string> $rest */
    public static function handle(Context $ctx, array $rest): Reply
    {
        if ($rest === []) {
            return self::index($ctx);
        }
        if ($rest === ['export']) {
            return self::export($ctx);
        }
        if (ctype_digit($rest[0])) {
            $id = (int) $rest[0];
            $row = Store::one('SELECT * FROM {t:applications} WHERE id = ?', [$id]);
            if ($row === null) {
                return Ui::error($ctx, 404, 'Cette candidature n\'existe pas.');
            }
            return match ($rest[1] ?? '') {
                '' => count($rest) === 1 ? self::show($ctx, $row) : Ui::error($ctx, 404, 'Page introuvable.'),
                'cv' => self::cv($ctx, $row),
                'delete' => self::delete($ctx, $row),
                default => Ui::error($ctx, 404, 'Page introuvable.'),
            };
        }
        return Ui::error($ctx, 404, 'Page introuvable.');
    }

    public static function cvDir(): string
    {
        return rtrim((string) Config::root(), '/\\') . '/storage/cv';
    }

    /** @return array{0:string,1:list<mixed>} clause WHERE et paramètres */
    private static function filter(Context $ctx): array
    {
        $where = [];
        $params = [];
        $status = $ctx->q('status');
        if (isset(Ui::STATUS_APPLICATION[$status])) {
            $where[] = 'status = ?';
            $params[] = $status;
        }
        $q = trim($ctx->q('q'));
        if ($q !== '') {
            $where[] = "(last_name LIKE ? ESCAPE '!' OR first_name LIKE ? ESCAPE '!' OR email LIKE ? ESCAPE '!' OR school LIKE ? ESCAPE '!')";
            array_push($params, Store::like($q), Store::like($q), Store::like($q), Store::like($q));
        }
        return [$where === [] ? '' : ' WHERE ' . implode(' AND ', $where), $params];
    }

    private static function index(Context $ctx): Reply
    {
        [$w, $params] = self::filter($ctx);
        $total = Store::count('SELECT COUNT(*) FROM {t:applications}' . $w, $params);
        $pages = max(1, (int) ceil($total / self::PER_PAGE));
        $page = max(1, min($pages, (int) $ctx->q('page', '1')));
        $rows = Store::all(
            'SELECT id, first_name, last_name, email, school, status, cv_file, created_at FROM {t:applications}' . $w
            . ' ORDER BY created_at DESC, id DESC LIMIT ' . self::PER_PAGE . ' OFFSET ' . (($page - 1) * self::PER_PAGE),
            $params
        );
        $counts = [];
        foreach (Store::all('SELECT status, COUNT(*) AS n FROM {t:applications} GROUP BY status') as $c) {
            $counts[(string) $c['status']] = (int) $c['n'];
        }
        return Ui::page($ctx, 'Candidatures', 'applications/list', [
            'rows' => $rows, 'total' => $total, 'page' => $page, 'pages' => $pages,
            'status' => isset(Ui::STATUS_APPLICATION[$ctx->q('status')]) ? $ctx->q('status') : '',
            'q' => trim($ctx->q('q')), 'counts' => $counts,
        ], 'candidatures');
    }

    /** @param array<string,mixed> $row */
    private static function show(Context $ctx, array $row): Reply
    {
        $id = (int) $row['id'];
        if ($ctx->isPost()) {
            $status = $ctx->p('status');
            $notes = trim(str_replace("\r\n", "\n", $ctx->p('notes')));
            if (!isset(Ui::STATUS_APPLICATION[$status])) {
                Flash::add('error', 'Statut inconnu.');
            } elseif (mb_strlen($notes, 'UTF-8') > 5000) {
                Flash::add('error', 'Notes : 5 000 caractères au maximum.');
            } else {
                Store::update('applications', $id, ['status' => $status, 'notes' => $notes, 'updated_at' => Time::nowUtc()]);
                Flash::add('ok', 'Candidature mise à jour.');
            }
            return Reply::redirect(Ui::u('/candidatures/' . $id));
        }
        return Ui::page($ctx, 'Candidature de ' . $row['first_name'] . ' ' . $row['last_name'], 'applications/show', [
            'a' => $row,
            'hasCv' => $row['cv_file'] !== null && $row['cv_file'] !== '' && CvFiles::resolve(self::cvDir(), (string) $row['cv_file']) !== null,
        ], 'candidatures');
    }

    /** @param array<string,mixed> $row */
    private static function cv(Context $ctx, array $row): Reply
    {
        $path = CvFiles::resolve(self::cvDir(), (string) ($row['cv_file'] ?? ''));
        if ($path === null) {
            return Ui::error($ctx, 404, 'Aucun CV disponible pour cette candidature.');
        }
        return Reply::fileDownload($path, 'cv-candidature-' . (int) $row['id'] . '.pdf', 'application/pdf');
    }

    /** @param array<string,mixed> $row */
    private static function delete(Context $ctx, array $row): Reply
    {
        $id = (int) $row['id'];
        if ($ctx->isPost()) {
            CvFiles::delete(self::cvDir(), (string) ($row['cv_file'] ?? ''));
            Store::delete('applications', $id);
            Flash::add('ok', 'Candidature supprimée, CV compris.');
            return Reply::redirect(Ui::u('/candidatures'));
        }
        return Ui::page($ctx, 'Confirmer la suppression', 'confirm', [
            'message' => 'Supprimer définitivement la candidature de ' . $row['first_name'] . ' ' . $row['last_name'] . ' (CV compris) ? Cette action est irréversible.',
            'action' => Ui::u('/candidatures/' . $id . '/delete'),
            'cancel' => Ui::u('/candidatures/' . $id),
            'button' => 'Supprimer définitivement',
        ], 'candidatures');
    }

    private static function export(Context $ctx): Reply
    {
        [$w, $params] = self::filter($ctx);
        $rows = Store::all('SELECT * FROM {t:applications}' . $w . ' ORDER BY created_at DESC, id DESC', $params);
        $header = ['Numéro', 'Reçue le (Paris)', 'Prénom', 'Nom', 'E-mail', 'École', 'Niveau', 'Compétences', 'Motivation', 'Disponibilité', 'CV joint', 'Statut', 'Notes internes', 'Langue'];
        $out = [];
        foreach ($rows as $a) {
            $out[] = [
                $a['id'], Time::utcToParis((string) $a['created_at']), $a['first_name'], $a['last_name'], $a['email'], $a['school'], $a['level'],
                $a['skills'], $a['motivation'], $a['availability'], ($a['cv_file'] ?? '') !== '' ? 'oui' : 'non',
                Ui::statusLabel((string) $a['status']), $a['notes'], $a['locale'],
            ];
        }
        return Reply::download(Csv::build($header, $out), 'candidatures-' . gmdate('Ymd') . '.csv', 'text/csv; charset=utf-8');
    }
}
