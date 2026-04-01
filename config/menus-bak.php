<?php
// config/menus.php

return


[
    'main_menu' => [
        [
            'header' => 'User',
        ],
        [
            'title' => 'Perusahaan',
            'icon' => 'ki-filled ki-setting-2',
            'permission' => ['users-read', 'edit-account'], // Parent will show if user has at least one of these
            'url' => null, // Parent item with children
            'pathUrl' =>['departement*'],
            'children' => [
                [
                    'title' => 'Account Home',
                    'url' => '#',
                    'permission' => ['users-read'], // Child permission check
                    'children' => [
                        [
                            'title' => 'Get Started',
                            'url' => 'departement.index',
                            'pathUrl' =>['departement*'],
                            'permission' => ['users-read'],
                        ],
                    ],
                ],
                [
                    'title' => 'Edit Account',
                    'url' => 'roles.index',
                    'pathUrl' =>['roles*'],
                    'permission' => ['users-read'],
                ],
            ],
        ],
        [
            'header' => 'Admin Section',
            'children' => [
                [
                    'title' => 'Admin Dashboard',
                    'url' => '#',
                    'permission' => ['view-admin-dashboard'],
                ],
            ],
        ]
    ]
];
