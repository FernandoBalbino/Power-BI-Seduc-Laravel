<?php

return [
    'roles' => [
        'admin' => 'Administrador',
        'setor' => 'Setor',
    ],

    'menu' => [
        [
            'label' => 'Meus Dashboards',
            'route' => 'dashboards.index',
            'active' => ['dashboard', 'dashboards.index', 'dashboards.show', 'dashboards.edit-basic', 'dashboards.edit'],
            'icon' => 'layout-dashboard',
        ],
        [
            'label' => 'Criar Dashboard',
            'route' => 'dashboards.create',
            'active' => ['dashboards.create'],
            'icon' => 'plus-circle',
        ],
        [
            'label' => 'Importações',
            'route' => 'dashboards.index',
            'active' => ['dashboards.import', 'dashboards.feed'],
            'icon' => 'cloud-upload',
        ],
        [
            'label' => 'Setores',
            'route' => 'admin.sectors.index',
            'active' => ['admin.sectors.*'],
            'icon' => 'building-2',
        ],
        [
            'label' => 'Usuários',
            'route' => 'admin.users.index',
            'active' => ['admin.users.*'],
            'icon' => 'users',
        ],
        [
            'label' => 'Configurações',
            'route' => 'profile',
            'active' => ['profile'],
            'icon' => 'settings',
        ],
    ],

    'friendly_column_types' => [
        'Texto curto',
        'Texto longo',
        'Número',
        'Dinheiro',
        'Porcentagem',
        'Data',
        'Opção/Categoria',
        'Código/Identificador',
        'Sim/Não',
        'Ignorar coluna',
    ],

    'chart_colors' => [
        '#0D6EFD',
        '#16A34A',
        '#EF4444',
        '#F59E0B',
        '#7C3AED',
        '#06B6D4',
        '#64748B',
    ],

    'imports' => [
        'max_upload_kb' => env('SEDUC_BI_IMPORT_MAX_UPLOAD_KB', 10240),
        'preview_rows' => 20,
    ],
];
