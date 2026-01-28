<?php

use Illuminate\Support\Str;

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

        'sqlite' => [
            'driver' => 'sqlite',
            'url' => env('DATABASE_URL'),
            'database' => env('DB_DATABASE', database_path('database.sqlite')),
            'prefix' => '',
            'foreign_key_constraints' => env('DB_FOREIGN_KEYS', true),
        ],

        'mysql' => [
            'driver' => 'mysql',
            'url' => env('DATABASE_URL'),
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => env('DB_DATABASE', 'forge'),
            'username' => env('DB_USERNAME', 'forge'),
            'password' => env('DB_PASSWORD', ''),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]) : [],
        ],

        'mysql2' => [
            'driver' => 'mysql',
            'url' => env('DATABASE_URL_2'),
            'host' => env('DB_HOST_2', '127.0.0.1'),
            'port' => env('DB_PORT_2', '3306'),
            'database' => env('DB_DATABASE_2', 'forge'),
            'username' => env('DB_USERNAME_2', 'forge'),
            'password' => env('DB_PASSWORD_2', ''),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]) : [],
        ],

        'mysql3' => [
            'driver' => 'mysql',
            'url' => env('DATABASE_URL_3'),
            'host' => env('DB_HOST_3', '127.0.0.1'),
            'port' => env('DB_PORT_3', '3306'),
            'database' => env('DB_DATABASE_3', 'forge'),
            'username' => env('DB_USERNAME_3', 'forge'),
            'password' => env('DB_PASSWORD_3', ''),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]) : [],
        ],

        'mysql4' => [
            'driver' => 'mysql',
            'url' => env('DATABASE_URL_4'),
            'host' => env('DB_HOST_4', '127.0.0.1'),
            'port' => env('DB_PORT_4', '3306'),
            'database' => env('DB_DATABASE_4', 'forge'),
            'username' => env('DB_USERNAME_4', 'forge'),
            'password' => env('DB_PASSWORD_4', ''),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]) : [],
        ],


        // 'pgsql' => [
        //     'driver' => 'pgsql',
        //     'url' => env('DATABASE_URL'),
        //     'host' => env('DB_HOST', '127.0.0.1'),
        //     'port' => env('DB_PORT', '5432'),
        //     'database' => env('DB_DATABASE', 'forge'),
        //     'username' => env('DB_USERNAME', 'forge'),
        //     'password' => env('DB_PASSWORD', ''),
        //     'charset' => 'utf8',
        //     'prefix' => '',
        //     'prefix_indexes' => true,
        //     'search_path' => 'public',
        //     'sslmode' => 'prefer',
        // ],

        'pgsql' => [
            'driver' => 'pgsql',
            'host' => env('DB_PGSQL_HOST', '127.0.0.1'),
            'port' => env('DB_PGSQL_PORT', '5432'),
            'database' => env('DB_PGSQL_DATABASE', 'forge'),
            'username' => env('DB_PGSQL_USERNAME', 'forge'),
            'password' => env('DB_PGSQL_PASSWORD', ''),
            'charset' => 'utf8',
            'prefix' => '',
            'schema' => 'public',
            'sslmode' => 'prefer',
        ],

        'pgsql2' => [
            'driver' => 'pgsql',
            'host' => env('DB_PGSQL_HOST_2', '127.0.0.1'),
            'port' => env('DB_PGSQL_PORT_2', '5432'),
            'database' => env('DB_PGSQL_DATABASE_2', 'forge'),
            'username' => env('DB_PGSQL_USERNAME_2', 'forge'),
            'password' => env('DB_PGSQL_PASSWORD_2', ''),
            'charset' => 'utf8',
            'prefix' => '',
            'schema' => 'public',
            'sslmode' => 'prefer',
        ],
        
        'pgsql3' => [
            'driver' => 'pgsql',
            'host' => env('DB_PGSQL_HOST_3', '127.0.0.1'),
            'port' => env('DB_PGSQL_PORT_3', '5432'),
            'database' => env('DB_PGSQL_DATABASE_3', 'forge'),
            'username' => env('DB_PGSQL_USERNAME_3', 'forge'),
            'password' => env('DB_PGSQL_PASSWORD_3', ''),
            'charset' => 'utf8',
            'prefix' => '',
            'schema' => 'public',
            'sslmode' => 'prefer',
        ],

        // 'sqlsrv' => [
        //     'driver' => 'sqlsrv',
        //     'url' => env('DATABASE_URL'),
        //     'host' => env('DB_HOST_1', '127.0.0.1'),
        //     'port' => env('DB_PORT_1', '1433'),
        //     'database' => env('DB_DATABASE_1', 'forge'),
        //     'username' => env('DB_USERNAME_1', 'forge'),
        //     'password' => env('DB_PASSWORD_1', ''),
        //     'charset' => 'utf8',
        //     'prefix' => '',
        //     'prefix_indexes' => true,
        //     // 'encrypt' => env('DB_ENCRYPT', 'yes'),
        //     // 'trust_server_certificate' => env('DB_TRUST_SERVER_CERTIFICATE', 'false'),
        // ],

        // 'sqlsrv2' => [
        //     'driver' => 'sqlsrv',
        //     'url' => env('DATABASE_URL'),
        //     'host' => env('DB_HOST_2', '127.0.0.1'),
        //     'port' => env('DB_PORT_2', '1433'),
        //     'database' => env('DB_DATABASE_2', 'forge'),
        //     'username' => env('DB_USERNAME_2', 'forge'),
        //     'password' => env('DB_PASSWORD_2', ''),
        //     'charset' => 'utf8',
        //     'prefix' => '',
        //     'prefix_indexes' => true,
        // ],        

        // config/database.php

        'sqlsrv5' => [
            'driver' => 'sqlsrv',
            'host' => env('DB_HOST_5', '127.0.0.1'),
            'port' => env('DB_PORT_5', '1433'),
            'database' => env('DB_DATABASE_5', 'forge'),
            'username' => env('DB_USERNAME_5', 'forge'),
            'password' => env('DB_PASSWORD_5', ''),
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
            'encrypt' => env('DB5_ENCRYPT', true),
            'trust_server_certificate' => env('DB1_TRUST_SERVER_CERTIFICATE', true),

            // Hanya set atribut yang memang ada di driver
            'options' => extension_loaded('sqlsrv') ? (function () {
                $o = [];
                if (defined('PDO::SQLSRV_ATTR_QUERY_TIMEOUT')) {
                    // timeout untuk eksekusi query (detik)
                    $o[PDO::SQLSRV_ATTR_QUERY_TIMEOUT] = (int) env('DB1_QUERY_TIMEOUT', 5);
                }
                if (defined('PDO::SQLSRV_ATTR_DIRECT_QUERY')) {
                    $o[PDO::SQLSRV_ATTR_DIRECT_QUERY] = true;
                }
                return $o;
            })() : [],
        ],

        'sqlsrv6' => [
            'driver' => 'sqlsrv',
            'host' => env('DB_HOST_6', '127.0.0.1'),
            'port' => env('DB_PORT_6', '1433'),
            'database' => env('DB_DATABASE_6', 'forge'),
            'username' => env('DB_USERNAME_6', 'forge'),
            'password' => env('DB_PASSWORD_6', ''),
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
            'encrypt' => env('DB6_ENCRYPT', true),
            'trust_server_certificate' => env('DB2_TRUST_SERVER_CERTIFICATE', true),

            'options' => extension_loaded('sqlsrv') ? (function () {
                $o = [];
                if (defined('PDO::SQLSRV_ATTR_QUERY_TIMEOUT')) {
                    $o[PDO::SQLSRV_ATTR_QUERY_TIMEOUT] = (int) env('DB2_QUERY_TIMEOUT', 5);
                }
                if (defined('PDO::SQLSRV_ATTR_DIRECT_QUERY')) {
                    $o[PDO::SQLSRV_ATTR_DIRECT_QUERY] = true;
                }
                return $o;
            })() : [],
        ],

        'mysql7' => [
            'driver' => 'mysql',
            'url' => env('DATABASE_URL_7'),
            'host' => env('DB_HOST_7', '127.0.0.1'),
            'port' => env('DB_PORT_7', '3306'),
            'database' => env('DB_DATABASE_7', 'forge'),
            'username' => env('DB_USERNAME_7', 'forge'),
            'password' => env('DB_PASSWORD_7', ''),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]) : [],
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
    | provides a richer body of commands than a typical key-value system
    | such as APC or Memcached. Laravel makes it easy to dig right in.
    |
    */

    'redis' => [

        'client' => env('REDIS_CLIENT', 'phpredis'),

        'options' => [
            'cluster' => env('REDIS_CLUSTER', 'redis'),
            'prefix' => env('REDIS_PREFIX', Str::slug(env('APP_NAME', 'laravel'), '_').'_database_'),
        ],

        'default' => [
            'url' => env('REDIS_URL'),
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'username' => env('REDIS_USERNAME'),
            'password' => env('REDIS_PASSWORD'),
            'port' => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_DB', '0'),
        ],

        'cache' => [
            'url' => env('REDIS_URL'),
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'username' => env('REDIS_USERNAME'),
            'password' => env('REDIS_PASSWORD'),
            'port' => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_CACHE_DB', '1'),
        ],

    ],

];
