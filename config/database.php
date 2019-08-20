<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Database Connection Name
    |--------------------------------------------------------------------------
    |
    | Here you may specify which of the database connections below you wish
    | to use as your default connection for all database work. Of course
    | you may use many connections at once using the Database library.
    |
    */

    'default' => env('DB_CONNECTION', 'mysql'),

    /*
    |--------------------------------------------------------------------------
    | Database Connections
    |--------------------------------------------------------------------------
    |
    | Here are each of the database connections setup for your application.
    | Of course, examples of configuring each database platform that is
    | supported by Laravel is shown below to make development simple.
    |
    |
    | All database work in Laravel is done through the PHP PDO facilities
    | so make sure you have the driver for your particular database of
    | choice installed on your machine before you begin development.
    |
    */

    'connections' => [
        'mysql' => [
            'driver' => 'mysql',
            'host' => '127.0.0.1',
            'database' =>'caiwu',
            'username' =>'root',
            'password' =>'',
            'prefix' => 'caiwu_',
            'port' => '3306',
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'strict' => false,
            'engine' => null,
        ],
        'game'=>[
            'driver' => 'mysql',
            'host' => 'localhost',
            'database' =>'nh',
            'username' =>'root',
            'password' =>'',
            'prefix' => '',
            'port' => '3306',
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'strict' => true,
            'engine' => null,
        ],
        'mysql_web'=>[
            'driver' => 'mysql',
            'host' => '127.0.0.1',
            'database' =>'tgy',
            'username' =>'root',
            'password' =>'',
            'prefix' => '',
            'port' => '3306',
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'strict' => true,
            'engine' => null,
        ],
        'pay_master'=>[
            'driver' => 'mysql',
            'host' => '127.0.0.1',
            'database' =>'pay',
            'username' =>'root',
            'password' =>'',
            'prefix' => '',
            'port' => '3306',
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'strict' => true,
            'engine' => null,
        ]


    ],

    /*
    |--------------------------------------------------------------------------
    | Migration Repository Table
    |--------------------------------------------------------------------------
    |
    | This table keeps track of all the migrations that have already run for
    | your application. Using this information, we can determine which of
    | the migrations on disk haven't actually been run in the database.
    |
    */

    'migrations' => 'migrations',

    /*
    |--------------------------------------------------------------------------
    | Redis Databases
    |--------------------------------------------------------------------------
    |
    | Redis is an open source, fast, and advanced key-value store that also
    | provides a richer set of commands than a typical key-value systems
    | such as APC or Memcached. Laravel makes it easy to dig right in.
    |
    */

    'redis' => [

        'client' => 'predis',
        'default'=>[
            'host' =>'127.0.0.1',
            'port' => 6379,
            'database' => 0,
        ],

        'game_redis' =>[
            'host' =>'127.0.0.1',
            'port' => 6379,
            'database' => 0,
        ],

        'session'=>[
            'host' =>'127.0.0.1',
            'port' => 6379,
            'database' => 0,
        ]

    ],

];
