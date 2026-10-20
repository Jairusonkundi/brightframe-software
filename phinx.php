<?php
/**
 * Phinx configuration.
 *
 * Reads the same project-root .env used by the website, so there is one
 * source of truth for database credentials. Run from the project root:
 *
 *     composer migrate          # php vendor/bin/phinx migrate
 *     composer migrate:status
 *     composer seed             # optional sample/demo content
 *
 * The database itself must already exist (Phinx creates tables, not the
 * database). `phinx migrate` is safe to run against an existing install:
 * migrations skip tables that are already present.
 */

require_once __DIR__ . '/config/functions.php';

$dbHost    = env('DB_HOST', 'localhost');
$dbPort    = (int) env('DB_PORT', '3306');
$dbName    = env('DB_NAME', 'brightframe_db');
$dbUser    = env('DB_USER', 'root');
$dbPass    = env('DB_PASS', '');
$dbCharset = 'utf8mb4';

$baseEnvironment = [
    'adapter'    => 'mysql',
    'host'       => $dbHost,
    'port'       => $dbPort,
    'name'       => $dbName,
    'user'       => $dbUser,
    'pass'       => $dbPass,
    'charset'    => $dbCharset,
    'collation'  => 'utf8mb4_unicode_ci',
];

return [
    'paths' => [
        'migrations' => __DIR__ . '/db/migrations',
        'seeds'      => __DIR__ . '/db/seeds',
    ],
    'environments' => [
        'default_migration_table' => 'phinxlog',
        'default_environment'     => env('APP_ENV', 'development'),

        'development' => $baseEnvironment,

        // Separate schema name so a test run can never touch live data.
        'testing' => array_merge($baseEnvironment, [
            'name' => env('DB_NAME_TEST', $dbName . '_test'),
        ]),

        'production' => $baseEnvironment,
    ],
    'version_order' => 'creation',
];
