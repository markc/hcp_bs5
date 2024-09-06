<?php

declare(strict_types=1);

// index.php 20150101 - 20240906
// Copyright (C) 2015-2024 Mark Constable <markc@renta.net> (AGPL-3.0)

const DS = DIRECTORY_SEPARATOR;
const INC = __DIR__ . DS . 'lib' . DS . 'php' . DS;
const DBG = true;

spl_autoload_register(static function (string $className): void {
    $filePath = INC . str_replace(['\\', '_'], [DS, DS], strtolower($className)) . '.php';
error_log("filePath=$filePath");
    if (is_file($filePath)) {
        require $filePath;
    } else {
        throw new \LogicException("Class $className not found");
    }
});

enum AclLevel: int {
    case SuperAdmin = 0;
    case Administrator = 1;
    case User       = 2;
    case Suspended  = 3;
    case Anonymous  = 9;
}

echo new Init(new class
{
    public array
    $cfg = [
        'email' => '',
        'admpw' => '',
        'file'  => __DIR__ . DS . 'lib' . DS . '.ht_conf.php', // settings override
        'hash'  => 'SHA512-CRYPT',
        'host'  => '',
        'perp'  => 25,
        'self'  => '/hcp/',
    ],
    $in = [
        'a'     => '',          // API (apiusr:apikey)
        'd'     => '',          // Domain (current)
        'g'     => null,        // Group/Category
        'i'     => null,        // Item or ID
        'l'     => '',          // Log (message)
        'm'     => 'list',      // Method (action)
        'o'     => 'home',      // Object (content)
        't'     => 'bootstrap5', // Theme
        'x'     => '',          // XHR (request)
    ],
    $out = [
        'doc'   => 'NetServa',
        'css'   => '',
        'log'   => '',
        'nav1'  => '',
        'nav2'  => '',
        'nav3'  => '',
        'head'  => 'NetServa',
        'main'  => 'Error: missing page!',
        'foot'  => 'Copyright (C) 2015-2019 Mark Constable (AGPL-3.0)',
        'js'   => '',
        'end'   => '',
    ],
    $db = [
        'host'  => '127.0.0.1', // DB site
        'name'  => 'sysadm',    // DB name
        'pass'  => 'lib' . DS . '.ht_pw', // MySQL password override
        'path'  => '/var/lib/sqlite/sysadm/sysadm.db', // SQLite DB
        'port'  => '3306',      // DB port
        'sock'  => '',          // '/run/mysqld/mysqld.sock',
        'type'  => 'mysql',     // mysql | sqlite
        'user'  => 'sysadm',    // DB user
    ],
    $nav1 = [
        'non' => [
            ['Webmail',     'webmail/',         'bi bi-envelope-fill'],
            ['Phpmyadmin',  'phpmyadmin/',      'bi bi-globe'],
        ],
        'usr' => [
            ['Webmail',     'webmail/',         'bi bi-envelope-fill'],
            ['Phpmyadmin',  'phpmyadmin/',      'bi bi-globe'],
        ],
        'adm' => [
            ['Menu',        [
                ['Webmail',     'webmail/',     'bi bi-envelope-fill'],
                ['Phpmyadmin',  'phpmyadmin/',  'bi bi-globe'],
            ], ''],
            ['Admin',       [
                ['Accounts',    '?o=accounts',  'bi bi-people-fill'],
                ['Vhosts',      '?o=vhosts',    'bi bi-globe'],
                ['Mailboxes',   '?o=vmails',    'bi bi-envelope-fill'],
                ['Aliases',     '?o=valias',    'bi bi-envelope'],
                ['Domains',     '?o=domains',   'bi bi-server'],
                ['DKIM',        '?o=dkim',      'bi bi-bar-chart'],
                ['SSHM',        '?o=sshm',      'bi bi-person-badge'],
            ], ''],
            ['Stats',       [
                ['Sys Info',    '?o=infosys',   'bi bi-speedometer'],
                ['Processes',   '?o=processes', 'bi bi-diagram-3'],
                ['Mail Info',   '?o=infomail',  'bi bi-envelope-open'],
            ], ''],
        ],
    ],
    $nav2 = [
    ],
    $dns = [
        'a'     => '127.0.0.1',
        'mx'    => '',
        'ns1'   => 'ns1.',
        'ns2'   => 'ns2.',
        'prio'  => 0,
        'ttl'   => 300,
        'soa'   => [
            'primary' => 'ns1.',
            'email'   => 'admin.',
            'refresh' => 7200,
            'retry'   => 540,
            'expire'  => 604800,
            'ttl'     => 3600,
        ],
        'db' => [
            'host'  => '127.0.0.1', // Alt DNS DB site
            'name'  => 'pdns',      // Alt DNS DB name
            'pass'  => 'lib' . DS . '.ht_dns_pw', // MySQL DNS password override
            'path'  => '/var/lib/sqlite/sysadm/pdns.db', // DNS SQLite DB
            'port'  => '3306',      // Alt DNS DB port
            'sock'  => '',          // '/run/mysqld/mysqld.sock',
            'type'  => '',          // mysql | sqlite | '' to disable
            'user'  => 'pdns',      // Alt DNS DB user
        ],
    ],
    $acl = [
        AclLevel::SuperAdmin->value    => AclLevel::SuperAdmin->name,
        AclLevel::Administrator->value => AclLevel::Administrator->name,
        AclLevel::User->value          => AclLevel::User->name,
        AclLevel::Suspended->value     => AclLevel::Suspended->name,
        AclLevel::Anonymous->value     => AclLevel::Anonymous->name,
    ];
});

