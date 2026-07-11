<?php
declare(strict_types=1);

namespace App\Admin;

final class Slug
{
    /** Translittère et normalise : « Équipe & Vol n°2 » devient « equipe-vol-n-2 ». */
    public static function make(string $text, int $max = 160): string
    {
        $map = [
            'à' => 'a', 'â' => 'a', 'ä' => 'a', 'á' => 'a', 'ã' => 'a', 'å' => 'a', 'æ' => 'ae',
            'ç' => 'c', 'é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e',
            'í' => 'i', 'ì' => 'i', 'î' => 'i', 'ï' => 'i', 'ñ' => 'n',
            'ó' => 'o', 'ò' => 'o', 'ô' => 'o', 'ö' => 'o', 'õ' => 'o', 'œ' => 'oe',
            'ú' => 'u', 'ù' => 'u', 'û' => 'u', 'ü' => 'u', 'ý' => 'y', 'ÿ' => 'y', 'ß' => 'ss',
        ];
        $s = mb_strtolower($text, 'UTF-8');
        $s = strtr($s, $map);
        $s = preg_replace('/[^a-z0-9]+/', '-', $s) ?? '';
        $s = trim($s, '-');
        if (strlen($s) > $max) {
            $s = trim(substr($s, 0, $max), '-');
        }
        return $s;
    }

    public static function isValid(string $slug, int $max = 160): bool
    {
        return $slug !== '' && strlen($slug) <= $max && preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug) === 1;
    }
}
