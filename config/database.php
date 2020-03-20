<?php
return[
    'default' => env('DB_CONNECTION', 'mysql_pedidos'),
    'migrations' => 'migrations',
    'connections' => [
        'mysql_auto_stock' => [
            'driver' => 'mysql',
            'host' => env('DB_HOST_AUTOSTOCK', 'localhost'),
            'port' => env('DB_PORT_AUTOSTOCK', '3306'),
            'database' => env('DB_DATABASE_AUTOSTOCK', 'db'),
            'username' => env('DB_USERNAME_AUTOSTOCK', 'user'),
            'password' => env('DB_PASSWORD_AUTOSTOCK', 'pass'),
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
        ],
        'mysql_pedidos' => [
            'driver' => 'mysql',
            'host' => env('DB_HOST_PEDIDOS', 'localhost'),
            'port' => env('DB_PORT_PEDIDOS', '3306'),
            'database' => env('DB_DATABASE_PEDIDOS', 'db'),
            'username' => env('DB_USERNAME_PEDIDOS', 'user'),
            'password' => env('DB_PASSWORD_PEDIDOS', 'pass'),
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
        ] 
    ]
];