<?php

return [
    [
        'key' => 'users',
        'label' => 'Users',
        'icon' => 'users', // placeholder, could be replaced with an icon system
        'children' => [
            [
                'label' => 'All Users',
                'route' => 'admin.users.index',
                'fallback' => '/admin/users',
            ],
            [
                'label' => 'Create User',
                'route' => 'admin.users.create',
                'fallback' => '/admin/users/create',
            ],
        ],
    ],
    [
        'key' => 'roles',
        'label' => 'Roles',
        'icon' => 'shield-check',
        'children' => [
            [
                'label' => 'All Roles',
                'route' => 'admin.roles.index',
                'fallback' => '/admin/roles',
            ],
            [
                'label' => 'Create Role',
                'route' => 'admin.roles.create',
                'fallback' => '/admin/roles/create',
            ],
        ],
    ],
    [
        'key' => 'settings',
        'label' => 'Settings',
        'icon' => 'cog-6-tooth',
        'children' => [
            [
                'label' => 'General',
                'route' => 'admin.settings.general',
                'fallback' => '/admin/settings/general',
            ],
            [
                'label' => 'Payment',
                'route' => 'admin.settings.payment',
                'fallback' => '/admin/settings/payment',
            ],
        ],
    ],
];
