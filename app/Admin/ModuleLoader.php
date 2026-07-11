<?php
declare(strict_types=1);

namespace App\Admin;

/** Découverte des modules de l'espace membres : app/Modules/<Nom>/module.php (contrat §7). */
final class ModuleLoader
{
    private const REQUIRED = ['key', 'label', 'icon', 'route', 'roles', 'controller', 'method'];

    /**
     * @return list<array{key:string,label:string,icon:string,route:string,roles:list<string>,controller:string,method:string,dir:string}>
     */
    public static function discover(string $modulesDir, ?string $role = null): array
    {
        $found = [];
        $files = glob(rtrim($modulesDir, '/\\') . '/*/module.php') ?: [];
        sort($files);
        foreach ($files as $file) {
            $def = require $file;
            if (!is_array($def)) {
                continue;
            }
            foreach (self::REQUIRED as $k) {
                if (!array_key_exists($k, $def)) {
                    continue 2;
                }
            }
            $key = (string) $def['key'];
            if (preg_match('/^[a-z0-9_-]{1,40}$/', $key) !== 1) {
                continue;
            }
            $roles = array_values(array_filter((array) $def['roles'], 'is_string'));
            if ($role !== null && !in_array($role, $roles, true)) {
                continue;
            }
            $found[$key] = [
                'key' => $key,
                'label' => (string) $def['label'],
                'icon' => (string) $def['icon'],
                'route' => (string) $def['route'],
                'roles' => $roles,
                'controller' => (string) $def['controller'],
                'method' => (string) $def['method'],
                'dir' => dirname($file),
            ];
        }
        $list = array_values($found);
        usort($list, static fn (array $a, array $b): int => strcmp($a['label'], $b['label']));
        return $list;
    }

    /** @return array<string,mixed>|null */
    public static function find(string $modulesDir, string $key, ?string $role = null): ?array
    {
        foreach (self::discover($modulesDir, $role) as $m) {
            if ($m['key'] === $key) {
                return $m;
            }
        }
        return null;
    }
}
