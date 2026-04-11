<?php

use Illuminate\Support\Str;

return [

    'default' => env('DB_CONNECTION', 'mysql'),

    'connections' => [

        'sqlite' => [
            'driver'   => 'sqlite',
            'url'      => env('DATABASE_URL'),
            'database' => env('DB_DATABASE', database_path('database.sqlite')),
            'prefix'   => '',
            'foreign_key_constraints' => env('DB_FOREIGN_KEYS', true),
        ],

        'mysql' => [
            'driver'         => 'mysql',
            'url'            => env('DATABASE_URL'),
            'host'           => env('DB_HOST', 'mysql'),
            'port'           => env('DB_PORT', '3306'),
            'database'       => env('DB_DATABASE', 'devflow'),
            'username'       => env('DB_USERNAME', 'devflow'),
            'password'       => env('DB_PASSWORD', ''),
            'unix_socket'    => env('DB_SOCKET', ''),
            'charset'        => 'utf8mb4',
            'collation'      => 'utf8mb4_unicode_ci',
            'prefix'         => '',
            'prefix_indexes' => true,
            'strict'         => true,
            'engine'         => null,
            'options'        => extension_loaded('pdo_mysql') ? array_filter([
                PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]) : [],
        ],

        // Read replica connection (DevOps practice: horizontal DB scaling)
        'mysql_read' => [
            'driver'   => 'mysql',
            'host'     => env('DB_READ_HOST', env('DB_HOST', 'mysql')),
            'port'     => env('DB_PORT', '3306'),
            'database' => env('DB_DATABASE', 'devflow'),
            'username' => env('DB_USERNAME', 'devflow'),
            'password' => env('DB_PASSWORD', ''),
            'charset'  => 'utf8mb4',
            'collation'=> 'utf8mb4_unicode_ci',
            'prefix'   => '',
            'strict'   => true,
            'engine'   => null,
        ],
    ],

    'migrations' => [
        'table'       => 'migrations',
        'update_date_on_publish' => true,
    ],

    'redis' => [

        'client' => env('REDIS_CLIENT', 'predis'),

        'options' => [
            'cluster'  => env('REDIS_CLUSTER', 'redis'),
            'prefix'   => env('REDIS_PREFIX', Str::slug(env('APP_NAME', 'laravel'), '_').'_database_'),
        ],

        // Cache store (db 0)
        'default' => [
            'url'      => env('REDIS_URL'),
            'host'     => env('REDIS_HOST', 'redis'),
            'username' => env('REDIS_USERNAME'),
            'password' => env('REDIS_PASSWORD'),
            'port'     => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_DB', '0'),
        ],

        // Cache (db 1)
        'cache' => [
            'url'      => env('REDIS_URL'),
            'host'     => env('REDIS_HOST', 'redis'),
            'username' => env('REDIS_USERNAME'),
            'password' => env('REDIS_PASSWORD'),
            'port'     => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_CACHE_DB', '1'),
        ],

        // Queue (db 2)
        'queue' => [
            'url'      => env('REDIS_URL'),
            'host'     => env('REDIS_HOST', 'redis'),
            'username' => env('REDIS_USERNAME'),
            'password' => env('REDIS_PASSWORD'),
            'port'     => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_QUEUE_DB', '2'),
        ],
    ],

];
