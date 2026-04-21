<?php
// config/menus.php

return


    [
        'main_menu' => [
            [
                'title' => 'RAB',
                'icon' => 'ki-filled ki-document',
                'permission' => ['rab-read'],
                'route' => 'rab.index',
                'pathUrl' => ['rab*'],
                'children' => [
                    [
                        'title' => 'Dashboard RAB',
                        'route' => 'rab.dashboard',
                        'permission' => ['rab-read'],
                        'pathUrl' => ['rab/dashboard*'],
                    ],
                    [
                        'title' => 'Daftar RAB',
                        'route' => 'rab.index',
                        'permission' => ['rab-read'],
                        'pathUrl' => ['rab', 'rab/create', 'rab/*/edit'],
                    ],

                ],
            ],
            [
                'title' => 'Pengajuan Pembelian',
                'icon' => 'ki-filled ki-purchase',
                'permission' => ['purchase-request-read'],
                'route' => 'purchase-requests.index',
                'pathUrl' => ['purchase-requests*'],
                'children' => [
                    [
                        'title' => 'Dashboard',
                        'route' => 'purchase-requests.dashboard',
                        'permission' => ['purchase-request-read'],
                        'pathUrl' => ['purchase-requests/dashboard*'],
                    ],
                    [
                        'title' => 'Daftar Pengajuan',
                        'route' => 'purchase-requests.index',
                        'permission' => ['purchase-request-read'],
                        'pathUrl' => ['purchase-requests', 'purchase-requests/create', 'purchase-requests/*/edit'],
                    ],
                ],
            ],
            // [
            //     'title' => 'Beranda',
            //     'icon' => 'ki-filled ki-element-11',
            //     'permission' => null,
            //     'route' => 'dashboard',
            //     'pathUrl' => ['/dashboard', 'dashboard']
            // ],
            // [
            //     'title' => 'Konfirmasi Pembayaran',
            //     'icon' => 'ki-filled ki-element-11',
            //     'permission' => null,
            //     'route' => 'admin.payment-confirmations.index',
            //     'pathUrl' => ['admin/payment-confirmations*']
            // ],
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

            // [
            //     'header' => 'Administrator',
            //     'permission' => ['location-read','user-read','role-read','permission-read'],
            // ],
            // [
            //     'title' => 'Lokasi',
            //     'icon' => 'ki-filled ki-geolocation',
            //     'permission' => ['location-read'],
            //     'route' => 'location.index',
            //     'pathUrl' => ['location*']
            // ],
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
