<?php
declare(strict_types=1);

namespace App\Modules\Calculator;

use App\Admin\Context;
use App\Admin\Ui;

/** Calculateur d'effet de sol (GET uniquement : aucun effet de bord, donc pas de CSRF). */
final class Controller
{
    /** Retourne le contenu HTML (le Router l'enveloppe dans la mise en page admin). */
    public function index(Context $ctx): string
    {
        $fields = GroundEffect::fields();
        $preset = $ctx->q('preset') === 'v6';
        $submitted = $preset || $ctx->q('calc') === '1';

        $form = [];
        foreach ($fields as $key => $f) {
            $form[$key] = $preset ? GroundEffect::presetV6()[$key] : $ctx->q($key);
        }
        if (!$submitted && !array_filter($form, static fn (string $s): bool => $s !== '')) {
            $form = GroundEffect::presetV6();
        }

        $errors = [];
        $result = null;
        $curve = [];
        $svg = '';
        $warnings = [];
        if ($submitted) {
            [$values, $errors] = GroundEffect::parse($form);
            if ($errors === []) {
                $result = GroundEffect::compute($values);
                $curve = GroundEffect::curve($values, 50);
                $svg = GroundEffect::svg($curve, $result);
                if ($result['hb'] > 0.5) {
                    $warnings[] = 'h/b dépasse 0,5 : le modèle de McCormick perd en pertinence loin du sol.';
                }
                if ($result['CL'] > 1.5) {
                    $warnings[] = 'Le CL calculé est très élevé (> 1,5) : vitesse trop faible pour cette masse, résultat peu réaliste.';
                }
            }
        }

        return Ui::renderFile(__DIR__ . '/views/index.php', [
            'fields' => $fields,
            'form' => $form,
            'errors' => $errors,
            'result' => $result,
            'curve' => $curve,
            'svg' => $svg,
            'warnings' => $warnings,
            'preset' => $preset,
        ]);
    }
}
