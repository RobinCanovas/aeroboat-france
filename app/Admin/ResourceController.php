<?php
declare(strict_types=1);

namespace App\Admin;

use App\Core\Markdown;

/** Contrôleur CRUD unique, piloté par la définition d'une Resource. */
final class ResourceController
{
    /** @param list<string> $rest segments après la clé : [], ['new'], ['12','edit'], ['12','delete'] */
    public static function handle(Resource $r, Context $ctx, array $rest): Reply
    {
        $key = $r->key();
        $base = '/' . $key;
        if ($rest === []) {
            return self::index($r, $ctx);
        }
        if ($rest === ['new'] && $r->canCreate()) {
            return self::form($r, $ctx, null);
        }
        if (count($rest) === 2 && ctype_digit($rest[0])) {
            $id = (int) $rest[0];
            if ($r->find($id) === null) {
                return Ui::error($ctx, 404, 'Cet enregistrement n\'existe pas.');
            }
            if ($rest[1] === 'edit') {
                return self::form($r, $ctx, $id);
            }
            if ($rest[1] === 'delete' && $r->canDelete()) {
                return self::delete($r, $ctx, $id, $base);
            }
        }
        return Ui::error($ctx, 404, 'Page introuvable.');
    }

    private static function index(Resource $r, Context $ctx): Reply
    {
        $data = $r->list($ctx->query);
        return Ui::page($ctx, (string) $r->def['label'], 'resources/list', [
            'res' => $r,
            'data' => $data,
            'query' => $ctx->query,
        ], (string) $r->key());
    }

    private static function form(Resource $r, Context $ctx, ?int $id): Reply
    {
        $row = $id === null ? null : $r->find($id);
        $errors = [];
        $previews = [];
        $values = $r->formValues($row);

        if ($ctx->isPost()) {
            if ($ctx->p('preview') !== '') {
                // Aperçu Markdown : rien n'est enregistré ni envoyé.
                foreach ($r->fields() as $name => $f) {
                    $type = (string) ($f['type'] ?? 'text');
                    if ($type === 'image' || $type === 'password') {
                        continue;
                    }
                    $values[$name] = $type === 'bool' ? (int) ($ctx->p($name) !== '' && $ctx->p($name) !== '0') : $ctx->p($name);
                }
                foreach ($r->def['preview'] ?? [] as $name) {
                    $previews[$name] = Markdown::render((string) $values[$name]);
                }
            } else {
                $res = $r->save($id, $ctx->post, $ctx->files, $ctx);
                if ($res['id'] !== null) {
                    Flash::add('ok', ucfirst((string) $r->def['singular']) . ($id === null ? ' créé.' : ' enregistré.'));
                    return Reply::redirect(Ui::u('/' . $r->key()));
                }
                $errors = $res['errors'];
                $values = $res['values'];
            }
        }

        $title = $id === null ? 'Nouveau : ' . $r->def['singular'] : 'Modifier : ' . $r->def['singular'];
        return Ui::page($ctx, $title, 'resources/form', [
            'res' => $r,
            'row' => $row,
            'values' => $values,
            'errors' => $errors,
            'previews' => $previews,
            'action' => $id === null ? Ui::u('/' . $r->key() . '/new') : Ui::u('/' . $r->key() . '/' . $id . '/edit'),
            'suggestions' => [],
        ], (string) $r->key());
    }

    private static function delete(Resource $r, Context $ctx, int $id, string $base): Reply
    {
        $row = $r->find($id);
        $label = (string) ($row[$r->def['title_field']] ?? ('n° ' . $id));
        if ($ctx->isPost()) {
            $err = $r->remove($id, $ctx);
            Flash::add($err === null ? 'ok' : 'error', $err ?? ucfirst((string) $r->def['singular']) . ' supprimé.');
            return Reply::redirect(Ui::u($base));
        }
        return Ui::page($ctx, 'Confirmer la suppression', 'confirm', [
            'message' => 'Supprimer définitivement ' . $r->def['singular'] . ' « ' . $label . ' » ? Cette action est irréversible.',
            'action' => Ui::u($base . '/' . $id . '/delete'),
            'cancel' => Ui::u($base),
            'button' => 'Supprimer définitivement',
        ], (string) $r->key());
    }
}
