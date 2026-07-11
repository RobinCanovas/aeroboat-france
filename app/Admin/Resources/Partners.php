<?php
declare(strict_types=1);

use App\Admin\Context;

/** Partenaires. Catégories : celles des clés de langue partners.categories.* */
return [
    'key' => 'partenaires',
    'table' => 'partners',
    'area' => 'partners',
    'label' => 'Partenaires',
    'singular' => 'partenaire',
    'title_field' => 'name',
    'timestamps' => true,
    'defaults' => ['category' => 'support', 'logo_consent' => 0, 'published' => 0, 'sort_order' => 0],
    'fields' => [
        'name' => ['type' => 'text', 'label' => 'Nom', 'required' => true, 'max' => 160],
        'category' => ['type' => 'select', 'label' => 'Catégorie', 'required' => true, 'options' => ['school' => 'École', 'technology' => 'Technologie', 'support' => 'Soutien']],
        'description_fr' => ['type' => 'textarea', 'label' => 'Description (français)', 'rows' => 4, 'max' => 2000],
        'description_en' => ['type' => 'textarea', 'label' => 'Description (anglais)', 'rows' => 4, 'max' => 2000],
        'url' => ['type' => 'url', 'label' => 'Site web (adresse complète)', 'max' => 250],
        'logo' => ['type' => 'image', 'label' => 'Logo', 'subdir' => 'partners', 'help' => 'Uniquement avec l\'accord du partenaire.'],
        'logo_consent' => ['type' => 'bool', 'label' => 'Accord du partenaire pour l\'affichage du logo'],
        'sort_order' => ['type' => 'int', 'label' => 'Ordre d\'affichage', 'min' => 0, 'max' => 9999, 'default' => 0, 'help' => 'Les petits nombres passent en premier.'],
        'published' => ['type' => 'bool', 'label' => 'Publié sur le site'],
    ],
    'groups' => [
        ['title' => 'Partenaire', 'fields' => ['name', 'category', 'url']],
        ['title' => 'Description', 'class' => 'col', 'fields' => ['description_fr']],
        ['title' => 'Description (EN)', 'class' => 'col', 'fields' => ['description_en']],
        ['title' => 'Logo et publication', 'fields' => ['logo', 'logo_consent', 'sort_order', 'published']],
    ],
    'list' => [
        'columns' => ['name', 'category', 'sort_order', 'published', 'logo_consent'],
        'search' => ['name'],
        'sort' => ['name', 'category', 'sort_order', 'published'],
        'default_sort' => ['sort_order', 'asc'],
        'filters' => ['category' => 'Catégorie'],
        'per_page' => 30,
    ],
    'before_save' => static function (array $v, ?array $ex, Context $ctx): array {
        $errors = [];
        if (!empty($v['logo']) && empty($v['logo_consent'])) {
            $errors['logo_consent'] = 'Cochez l\'accord du partenaire pour enregistrer un logo.';
        }
        // Règle du projet : la fiche « Un morceau de ciel bleu » reste non publiée.
        if (!empty($v['published']) && stripos((string) $v['name'], 'morceau de ciel bleu') !== false) {
            $errors['published'] = 'Cette fiche doit rester non publiée.';
        }
        return [$v, $errors];
    },
];
