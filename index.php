<?php

declare(strict_types=1);

// index.php 20150101 - 20240901
// Copyright (C) 2015-2024 Mark Constable <markc@renta.net> (AGPL-3.0)

const DS = DIRECTORY_SEPARATOR;
const INC = __DIR__ . DS . 'lib' . DS . 'php' . DS;
const DBG = true;

spl_autoload_register(static function (string $class): void {
    $file = INC . str_replace(['\\', '_'], [DS, DS], strtolower($class)) . '.php';
    if (is_file($file)) {
        require $file;
        if (DBG) {
            error_log("Loaded: $file");
        }
    } else {
        error_log("Error: {$file} does not exist");
    }
});

echo new Init(new class()
{
    public object $t; // theme object

    public array $cfg = [
        'email' => 'markc@renta.net',
        'file'  => __DIR__ . DS . 'lib' . DS . '.ht_conf.php', // this config override
        'hash'  => 'SHA512-CRYPT',
        'host'  => '',
        'perp'  => 25,
        'self'  => '/',
    ];

    public array $in = [
        'a'     => '',           // API (apiusr:apikey)
        'd'     => '',           // Domain (current)
        'g'     => null,         // Group/Category
        'i'     => null,         // Item or ID
        'l'     => '',           // Log (message)
        'm'     => 'list',       // Method (action)
        'o'     => 'home',       // Object (content)
        'r'     => 'local',      // Remote Server (local)
        't'     => 'bootstrap',  // Theme (bootstrap(5))
        'x'     => '',           // XHR (request)
    ];

    public array $out = [
        'doc'   => 'NetServa',
        'css'   => '',
        'log'   => '',
        'nav1'  => '',
        'nav2'  => '',
        'nav3'  => '',
        'head'  => 'NetServa HCP',
        'main'  => 'Error: missing page!',
        'foot'  => 'Copyright (C) 2015-2024 Mark Constable (AGPL-3.0)',
        'js'    => '',
        'end'   => '',
    ];

    public array $db = [
        'host'  => '127.0.0.1', // DB site
        'name'  => 'sysadm',    // DB name
        'pass'  => 'lib' . DS . '.ht_pw', // MySQL password override
        'path'  => 'sqlite/sysadm/sysadm.db', // SQLite DB
        'port'  => '3306',      // DB port
        'sock'  => '',          // '/run/mysqld/mysqld.sock',
        'type'  => 'sqlite',     // mysql | sqlite
        'user'  => 'sysadm',    // DB user
    ];

    public array $nav1 = [
        'non' => [
            ['Webmail',     'webmail/',     'bi bi-envelope-fill'],
            ['Phpmyadmin',  'phpmyadmin/',  'bi bi-globe'],
        ],
        'usr' => [
            ['Webmail',     'webmail/',     'bi bi-envelope-fill'],
            ['Phpmyadmin',  'phpmyadmin/',  'bi bi-globe'],
        ],
        'adm' => [
            ['Manage', [
                ['Accounts',    '?o=accounts',  'bi bi-people-fill'],
                ['SSH Manager', '?o=sshm',      'bi bi-key'],
                ['Vhosts',      '?o=vhosts',    'bi bi-globe2'],
                ['Mailboxes',   '?o=vmails',    'bi bi-envelope-fill'],
                ['Aliases',     '?o=valias',    'bi bi-envelope-paper-fill'],
                ['DKIM',        '?o=dkim',      'bi bi-person-vcard-fill'],
                ['Domains',     '?o=domains',   'bi bi-globe-americas'],
            ], 'bi bi-stack'],
            ['Stats', [
                ['Sys Info',    '?o=infosys',   'bi bi-speedometer2'],
                ['Processes',   '?o=processes', 'bi bi-bezier2'],
                ['Mail Info',   '?o=infomail',  'bi bi-envelope-open'],
                ['Mail Graph',  '?o=mailgraph', 'bi bi-bar-chart'],
            ], 'bi bi-graph-up-arrow'],
            ['Links', [
                ['Webmail',     'webmail/',     'bi bi-envelope-fill'],
                ['Phpmyadmin',  'phpmyadmin/',  'bi bi-globe'],
            ], 'bi bi-list'],
            ['Sites', [
                ['local',       '?r=local',     'bi bi-globe', 'r'],
                ['mgo',         '?r=mgo',       'bi bi-globe', 'r'],
                ['vmd1',        '?r=vmd1',      'bi bi-globe', 'r'],
            ], 'bi bi-globe'],
        ],
    ];

    public array $nav2 = [
        ['local',   '?r=local', 'bi bi-globe'],
        ['mgo',     '?r=mgo',   'bi bi-globe'],
        ['vmd1',    '?r=vmd1',  'bi bi-globe'],
    ];

    public array $dns = [
        'a'     => '127.0.0.1',
        'mx'    => '',
        'ns1'   => 'ns1.',
        'ns2'   => 'ns2.',
        'prio'  => 0,
        'ttl'   => 300,
        'soa'   => [
            'primary'   => 'ns1.',
            'email'     => 'admin.',
            'refresh'   => 7200,
            'retry'     => 540,
            'expire'    => 604800,
            'ttl'       => 3600,
        ],
        'db'    => [
            'host'      => '127.0.0.1',  // Alt DNS DB site
            'name'      => 'pdns',       // Alt DNS DB name
            'pass'      => 'lib' . DS . '.ht_dns_pw', // MySQL DNS password override
            'path'      => 'sqlite/sysadm/pdns.db', // DNS SQLite DB
            'port'      => '3306',       // Alt DNS DB port
            'sock'      => '',           // '/run/mysqld/mysqld.sock',
            'type'      => 'sqlite',     // mysql | sqlite | '' to disable
            'user'      => 'sysadm',     // Alt DNS DB user
        ],
    ];

    public array $acl = [
        0   => 'SuperAdmin',
        1   => 'Administrator',
        2   => 'User',
        3   => 'Suspended',
        9   => 'Anonymous',
    ];
});
