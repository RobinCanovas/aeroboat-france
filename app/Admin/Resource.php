<?php
declare(strict_types=1);

namespace App\Admin;

use App\Core\Upload;

/**
 * Ressource CRUD générique pilotée par une définition (app/Admin/Resources/*.php).
 *
 * Définition : key, table, area (zone de Policy), label, singular, title_field, fields (ordre = ordre du formulaire),
 * groups (mise en page du formulaire), list{columns,search,sort,default_sort,per_page,filters},
 * defaults (valeurs d'un nouvel enregistrement), timestamps, can_create, can_delete, virtual (champs non stockés),
 * before_save(array $values, ?array $existing, Context $ctx): array{0:array,1:array<string,string>}, before_delete(array $row, Context $ctx): ?string,
 * after_save(array $values, ?array $existing, int $id, Context $ctx): void (après écriture réussie).
 */
final class Resource
{
    /** @var array<string,self>|null */
    private static ?array $registry = null;

    /** @param array<string,mixed> $def */
    public function __construct(public readonly array $def)
    {
    }

    /** @return array<string,self> */
    public static function registry(): array
    {
        if (self::$registry === null) {
            self::$registry = [];
            foreach (glob(__DIR__ . '/Resources/*.php') ?: [] as $file) {
                $def = require $file;
                if (is_array($def) && isset($def['key'])) {
                    self::$registry[(string) $def['key']] = new self($def);
                }
            }
        }
        return self::$registry;
    }

    public static function get(string $key): ?self
    {
        return self::registry()[$key] ?? null;
    }

    public function key(): string
    {
        return (string) $this->def['key'];
    }

    /** @return array<string,array<string,mixed>> */
    public function fields(): array
    {
        return $this->def['fields'];
    }

    public function canCreate(): bool
    {
        return (bool) ($this->def['can_create'] ?? true);
    }

    public function canDelete(): bool
    {
        return (bool) ($this->def['can_delete'] ?? true);
    }

    /** @return array<string,mixed>|null */
    public function find(int $id): ?array
    {
        return Store::one('SELECT * FROM {t:' . $this->def['table'] . '} WHERE id = ?', [$id]);
    }

    /**
     * @param array<string,mixed> $query paramètres GET : q, sort, dir, page, f_<champ>
     * @return array{rows:list<array<string,mixed>>,total:int,page:int,pages:int,q:string,sort:string,dir:string,filters:array<string,string>}
     */
    public function list(array $query): array
    {
        $list = $this->def['list'];
        $table = '{t:' . $this->def['table'] . '}';
        $where = [];
        $params = [];

        $q = isset($query['q']) && is_scalar($query['q']) ? trim((string) $query['q']) : '';
        if ($q !== '' && !empty($list['search'])) {
            $or = [];
            foreach ($list['search'] as $col) {
                $or[] = $col . " LIKE ? ESCAPE '!'";
                $params[] = Store::like($q);
            }
            $where[] = '(' . implode(' OR ', $or) . ')';
        }

        $filters = [];
        foreach (array_keys($list['filters'] ?? []) as $col) {
            $val = isset($query['f_' . $col]) && is_scalar($query['f_' . $col]) ? (string) $query['f_' . $col] : '';
            $options = $this->def['fields'][$col]['options'] ?? [];
            if ($val !== '' && isset($options[$val])) {
                $where[] = $col . ' = ?';
                $params[] = $val;
                $filters[$col] = $val;
            }
        }
        $w = $where === [] ? '' : ' WHERE ' . implode(' AND ', $where);

        $sortable = $list['sort'] ?? ['id'];
        [$defCol, $defDir] = $list['default_sort'] ?? ['id', 'desc'];
        $sort = isset($query['sort']) && is_string($query['sort']) && in_array($query['sort'], $sortable, true) ? $query['sort'] : $defCol;
        $dir = isset($query['dir']) && is_string($query['dir']) ? (strtolower($query['dir']) === 'asc' ? 'asc' : 'desc') : $defDir;

        $total = Store::count('SELECT COUNT(*) FROM ' . $table . $w, $params);
        $per = max(5, (int) ($list['per_page'] ?? 20));
        $pages = max(1, (int) ceil($total / $per));
        $page = isset($query['page']) && is_scalar($query['page']) ? max(1, min($pages, (int) $query['page'])) : 1;

        $rows = Store::all(
            'SELECT * FROM ' . $table . $w . ' ORDER BY ' . $sort . ' ' . $dir . ', id DESC LIMIT ' . $per . ' OFFSET ' . (($page - 1) * $per),
            $params
        );
        return ['rows' => $rows, 'total' => $total, 'page' => $page, 'pages' => $pages, 'q' => $q, 'sort' => $sort, 'dir' => $dir, 'filters' => $filters];
    }

    /**
     * Valeurs affichées dans le formulaire (conversion UTC vers Paris, mots de passe vides).
     *
     * @param array<string,mixed>|null $row
     * @return array<string,mixed>
     */
    public function formValues(?array $row): array
    {
        $out = [];
        foreach ($this->fields() as $name => $f) {
            $type = (string) ($f['type'] ?? 'text');
            if ($row === null) {
                $out[$name] = $f['default'] ?? ($this->def['defaults'][$name] ?? ($type === 'bool' ? 0 : ''));
                continue;
            }
            $v = $row[$name] ?? '';
            if ($type === 'password') {
                $v = '';
            } elseif (($f['type'] ?? '') === 'datetime') {
                $v = Time::utcToParisInput($v === null ? null : (string) $v);
            }
            $out[$name] = $v ?? '';
        }
        return $out;
    }

