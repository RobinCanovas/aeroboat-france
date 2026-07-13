<?php
declare(strict_types=1);

namespace App\Modules\Calculator;

/**
 * Modèle simplifié de l'effet de sol (McCormick). Fonctions pures, sans accès base ni Core.
 *
 *   φ      = (16 h/b)² / (1 + (16 h/b)²)
 *   CDi,sol = φ · CL² / (π · AR · e)       AR = b² / S
 *   CL     = m g / (q S)                   q  = ½ ρ v²
 *
 * Résultat indicatif, non validé par des essais.
 */
final class GroundEffect
{
    public const G = 9.81;

    /**
     * Champs de saisie. « status » : known (donnée source), default (valeur usuelle modifiable),
     * hypothesis (inconnue : hypothèse à renseigner, jamais une mesure).
     *
     * @return array<string,array{label:string,unit:string,min:float,max:float,status:string,help:string}>
     */
    public static function fields(): array
    {
        return [
            'span' => ['label' => 'Envergure b', 'unit' => 'm', 'min' => 0.2, 'max' => 10.0, 'status' => 'known', 'help' => 'Somme des deux demi-ailes.'],
            'chord' => ['label' => 'Corde c', 'unit' => 'm', 'min' => 0.05, 'max' => 3.0, 'status' => 'known', 'help' => 'Aile rectangulaire : surface S = b × c.'],
            'mass' => ['label' => 'Masse m', 'unit' => 'kg', 'min' => 0.1, 'max' => 500.0, 'status' => 'known', 'help' => 'Masse totale en vol.'],
            'speed' => ['label' => 'Vitesse v', 'unit' => 'm/s', 'min' => 1.0, 'max' => 100.0, 'status' => 'hypothesis', 'help' => 'Vitesse de croisière.'],
            'height' => ['label' => 'Hauteur h (aile-sol)', 'unit' => 'm', 'min' => 0.005, 'max' => 5.0, 'status' => 'hypothesis', 'help' => 'Hauteur de l\'aile au-dessus de la surface.'],
            'rho' => ['label' => 'Masse volumique ρ', 'unit' => 'kg/m³', 'min' => 0.5, 'max' => 1.5, 'status' => 'default', 'help' => 'Air standard : 1,225.'],
            'e' => ['label' => 'Facteur d\'Oswald e', 'unit' => '', 'min' => 0.3, 'max' => 1.0, 'status' => 'default', 'help' => 'Valeur usuelle : 0,8.'],
            'cd0' => ['label' => 'Traînée parasite CD0', 'unit' => '', 'min' => 0.001, 'max' => 0.5, 'status' => 'hypothesis', 'help' => 'Traînée hors portance.'],
        ];
    }

    /**
     * Préréglage V6 indicatif. Seules l'envergure (2 × 100 cm), la corde (50 cm) et la masse
     * (« moins de 2 kg », donc 2,0 kg retenu) viennent de la source ; le reste est une hypothèse.
     *
     * @return array<string,string>
     */
    public static function presetV6(): array
    {
        return [
            'span' => '2,0', 'chord' => '0,5', 'mass' => '2,0',
            'speed' => '10', 'height' => '0,2', 'rho' => '1,225', 'e' => '0,8', 'cd0' => '0,05',
        ];
    }

    /** Facteur d'effet de sol de McCormick. */
    public static function phi(float $h, float $b): float
    {
        $x = (16.0 * $h / $b) ** 2;
        return $x / (1.0 + $x);
    }

