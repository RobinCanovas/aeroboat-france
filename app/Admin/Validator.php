<?php
declare(strict_types=1);

namespace App\Admin;

/**
 * Validation pilotée par une définition de champs (pure, sans accès base).
 * Types : text, textarea, markdown, email, url, slug, int, bool, select, datetime, password.
 * Les champs « image » sont gérés par Resource (envoi de fichier).
 */
final class Validator
{
    /**
     * @param array<string,array<string,mixed>> $fields
     * @param array<string,mixed> $input
     * @return array{0:array<string,mixed>,1:array<string,string>} [valeurs, erreurs par champ]
     */
    public static function run(array $fields, array $input): array
    {
        $values = [];
        $errors = [];
        foreach ($fields as $name => $f) {
            $type = (string) ($f['type'] ?? 'text');
            if ($type === 'image') {
                continue;
            }
            $label = (string) ($f['label'] ?? $name);
            $raw = $input[$name] ?? null;
            if (is_array($raw)) {
                $errors[$name] = "« $label » : valeur invalide.";
                continue;
            }

            if ($type === 'bool') {
                $values[$name] = ($raw !== null && $raw !== '' && $raw !== '0') ? 1 : 0;
                continue;
            }

            $s = $raw === null ? '' : (string) $raw;
            if ($type !== 'password') {
                $s = $type === 'textarea' || $type === 'markdown'
                    ? trim(str_replace("\r\n", "\n", $s))
                    : trim($s);
            }
            if (str_contains($s, "\0")) {
                $errors[$name] = "« $label » : caractères invalides.";
                continue;
            }
            $required = (bool) ($f['required'] ?? false);
            if ($s === '') {
                if ($required) {
                    $errors[$name] = "« $label » est obligatoire.";
                } else {
                    $values[$name] = $type === 'int' ? ($f['default'] ?? 0) : '';
                }
                continue;
            }

            $max = isset($f['max']) ? (int) $f['max'] : null;
            $min = isset($f['min_length']) ? (int) $f['min_length'] : null;
            $len = mb_strlen($s, 'UTF-8');
            if ($max !== null && $type !== 'int' && $len > $max) {
                $errors[$name] = "« $label » : $max caractères au maximum.";
                continue;
            }
            if ($min !== null && $len < $min) {
                $errors[$name] = "« $label » : $min caractères au minimum.";
                continue;
            }

            switch ($type) {
                case 'email':
                    if (filter_var($s, FILTER_VALIDATE_EMAIL) === false) {
                        $errors[$name] = "« $label » : adresse e-mail invalide.";
                        continue 2;
                    }
                    break;
                case 'url':
                    $scheme = strtolower((string) parse_url($s, PHP_URL_SCHEME));
                    if (filter_var($s, FILTER_VALIDATE_URL) === false || !in_array($scheme, ['http', 'https'], true)) {
                        $errors[$name] = "« $label » : adresse http(s) invalide.";
                        continue 2;
                    }
                    break;
                case 'slug':
                    if (!Slug::isValid($s, $max ?? 160)) {
                        $errors[$name] = "« $label » : minuscules, chiffres et tirets uniquement.";
                        continue 2;
                    }
                    break;
                case 'int':
                    if (preg_match('/^-?\d{1,9}$/', $s) !== 1) {
                        $errors[$name] = "« $label » : nombre entier attendu.";
                        continue 2;
                    }
                    $n = (int) $s;
                    if (isset($f['min']) && $n < (int) $f['min'] || isset($f['max']) && $n > (int) $f['max']) {
                        $errors[$name] = "« $label » : valeur hors limites.";
                        continue 2;
                    }
                    $values[$name] = $n;
                    continue 2;
                case 'select':
                    if (!isset(($f['options'] ?? [])[$s])) {
                        $errors[$name] = "« $label » : choix invalide.";
                        continue 2;
                    }
                    break;
                case 'datetime':
                    if (Time::parisToUtc($s) === null) {
                        $errors[$name] = "« $label » : date et heure invalides.";
                        continue 2;
                    }
                    break;
            }
            $values[$name] = $s;
        }
        return [$values, $errors];
    }

    /** Mot de passe de compte : 12 caractères minimum. Retourne un message ou null. */
    public static function passwordError(string $password, ?string $confirmation = null): ?string
    {
        if (mb_strlen($password, 'UTF-8') < 12) {
            return 'Le mot de passe doit contenir au moins 12 caractères.';
        }
        if (strlen($password) > 200) {
            return 'Le mot de passe est trop long (200 caractères au maximum).';
        }
        if ($confirmation !== null && !hash_equals($password, $confirmation)) {
            return 'La confirmation ne correspond pas au mot de passe.';
        }
        return null;
    }
}
