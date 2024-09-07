<?php

declare(strict_types=1);

const DS = DIRECTORY_SEPARATOR;
const LIB = __DIR__ . DS . 'lib' . DS;
const INC = LIB . 'php' . DS;
const DBG = true;

spl_autoload_register(function (string $className): void {
    $filePath = INC . str_replace(['\\', '_'], [DS, DS], strtolower($className)) . '.php';
    if (DBG) { error_log("filePath=$filePath"); }
    if (!is_file($filePath)) {
        throw new \LogicException("Class $className not found");
    }
    require $filePath;
});

enum AclLevel: int
{
    case SuperAdmin = 0;
    case Admin      = 1;
    case User       = 2;
    case Suspended  = 3;
    case Anonymous  = 9;
}

class Config
{
    public function __construct(
        public array $cfg   = [],
        public array $in    = [],
        public array $out   = [],
        public array $db    = [],
        public array $nav1  = [],
        public array $nav2  = [],
        public array $dns   = [],
        public array $acl   = []
    ) {
        $defaults = [
            'cfg' => [
                'email' => '',
                'admpw' => '',
                'file'  => LIB . '.ht_conf.php',
                'hash'  => 'SHA512-CRYPT',
                'host'  => '',
                'perp'  => 25,
                'self'  => '/',
            ],
            'in' => [
                'a'     => '',
                'd'     => '',
                'g'     => null,
                'i'     => null,
                'l'     => '',
                'm'     => 'list',
                'o'     => 'home',
                'r'     => 'local',
                't'     => 'bootstrap5',
                'x'     => '',
            ],
            'out' => [
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
            'db' => [
                'host'  => '127.0.0.1',
                'name'  => 'sysadm',
                'pass'  => LIB . DS . '.ht_pw',
                'path'  => '/var/lib/sqlite/sysadm/sysadm.db',
                'port'  => '3306',
                'sock'  => '',
                'type'  => 'mysql',
                'user'  => 'sysadm',
            ],
            'nav1' => [
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
            'nav2' => [
                ['local',   '?r=local', 'bi bi-globe'],
                ['mgo',     '?r=mgo',   'bi bi-globe'],
                ['vmd1',    '?r=vmd1',  'bi bi-globe'],
            ],
            'dns' => [
                'a'     => '127.0.0.1',
                'mx'    => '',
                'ns1'   => 'ns1.',
                'ns2'   => 'ns2.',
                'prio'  => 0,
                'ttl'   => 300,
                'soa'   => [
                    'primary'           => 'ns1.',
                    'email'             => 'admin.',
                    'refresh'           => 7200,
                    'retry'             => 540,
                    'expire'            => 604800,
                    'ttl'               => 3600,
                ],
                'db'    => [
                    'host'              => '127.0.0.1',
                    'name'              => 'pdns',
                    'pass'              => 'lib' . DS . '.ht_dns_pw',
                    'path'              => '/var/lib/sqlite/sysadm/pdns.db',
                    'port'              => '3306',
                    'sock'              => '',
                    'type'              => 'sqlite',
                    'user'              => 'sysadm',
                ],
            ],
            'acl' => [
                AclLevel::SuperAdmin->value => AclLevel::SuperAdmin->name,
                AclLevel::Admin->value      => AclLevel::Admin->name,
                AclLevel::User->value       => AclLevel::User->name,
                AclLevel::Suspended->value  => AclLevel::Suspended->name,
                AclLevel::Anonymous->value  => AclLevel::Anonymous->name,
            ],
        ];

        $this->cfg  = array_merge($defaults['cfg'], $cfg);
        $this->in   = array_merge($defaults['in'], $in);
        $this->out  = array_merge($defaults['out'], $out);
        $this->db   = array_merge($defaults['db'], $db);
        $this->nav1 = array_merge($defaults['nav1'], $nav1);
        $this->nav2 = array_merge($defaults['nav2'], $nav2);
        $this->dns  = array_merge($defaults['dns'], $dns);
        $this->acl  = array_merge($defaults['acl'], $acl);
    }
}

$config = new Config();
echo new Init($config);
