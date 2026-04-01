<?php
// config/menus.php

return


    [
        'main_menu' => [
            [
                'title' => 'Beranda',
                'icon' => 'ki-filled ki-element-11',
                'permission' => null,
                'route' => 'dashboard',
                'pathUrl' => ['/dashboard', 'dashboard']
            ],
            [
                'title' => 'Konfirmasi Pembayaran',
                'icon' => 'ki-filled ki-element-11',
                'permission' => null,
                'route' => 'admin.payment-confirmations.index',
                'pathUrl' => ['admin/payment-confirmations*']
            ],
            // [
            //     'header' => 'Aktifitas Saya',
            // ],
            // [
            //     'title' => 'Kehadiran',
            //     'icon' => 'ki-filled ki-calendar-tick',
            //     'permission' => null,
            //     'route' => 'my-activity.my-attendance.index',
            //     'pathUrl' => ['my-activity/my-attendance*']
            // ],
            // [
            //     'header' => 'Musyrif',
            //     'permission' => ['evaluation-create'],
            // ],
            [
                'title' => 'Tahsin Tilawah',
                'icon' => 'ki-filled ki-book',
                'permission' => ['evaluation-create'],
                'route' => 'forms.create.tahsin-tilawah',
                'pathUrl' => ['forms/tahsin-tilawah*']
            ],
            // [
            //     'header' => 'Administrator',
            //     'permission' => ['location-read','user-read','role-read','permission-read'],
            // ],
            [
                'title' => 'Lokasi',
                'icon' => 'ki-filled ki-geolocation',
                'permission' => ['location-read'],
                'route' => 'location.index',
                'pathUrl' => ['location*']
            ],
            [
                'title' => 'User',
                'icon' => 'ki-filled ki-security-user',
                'permission' => ['user-read'],
                'route' => 'users.index',
                'pathUrl' => ['user*']
            ],
            [
                'title' => 'Roles & Permissions',
                'icon' => 'ki-filled ki-bank',
                'permission' => [ 'role-read', 'permission-read', 'permission-group-read'],
                'route' => null,
                'pathUrl' => [ 'roles*', 'permission*'],
                'children' => [

                    [
                        'title' => 'Roles',
                        'route' => 'roles.index',
                        'permission' => ['role-read'],
                        'pathUrl' => ['roles*'],
                    ],
                    [
                        'title' => 'Hak Akses',
                        'route' => null,
                        'permission' => ['permission-read', 'permission-group-read'],
                        'pathUrl' => ['permission*'],
                        'children' => [
                            [
                                'title' => 'Grup Hak Akses',
                                'route' => 'permission-groups.index',
                                'pathUrl' => ['permission-groups*'],
                                'permission' => ['permission-group-read'],
                            ],
                            [
                                'title' => 'Hak Akses',
                                'route' => 'permissions.index',
                                'pathUrl' => ['permissions*'],
                                'permission' => ['permission-read'],
                            ],
                        ],
                    ],
                ],
            ]
        ]
    ];