    /**
     * Validation stricte : nombres décimaux positifs (virgule ou point), bornes, corde ≤ envergure.
     *
     * @param array<string,mixed> $raw
     * @return array{0:array<string,float>,1:array<string,string>}
     */
    public static function parse(array $raw): array
    {
        $values = [];
        $errors = [];
        foreach (self::fields() as $key => $f) {
            $in = $raw[$key] ?? '';
            if (!is_string($in) && !is_int($in) && !is_float($in)) {
                $errors[$key] = $f['label'] . ' : valeur invalide.';
                continue;
            }
            $s = trim((string) $in);
            if ($s === '') {
                $errors[$key] = $f['label'] . ' : à renseigner.';
                continue;
            }
            if (preg_match('/^\d{1,6}(?:[.,]\d{1,6})?$/', $s) !== 1) {
                $errors[$key] = $f['label'] . ' : nombre positif attendu (ex. 1,5).';
                continue;
            }
            $n = (float) str_replace(',', '.', $s);
            if ($n < $f['min'] || $n > $f['max']) {
                $errors[$key] = $f['label'] . ' : entre ' . self::fmt($f['min'], 3) . ' et ' . self::fmt($f['max'], 3)
                    . ($f['unit'] !== '' ? ' ' . $f['unit'] : '') . '.';
                continue;
            }
            $values[$key] = $n;
        }
        if (isset($values['span'], $values['chord']) &&$values['chord'] > $values['span']) {
            $errors['chord'] = 'Corde c : elle ne peut pas dépasser l\'envergure.';
        }
        return [$values, $errors];
    }

    /**
     * @param array<string,float> $v entrées validées
     * @return array<string,float>
     */
    public static function compute(array $v): array
    {
        $b = $v['span'];
        $S = $b * $v['chord'];
        $AR = $b * $b / $S;
        $q = 0.5 * $v['rho'] * $v['speed'] ** 2;
        $CL = $v['mass'] * self::G / ($q * $S);
        $hb = $v['height'] / $b;
        $phi = self::phi($v['height'], $b);
        $cdiFree = $CL ** 2 / (M_PI * $AR * $v['e']);
        $cdiGround = $phi * $cdiFree;
        $cdFree = $v['cd0'] + $cdiFree;
        $cdGround = $v['cd0'] + $cdiGround;
        return [
            'S' => $S, 'AR' => $AR, 'q' => $q, 'CL' => $CL, 'hb' => $hb, 'phi' => $phi,
            'cdi_free' => $cdiFree, 'cdi_ground' => $cdiGround,
            'reduction' => (1.0 - $phi) * 100.0,
            'cd_free' => $cdFree, 'cd_ground' => $cdGround,
            'ld_free' => $CL / $cdFree, 'ld_ground' => $CL / $cdGround,
            'weight' => $v['mass'] * self::G,
        ];
    }

    /**
     * Courbe L/D selon h/b (CL et CD0 constants).
     *
     * @param array<string,float> $v
     * @return list<array{hb:float,phi:float,ld:float}>
     */
    public static function curve(array $v, int $points = 50, float $maxHb = 1.0): array
    {
        $r = self::compute($v);
        $cdi = $r['cdi_free'];
        $out = [];
        for ($i = 1; $i <= $points; $i++) {
            $hb = $maxHb * $i / $points;
            $phi = self::phi($hb, 1.0);
            $out[] = ['hb' => $hb, 'phi' => $phi, 'ld' => $r['CL'] / ($v['cd0'] + $phi * $cdi)];
        }
        return $out;
    }

    /** Nombre au format français. */
    public static function fmt(float $x, int $decimals = 3): string
    {
        return number_format($x, $decimals, ',', "\u{202F}");
    }

    /** Graduation « ronde » : retourne [max, pas] pour environ 5 graduations. */
    public static function niceScale(float $max): array
    {
        if ($max <= 0) {
            return [1.0, 0.2];
        }
        $raw = $max / 5.0;
        $mag = 10 ** floor(log10($raw));
        $norm = $raw / $mag;
        $step = ($norm <= 1 ? 1 : ($norm <= 2 ? 2 : ($norm <= 5 ? 5 : 10))) * $mag;
        return [ceil($max / $step) * $step, $step];
    }

