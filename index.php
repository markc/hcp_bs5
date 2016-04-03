<?php
// index.php 20151015 (C) 2015 Mark Constable <markc@renta.net> (AGPL-3.0)

declare(strict_types = 1);

const DS    = DIRECTORY_SEPARATOR;
const SYS   = __DIR__;
const INC   = SYS.DS.'lib'.DS.'php'.DS;

spl_autoload_register(function ($c) {
    $f = INC.str_replace(['\\', '_'], [DS, DS], strtolower($c)).'.php';
    if (file_exists($f)) include $f;
});

echo new Controller(new class
{
    public
    $dbh = null,
    $cfg = [
        'file'      => 'lib'.DS.'.ht_conf.php', // override settings file
        'email'     => 'markc@renta.net',       // site admin email
    ],
    $in = [
        'a'         => '',                      // API [html(default)|json]
        'g'         => 0,                       // Group (category)
        'i'         => 0,                       // Item or ID
        'l'         => '',                      // Logging [lvl:msg]
        'm'         => 'read',                  // Method action
        'n'         => 1,                       // Navigation
        'o'         => 'home',                  // Object module
        't'         => 'bootstrap',             // current Theme
    ],
    $out = [
        'top'       => '',
        'meta'      => '',
        'doc'       => 'SysAdm',
        'css'       => '',
        'log'       => '',
        'nav1'      => '',
        'nav2'      => '',
        'nav3'      => '',
        'head'      => 'SysAdm',
        'main'      => 'Missing home page',
        'foot'      => 'Copyright (C) 2015-2016 Mark Constable (AGPL-3.0)',
        'end'       => '',
    ],
    $db = [
        'host'      => '127.0.0.1',
        'name'      => 'sysadm',
        'pass'      => 'lib' . DS . '.ht_pw.php',
        'path'      => 'lib' . DS . '.ht_db.sqlite',
        'port'      => '3306',
        'sock'      => '', // '/run/mysqld/mysqld.sock',
        'type'      => 'sqlite', // mysql|sqlite
        'user'      => 'sysadm',
    ],
    $nav1 = [
        'non' => [
            ['NetServa', '?o=home', 'fa fa-home fa-fw'],
            ['About', '?o=about', 'fa fa-question-circle fa-fw'],
            ['Contact', '?o=contact', 'fa fa-envelope-o fa-fw'],
            ['News', '?o=w_news', 'fa fa-newspaper-o fa-fw'],
            ['Sign in', '?o=auth&m=signin', 'fa fa-sign-in fa-fw'],
        ],
        'usr' => [
            ['NetServa', '?o=home', 'fa fa-home fa-fw'],
            ['About', '?o=about', 'fa fa-question-circle fa-fw'],
            ['Contact', '?o=contact', 'fa fa-envelope-o fa-fw'],
            ['News', '?o=w_news', 'fa fa-newspaper-o fa-fw'],
            ['Sign out', '?o=auth&m=signout', 'fa fa-sign-in fa-fw'],
        ],
        'adm' => [
            ['NetServa', '?o=home', 'fa fa-home fa-fw'],
            ['News', '?o=w_news', 'fa fa-newspaper-o fa-fw'],
            ['Admin', [
                ['Mail Users', '?o=m_users', ''],
                ['Mail Forwards', '?o=m_forwards', ''],
                ['Mail Limits', '?o=m_limits', ''],
                ['Mail Vacations', '?o=m_vacations', ''],
                ['Mail Welcomes', '?o=m_welcomes', ''],
                ['SysUsers', '?o=s_users', ''],
                ['SysUserGroups', '?o=s_usergroups', ''],
                ['SysUserGrouplist', '?o=s_usergrouplist', ''],
                ['Web News', '?o=w_news', ''],
                ['Web Users', '?o=w_users', ''],
            ], 'fa fa-user-secret fa-fw'],
            ['Sign out', '?o=auth&m=signout', 'fa fa-sign-out fa-fw'],
        ],
    ],
    $nav2 = [
        ['Theme', [
            ['None', '?t=none', ''],
            ['Simple', '?t=simple', ''],
            ['Bootstrap', '?t=bootstrap', ''],
            ['Material', '?t=material', ''],
        ], 'fa fa-list-alt fa-fw'],
    ],
    $acl = [
        0 => 'Anonymous',
        1 => 'Administrator',
        2 => 'User',
        3 => 'Suspended',
    ];
});

function dbg($var = null)
{
    if (is_object($var))
        error_log(ReflectionObject::export($var, true));
    ob_start();
    print_r($var);
    $ob = ob_get_contents();
    ob_end_clean();
    error_log($ob);
}
