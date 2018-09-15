<?php

if (!env('DB_DATABASE')) {
    if (env('DATABASE_URL')) {
        $url = parse_url(env('DATABASE_URL'));
    } elseif (env('JAWSDB_URL')) {
        $url = parse_url(env('JAWSDB_URL'));
    }
}

if (env('REDIS_URL')) {
    $redisUrl = parse_url(env('REDIS_URL'));
} elseif (env('REDISCLOUD_URL')) {
    $redisUrl = parse_url(env('REDISCLOUD_URL'));
}

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

    'default' => env('DB_CONNECTION', isset($url) ? $url['scheme'] : 'mysql'),

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

        'sqlite' => [
            'driver' => 'sqlite',
            'database' => env('DB_DATABASE', database_path('database.sqlite')),
            'prefix' => '',
        ],

        'mysql' => [
            'driver' => 'mysql',
            'host' => env('DB_HOST', isset($url) ? $url['host'] : '127.0.0.1'),
            'port' => env('DB_PORT', isset($url) ? $url['port'] : '3306'),
            'database' => env('DB_DATABASE', isset($url) ? ltrim($url['path'], '/') : 'forge'),
            'username' => env('DB_USERNAME', isset($url) ? $url['user'] : 'forge'),
            'password' => env('DB_PASSWORD', isset($url) ? $url['pass'] : ''),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => true,
            'engine' => null,
        ],

        'pgsql' => [
            'driver' => 'pgsql',
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '5432'),
            'database' => env('DB_DATABASE', 'forge'),
            'username' => env('DB_USERNAME', 'forge'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => 'utf8',
            'prefix' => '',
            'schema' => 'public',
            'sslmode' => 'prefer',
        ],

        'sqlsrv' => [
            'driver' => 'sqlsrv',
            'host' => env('DB_HOST', 'localhost'),
            'port' => env('DB_PORT', '1433'),
            'database' => env('DB_DATABASE', 'forge'),
            'username' => env('DB_USERNAME', 'forge'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => 'utf8',
            'prefix' => '',
        ],

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

        'default' => [
            'host' => env('REDIS_HOST', isset($redisUrl) ? $redisUrl['host'] : '127.0.0.1'),
            'password' => env('REDIS_PASSWORD', isset($redisUrl) ? $redisUrl['pass'] : null),
            'port' => env('REDIS_PORT', isset($redisUrl) ? $redisUrl['port'] : 6379),
            'database' => 0,
        ],

    ],

];
