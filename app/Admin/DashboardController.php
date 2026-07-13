<?php
declare(strict_types=1);

namespace App\Admin;

/** Tableau de bord : compteurs de nouveautés selon le rôle. */
final class DashboardController
{
    public static function index(Context $ctx): Reply
    {
        $role = $ctx->role();
        $cards = [];
        if (Policy::allowed($role, 'applications')) {
            $cards[] = ['label' => 'Nouvelles candidatures', 'n' => Store::count("SELECT COUNT(*) FROM {t:applications} WHERE status = 'new'"), 'href' => Ui::u('/candidatures?status=new')];
            $cards[] = ['label' => 'Candidatures en cours (examen ou entretien)', 'n' => Store::count("SELECT COUNT(*) FROM {t:applications} WHERE status IN ('reviewing','interview')"), 'href' => Ui::u('/candidatures')];
        }
        if (Policy::allowed($role, 'messages')) {
            $cards[] = ['label' => 'Messages de contact à traiter', 'n' => Store::count("SELECT COUNT(*) FROM {t:messages} WHERE kind = 'contact' AND handled = 0"), 'href' => Ui::u('/messages?kind=contact&state=open')];
            $cards[] = ['label' => 'Demandes de droits à traiter (délai : un mois)', 'n' => Store::count("SELECT COUNT(*) FROM {t:messages} WHERE kind = 'rights' AND handled = 0"), 'href' => Ui::u('/messages?kind=rights&state=open'), 'urgent' => true];
        }
        if (Policy::allowed($role, 'posts')) {
            $cards[] = ['label' => 'Articles en brouillon', 'n' => Store::count("SELECT COUNT(*) FROM {t:posts} WHERE status = 'draft'"), 'href' => Ui::u('/articles?f_status=draft')];
            $cards[] = ['label' => 'Articles programmés', 'n' => Store::count("SELECT COUNT(*) FROM {t:posts} WHERE status = 'scheduled'"), 'href' => Ui::u('/articles?f_status=scheduled')];
        }
        return Ui::page($ctx, 'Tableau de bord', 'dashboard', ['cards' => $cards], 'dashboard');
    }
}