/*
echo new Init(new class() {
    public object $t;

    public function __construct(
        public array $cfg = [
            'email' => '',
            'admpw' => '',
            'file'  => __DIR__ . DS . 'lib' . DS . '.ht_conf.php',
            'hash'  => 'SHA512-CRYPT',
            'host'  => '',
            'perp'  => 25,
            'self'  => '/',
        ],
        public array $in = [
            'a'     => '',
            'd'     => '',
            'g'     => null,
            'i'     => null,
            'l'     => '',
            'm'     => 'list',
            'o'     => 'home',
            'r'     => 'local',
            't'     => 'bootstrap',
            'x'     => '',
        ],
        public array $out = [
            'doc'   => 'NetServa',
            'css'   => '',
            'log'   => '',
            'nav1'  => '',
            'nav2'  => '',
            'nav3'  => '',
            'head'  => 'NetServa HCP',
            'main'  => 'Error: missing page!',
            'foot'  => 'Copyright (C) 2015-2024 Netserva HCP (AGPL-3.0)',
            'js'    => '',
            'end'   => '',
        ],
        public array $db = [
            'host'  => '127.0.0.1',
            'name'  => 'sysadm',
            'pass'  => 'lib' . DS . '.ht_pw',
            'path'  => 'sqlite/sysadm/sysadm.db',
            'port'  => '3306',
            'sock'  => '',
            'type'  => 'sqlite',
            'user'  => 'sysadm',
        ],
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
        ],
        public array $nav2 = [
            ['local',   '?r=local', 'bi bi-globe'],
            ['mgo',     '?r=mgo',   'bi bi-globe'],
            ['vmd1',    '?r=vmd1',  'bi bi-globe'],
        ],
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
                'host'      => '127.0.0.1',
                'name'      => 'pdns',
                'pass'      => 'lib' . DS . '.ht_dns_pw',
                'path'      => 'sqlite/sysadm/pdns.db',
                'port'      => '3306',
                'sock'      => '',
                'type'      => 'sqlite',
                'user'      => 'sysadm',
            ],
        ],
        public array $acl = [
            AclLevel::SuperAdmin->value    => AclLevel::SuperAdmin->name,
            AclLevel::Administrator->value => AclLevel::Administrator->name,
            AclLevel::User->value          => AclLevel::User->name,
            AclLevel::Suspended->value     => AclLevel::Suspended->name,
            AclLevel::Anonymous->value     => AclLevel::Anonymous->name,
        ]
    ) {}
});
*/
