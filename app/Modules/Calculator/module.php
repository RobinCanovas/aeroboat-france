<?php
declare(strict_types=1);

return [
    'key' => 'calculator',
    'label' => 'Calculateur d\'effet de sol',
    'icon' => 'calculator',
    'route' => '/admin/modules/calculator',
    'roles' => ['admin', 'editor'],
    'controller' => \App\Modules\Calculator\Controller::class,
    'method' => 'index',
];
