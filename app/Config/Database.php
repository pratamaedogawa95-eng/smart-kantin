<?php

namespace Config;

use CodeIgniter\Database\Config;

/**
 * Database Configuration
 */
class Database extends Config
{
    /**
     * The directory that holds the Migrations and Seeds directories.
     */
    public string $filesPath = APPPATH . 'Database' . DIRECTORY_SEPARATOR;

    /**
     * Lets you choose which connection group to use if no other is specified.
     */
    public string $defaultGroup = 'default';

    /**
     * The default database connection.
     *
     * @var array<string, mixed>
     */
    public array $default = [
        'DSN'          => '',
        'hostname'     => 'aws-0-ap-southeast-1.pooler.supabase.com',
        'username'     => 'postgres.meeeimycxytimlmoetaq',
        'password'     => 'hdpK8da2GKdy7btR',
        'database'     => 'postgres',
        'DBDriver'     => 'Postgre',
        'DBPrefix'     => '',
        'pConnect'     => false,
        'DBDebug'      => true,
        'charset'      => 'utf8',
        'DBCollat'     => '',
        'swapPre'      => '',
        'encrypt'      => false,
        'compress'     => false,
        'strictOn'     => false,
        'failover'     => [],
        'port'         => 5432,
        'schema'       => 'public',
        'numberNative' => false,
        'foundRows'    => false,
        'dateFormat'   => [
            'date'     => 'Y-m-d',
            'datetime' => 'Y-m-d H:i:s',
            'time'     => 'H:i:s',
        ],
    ];

    /**
     * This database connection is used when running PHPUnit database tests.
     *
     * @var array<string, mixed>
     */
    public array $tests = [
        'DSN'         => '',
        'hostname'    => '127.0.0.1',
        'username'    => '',
        'password'    => '',
        'database'    => ':memory:',
        'DBDriver'    => 'SQLite3',
        'DBPrefix'    => 'db_',
        'pConnect'    => false,
        'DBDebug'     => true,
        'charset'     => 'utf8',
        'DBCollat'    => '',
        'swapPre'     => '',
        'encrypt'     => false,
        'compress'    => false,
        'strictOn'    => true,
        'failover'    => [],
        'port'        => 3306,
        'foreignKeys' => true,
        'busyTimeout' => 1000,
        'synchronous' => null,
        'dateFormat'  => [
            'date'     => 'Y-m-d',
            'datetime' => 'Y-m-d H:i:s',
            'time'     => 'H:i:s',
        ],
    ];

    public function __construct()
    {
        parent::__construct();

        // 1. Dukungan DATABASE_URL / DATABASE_PRIVATE_URL dari Railway atau Supabase
        $databaseUrl = getenv('DATABASE_URL') ?: getenv('DATABASE_PRIVATE_URL');
        if ($databaseUrl) {
            $parsed = parse_url($databaseUrl);
            if ($parsed !== false) {
                if (isset($parsed['host'])) {
                    $this->default['hostname'] = $parsed['host'];
                }
                if (isset($parsed['port'])) {
                    $this->default['port'] = (int) $parsed['port'];
                }
                if (isset($parsed['user'])) {
                    $this->default['username'] = urldecode($parsed['user']);
                }
                if (isset($parsed['pass'])) {
                    $this->default['password'] = urldecode($parsed['pass']);
                }
                if (isset($parsed['path'])) {
                    $this->default['database'] = ltrim($parsed['path'], '/');
                }
                $this->default['DBDriver'] = 'Postgre';
            }
        }

        // 2. Dukungan Environment Variables individual (database.default.* / DB_* / PG*)
        $host = getenv('database.default.hostname') ?: (getenv('DB_HOST') ?: getenv('PGHOST'));
        if ($host) {
            $this->default['hostname'] = $host;
        }

        $user = getenv('database.default.username') ?: (getenv('DB_USER') ?: (getenv('DB_USERNAME') ?: getenv('PGUSER')));
        if ($user) {
            $this->default['username'] = $user;
        }

        $pass = getenv('database.default.password');
        if ($pass === false || $pass === null) {
            $pass = getenv('DB_PASSWORD') ?: (getenv('DB_PASS') ?: getenv('PGPASSWORD'));
        }
        if ($pass !== false && $pass !== null && $pass !== '') {
            $this->default['password'] = $pass;
        }

        $dbName = getenv('database.default.database') ?: (getenv('DB_NAME') ?: (getenv('DB_DATABASE') ?: getenv('PGDATABASE')));
        if ($dbName) {
            $this->default['database'] = $dbName;
        }

        $driver = getenv('database.default.DBDriver') ?: getenv('DB_DRIVER');
        if ($driver) {
            $this->default['DBDriver'] = $driver;
        }

        $port = getenv('database.default.port') ?: (getenv('DB_PORT') ?: getenv('PGPORT'));
        if ($port) {
            $this->default['port'] = (int) $port;
        }

        // Pastikan driver Postgre memiliki schema public dan charset utf8
        if ($this->default['DBDriver'] === 'Postgre') {
            $this->default['schema'] = 'public';
            $this->default['charset'] = 'utf8';
        }

        if (ENVIRONMENT === 'testing') {
            $this->defaultGroup = 'tests';
        }
    }
}