    /**
     * Valide et enregistre. Retourne l'id (null si erreurs), les erreurs par champ et les valeurs saisies à réafficher.
     *
     * @param array<string,mixed> $input
     * @param array<string,mixed> $files
     * @return array{id:?int,errors:array<string,string>,values:array<string,mixed>}
     */
    public function save(?int $id, array $input, array $files, Context $ctx): array
    {
        $existing = $id === null ? null : $this->find($id);
        $fields = $this->fields();
        $check = [];
        foreach ($fields as $name => $f) {
            if (!empty($f['required_on_create'])) {
                $f['required'] = $existing === null;
            }
            $check[$name] = $f;
        }
        [$values, $errors] = Validator::run($check, $input);

        $display = $values;
        foreach ($fields as $name => $f) {
            if (($f['type'] ?? '') === 'password') {
                $display[$name] = '';
            }
            if (!isset($display[$name]) && ($f['type'] ?? '') !== 'image') {
                $display[$name] = is_scalar($input[$name] ?? null) ? (string) $input[$name] : '';
            }
            if (($f['type'] ?? '') === 'image') {
                $display[$name] = $existing[$name] ?? '';
            }
        }

        if ($errors === []) {
            // Dates saisies à l'heure de Paris, stockées en UTC.
            foreach ($fields as $name => $f) {
                if (($f['type'] ?? '') === 'datetime') {
                    $values[$name] = ($values[$name] ?? '') === '' ? null : Time::parisToUtc((string) $values[$name]);
                }
            }
            // Images : conserver, remplacer ou retirer.
            foreach ($fields as $name => $f) {
                if (($f['type'] ?? '') !== 'image') {
                    continue;
                }
                $current = $existing[$name] ?? null;
                $values[$name] = $current === '' ? null : $current;
                if (!empty($input['remove_' . $name]) && $input['remove_' . $name] !== '0') {
                    $values[$name] = null;
                }
                $file = $files[$name] ?? null;
                if (is_array($file) && (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
                    try {
                        $values[$name] = Upload::image($file, (string) ($f['subdir'] ?? 'misc'));
                    } catch (\Throwable) {
                        $errors[$name] = '« ' . ($f['label'] ?? $name) . ' » : image refusée (JPEG, PNG ou WebP valide attendu).';
                    }
                }
                $display[$name] = $values[$name] ?? '';
            }
        }

        if ($errors === [] && isset($this->def['before_save'])) {
            [$values, $hookErrors] = ($this->def['before_save'])($values, $existing, $ctx);
            $errors += $hookErrors;
        }
        if ($errors !== []) {
            // Une image téléversée dans cette requête mais non enregistrée (erreur ailleurs) ne doit pas rester sur le disque.
            foreach ($fields as $name => $f) {
                if (($f['type'] ?? '') === 'image' && is_string($values[$name] ?? null)
                    && $values[$name] !== ($existing[$name] ?? null)) {
                    ImageFiles::delete(ImageFiles::dir(), $values[$name]);
                }
            }
            return ['id' => null, 'errors' => $errors, 'values' => $display];
        }

        foreach ($this->def['virtual'] ?? [] as $v) {
            unset($values[$v]);
        }
        $table = (string) $this->def['table'];
        $now = Time::nowUtc();
        if ($existing === null) {
            if (!empty($this->def['timestamps'])) {
                $values['created_at'] = $now;
                $values['updated_at'] = $now;
            }
            $newId = Store::insert($table, $values);
            if (isset($this->def['after_save'])) {
                ($this->def['after_save'])($values, null, $newId, $ctx);
            }
            return ['id' => $newId, 'errors' => [], 'values' => $display];
        }
        if (!empty($this->def['timestamps'])) {
            $values['updated_at'] = $now;
        }
        Store::update($table, (int) $existing['id'], $values);
        // Fichiers devenus orphelins (image remplacée ou retirée) : supprimés après la réussite de l'écriture.
        foreach ($fields as $name => $f) {
            if (($f['type'] ?? '') === 'image') {
                $old = (string) ($existing[$name] ?? '');
                if ($old !== '' && $old !== (string) ($values[$name] ?? '')) {
                    ImageFiles::delete(ImageFiles::dir(), $old);
                }
            }
        }
        if (isset($this->def['after_save'])) {
            ($this->def['after_save'])($values, $existing, (int) $existing['id'], $ctx);
        }
        return ['id' => (int) $existing['id'], 'errors' => [], 'values' => $display];
    }

    /** Supprime un enregistrement. Retourne un message d'erreur ou null. */
    public function remove(int $id, Context $ctx): ?string
    {
        $row = $this->find($id);
        if ($row === null) {
            return 'Enregistrement introuvable.';
        }
        if (isset($this->def['before_delete'])) {
            $err = ($this->def['before_delete'])($row, $ctx);
            if ($err !== null) {
                return $err;
            }
        }
        Store::delete((string) $this->def['table'], $id);
        foreach ($this->fields() as $name => $f) {
            if (($f['type'] ?? '') === 'image') {
                ImageFiles::delete(ImageFiles::dir(), (string) ($row[$name] ?? ''));
            }
        }
        return null;
    }
}
