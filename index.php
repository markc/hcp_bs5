<?php

declare(strict_types=1);
// index.php 20150101 - 20230619
// Copyright (C) 2015-2023 Mark Constable <markc@renta.net> (AGPL-3.0)

const DS = DIRECTORY_SEPARATOR;
const INC = __DIR__ . DS . 'lib' . DS . 'php' . DS;
const DBG = true;

spl_autoload_register(function ($c): void {
    $f = INC . str_replace(['\\', '_'], [DS, DS], strtolower($c)) . '.php';
    if (file_exists($f)) {
        include $f;
        if (DBG) {
            error_log("include $f");
        }
    } else {
        error_log("!!! {$f} does not exist");
    }
});

function elog(string $content): void
{
    if (DBG) {
        error_log($content);
    }
}

class gbl
{
    private static ?gbl $instance = null;

    private function __construct()
    {
    }

    public static function getInstance(): gbl
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public Object $p; // plugin object
    public Object $t; // theme object

    public $cfg = [
        'email' => 'markc@renta.net',
        'file' => __DIR__ . DS . 'lib' . DS . '.ht_conf.php', // settings override
        'hash' => 'SHA512-CRYPT',
        'host' => '',
        'perp' => 25,
        'self' => '/hcp/',
    ];

    public $in = [
        'a' => '',           // API (apiusr:apikey)
        'd' => '',           // Domain (current)
        'g' => null,         // Group/Category
        'i' => null,         // Item or ID
        'l' => '',           // Log (message)
        'm' => 'list',       // Method (action)
        'o' => 'home',       // Object (content)
        't' => 'bootstrap5', // Theme (bootstrap, bootstrap5, mazer)
        'x' => '',           // XHR (request)
    ];

    public $out = [
        'doc' => 'NetServa',
        'css' => '',
        'log' => '',
        'nav1' => '',
        'nav2' => '',
        'nav3' => '',
        'head' => 'NetServa',
        'main' => 'Error: missing page!',
        'foot' => 'Copyright (C) 2015-2023 Mark Constable (AGPL-3.0)',
        'js' => '',
        'end' => '',
    ];

    public $db = [
        'host' => '127.0.0.1', // DB site
        'name' => 'sysadm',    // DB name
        'pass' => 'lib' . DS . '.ht_pw', // MySQL password override
        'path' => '/var/lib/sqlite/sysadm/sysadm.db', // SQLite DB
        'port' => '3306',      // DB port
        'sock' => '',          // '/run/mysqld/mysqld.sock',
        'type' => 'mysql',     // mysql | sqlite
        'user' => 'sysadm',    // DB user
    ];

    public $nav1 = [
        'non' => [
            ['Webmail',     'webmail/',         'bi bi-envelope-fill'],
            ['Phpmyadmin',  'phpmyadmin/',      'bi bi-globe'],
        ],
        'usr' => [
            ['Webmail',     'webmail/',         'bi bi-envelope-fill'],
            ['Phpmyadmin',  'phpmyadmin/',      'bi bi-globe'],
        ],
        'adm' => [
            ['Manage',       [
                ['Accounts',    '?o=accounts',  'bi bi-people-fill'],
                ['Vhosts',      '?o=vhosts',    'bi bi-globe2'],
                ['Mailboxes',   '?o=vmails',    'bi bi-envelope-fill'],
                ['Aliases',     '?o=valias',    'bi bi-envelope-paper-fill'],
                ['DKIM',        '?o=dkim',      'bi bi-person-vcard-fill'],
                ['Domains',     '?o=domains',   'bi bi-globe-americas'],
            ], 'bi bi-stack'],
            ['Stats',       [
                ['Sys Info',    '?o=infosys',   'bi bi-speedometer2'],
                ['Processes',   '?o=processes', 'bi bi-bezier2'],
                ['Mail Info',   '?o=infomail',  'bi bi-envelope-open'],
                ['Mail Graph',  '?o=mailgraph', 'bi bi-bar-chart'],
            ], 'bi bi-graph-up-arrow'],
            ['Links',        [
                ['Webmail',     'webmail/',     'bi bi-envelope-fill'],
                ['Phpmyadmin',  'phpmyadmin/',  'bi bi-globe'],
            ], 'bi bi-list'],
        ],
    ];

    public $nav2 = [];

    public $dns = [
        'a' => '127.0.0.1',
        'mx' => '',
        'ns1' => 'ns1.',
        'ns2' => 'ns2.',
        'prio' => 0,
        'ttl' => 300,
        'soa' => [
            'primary' => 'ns1.',
            'email' => 'admin.',
            'refresh' => 7200,
            'retry' => 540,
            'expire' => 604800,
            'ttl' => 3600,
        ],
        'db' => [
            'host' => '127.0.0.1', // Alt DNS DB site
            'name' => 'pdns',      // Alt DNS DB name
            'pass' => 'lib' . DS . '.ht_dns_pw', // MySQL DNS password override
            'path' => '/var/lib/sqlite/sysadm/pdns.db', // DNS SQLite DB
            'port' => '3306',      // Alt DNS DB port
            'sock' => '',          // '/run/mysqld/mysqld.sock',
            'type' => '',          // mysql | sqlite | '' to disable
            'user' => 'pdns',      // Alt DNS DB user
        ],
    ];

    public $acl = [
        0 => 'SuperAdmin',
        1 => 'Administrator',
        2 => 'User',
        3 => 'Suspended',
        9 => 'Anonymous',
    ];
};

echo new Init();
