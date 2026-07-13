<?php
declare(strict_types=1);

use App\Admin\Context;

/** Équipe : membres affichés sur la page publique. */
return [
    'key' => 'equipe',
    'table' => 'team_members',
    'area' => 'team',
    'label' => 'Équipe',
    'singular' => 'membre',
    'title_field' => 'name',
    'timestamps' => true,
    'defaults' => ['photo_consent' => 0, 'published' => 0, 'sort_order' => 0],
    'fields' => [
        'name' => ['type' => 'text', 'label' => 'Nom affiché', 'required' => true, 'max' => 120],
        'role_fr' => ['type' => 'text', 'label' => 'Rôle (français)', 'max' => 160],
        'role_en' => ['type' => 'text', 'label' => 'Rôle (anglais)', 'max' => 160],
        'bio_fr' => ['type' => 'textarea', 'label' => 'Présentation (français)', 'rows' => 5, 'max' => 2000],
        'bio_en' => ['type' => 'textarea', 'label' => 'Présentation (anglais)', 'rows' => 5, 'max' => 2000],
        'photo' => ['type' => 'image', 'label' => 'Photo', 'subdir' => 'team', 'help' => 'Uniquement avec le consentement de la personne.'],
        'photo_consent' => ['type' => 'bool', 'label' => 'Consentement photo obtenu'],
        'linkedin' => ['type' => 'url', 'label' => 'Profil LinkedIn (adresse complète)', 'max' => 250],
        'sort_order' => ['type' => 'int', 'label' => 'Ordre d\'affichage', 'min' => 0, 'max' => 9999, 'default' => 0, 'help' => 'Les petits nombres passent en premier.'],
        'published' => ['type' => 'bool', 'label' => 'Publié sur le site'],
    ],
    'groups' => [
        ['title' => 'Identité', 'fields' => ['name', 'role_fr', 'role_en', 'linkedin']],
        ['title' => 'Présentation', 'class' => 'col', 'fields' => ['bio_fr']],
        ['title' => 'Présentation (EN)', 'class' => 'col', 'fields' => ['bio_en']],
        ['title' => 'Photo et publication', 'fields' => ['photo', 'photo_consent', 'sort_order', 'published']],
    ],
    'list' => [
        'columns' => ['name', 'role_fr', 'sort_order', 'published', 'photo_consent'],
        'search' => ['name', 'role_fr'],
        'sort' => ['name', 'sort_order', 'published', 'updated_at'],
        'default_sort' => ['sort_order', 'asc'],
        'filters' => [],
        'per_page' => 30,
    ],
    'before_save' => static function (array $v, ?array $ex, Context $ctx): array {
        $errors = [];
        if (!empty($v['photo']) && empty($v['photo_consent'])) {
            $errors['photo_consent'] = 'Cochez le consentement photo pour enregistrer une photo.';
        }
        return [$v, $errors];
    },
];