    /**
     * Graphique SVG accessible (title, desc) généré côté serveur. Les couleurs viennent de admin.css (classes).
     *
     * @param list<array{hb:float,phi:float,ld:float}> $curve
     * @param array<string,float> $res
     */
    public static function svg(array $curve, array $res): string
    {
        $w = 640;
        $h = 360;
        $ml = 56;
        $mr = 16;
        $mt = 16;
        $mb = 46;
        $pw = $w - $ml - $mr;
        $ph = $h - $mt - $mb;
        $maxLd = max($res['ld_free'], ...array_column($curve, 'ld'));
        [$yMax, $yStep] = self::niceScale($maxLd * 1.05);
        $x = static fn (float $hb): float => $ml + $pw * min(1.0, max(0.0, $hb));
        $y = static fn (float $ld): float => $mt + $ph * (1.0 - $ld / $yMax);
        $esc = static fn (string $s): string => htmlspecialchars($s, ENT_QUOTES, 'UTF-8');

        $s = '<svg class="chart" viewBox="0 0 ' . $w . ' ' . $h . '" role="img" aria-labelledby="chart-title chart-desc" xmlns="http://www.w3.org/2000/svg">';
        $s .= '<title id="chart-title">Finesse L/D en fonction de la hauteur relative h/b</title>';
        $s .= '<desc id="chart-desc">' . $esc(
            'Courbe de la finesse portance/traînée en effet de sol pour h/b de 0 à 1, comparée à la finesse hors effet de sol ('
            . self::fmt($res['ld_free'], 1) . '). Au point choisi (h/b = ' . self::fmt($res['hb'], 3) . '), la finesse en effet de sol est de '
            . self::fmt($res['ld_ground'], 1) . '. Modèle simplifié, résultat indicatif. Les valeurs sont aussi données dans le tableau ci-dessous.'
        ) . '</desc>';

        for ($t = 0.0; $t <= $yMax + 1e-9; $t += $yStep) {
            $yy = round($y($t), 1);
            $s .= '<line class="chart-grid" x1="' . $ml . '" y1="' . $yy . '" x2="' . ($w - $mr) . '" y2="' . $yy . '"/>';
            $s .= '<text class="chart-tick" x="' . ($ml - 6) . '" y="' . ($yy + 4) . '" text-anchor="end">' . $esc(self::fmt($t, $yStep < 1 ? 1 : 0)) . '</text>';
        }
        for ($i = 0; $i <= 5; $i++) {
            $hb = $i / 5;
            $xx = round($x($hb), 1);
            $s .= '<line class="chart-grid" x1="' . $xx . '" y1="' . $mt . '" x2="' . $xx . '" y2="' . ($mt + $ph) . '"/>';
            $s .= '<text class="chart-tick" x="' . $xx . '" y="' . ($mt + $ph + 16) . '" text-anchor="middle">' . $esc(self::fmt($hb, 1)) . '</text>';
        }
        $s .= '<line class="chart-axis" x1="' . $ml . '" y1="' . ($mt + $ph) . '" x2="' . ($w - $mr) . '" y2="' . ($mt + $ph) . '"/>';
        $s .= '<line class="chart-axis" x1="' . $ml . '" y1="' . $mt . '" x2="' . $ml . '" y2="' . ($mt + $ph) . '"/>';
        $s .= '<text class="chart-label" x="' . ($ml + $pw / 2) . '" y="' . ($h - 6) . '" text-anchor="middle">h/b (hauteur relative)</text>';
        $s .= '<text class="chart-label" x="14" y="' . ($mt + $ph / 2) . '" text-anchor="middle" transform="rotate(-90 14 ' . ($mt + $ph / 2) . ')">Finesse L/D</text>';

        $yFree = round($y($res['ld_free']), 1);
        $s .= '<line class="chart-free" x1="' . $ml . '" y1="' . $yFree . '" x2="' . ($w - $mr) . '" y2="' . $yFree . '" stroke-dasharray="6 4"/>';
        $pts = [];
        foreach ($curve as $p) {
            $pts[] = round($x($p['hb']), 1) . ',' . round($y($p['ld']), 1);
        }
        $s .= '<polyline class="chart-ground" fill="none" points="' . implode(' ', $pts) . '"/>';
        if ($res['hb'] <= 1.0) {
            $s .= '<circle class="chart-point" r="5" cx="' . round($x($res['hb']), 1) . '" cy="' . round($y($res['ld_ground']), 1) . '"/>';
        }
        $s .= '</svg>';
        return $s;
    }
}
