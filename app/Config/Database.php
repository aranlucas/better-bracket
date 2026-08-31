<?php

declare(strict_types=1);

namespace Config;

use CodeIgniter\Database\Config;

class Database extends Config
{
    public string $filesPath = APPPATH . 'Database' . DIRECTORY_SEPARATOR;
    public string $defaultGroup = 'default';

    /** @var array<string, mixed> */
    public array $default = [
        'DSN'        => '',
        'hostname'   => 'localhost',
        'username'   => 'better_bracket',
        'password'   => '',
        'database'   => 'better_bracket',
        'schema'     => 'public',
        'DBDriver'   => 'Postgre',
        'DBPrefix'   => '',
        'pConnect'   => false,
        'DBDebug'    => true,
        'charset'    => 'utf8',
        'DBCollat'   => '',
        'swapPre'    => '',
        'encrypt'    => false,
        'compress'   => false,
        'strictOn'   => true,
        'failover'   => [],
        'port'       => 5432,
        'dateFormat' => [
            'date'     => 'Y-m-d',
            'datetime' => 'Y-m-d H:i:s',
            'time'     => 'H:i:s',
        ],
    ];

    /** @var array<string, mixed> */
    public array $tests = [
        'DSN'         => '',
        'hostname'    => '127.0.0.1',
        'username'    => '',
        'password'    => '',
        'database'    => ':memory:',
        'DBDriver'    => 'SQLite3',
        'DBPrefix'    => 'test_',
        'pConnect'    => false,
        'DBDebug'     => true,
        'charset'     => 'utf8',
        'DBCollat'    => '',
        'swapPre'     => '',
        'encrypt'     => false,
        'compress'    => false,
        'strictOn'    => true,
        'failover'    => [],
        'port'        => 5432,
        'foreignKeys' => true,
    ];

    public function __construct()
    {
        parent::__construct();

        $this->default = array_replace($this->default, [
            'hostname' => env('database.default.hostname', $this->default['hostname']),
            'username' => env('database.default.username', $this->default['username']),
            'password' => env('database.default.password', $this->default['password']),
            'database' => env('database.default.database', $this->default['database']),
            'DBDriver' => env('database.default.DBDriver', $this->default['DBDriver']),
            'port'     => (int) env('database.default.port', $this->default['port']),
            'DBDebug'  => ENVIRONMENT !== 'production',
        ]);

        if (ENVIRONMENT === 'testing') {
            $this->defaultGroup = 'tests';
        }
    }
}
