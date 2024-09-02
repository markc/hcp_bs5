<?php

declare(strict_types=1);

// index.php 20150101 - 20240902
// Copyright (C) 2015-2024 Mark Constable <markc@renta.net> (AGPL-3.0)

// Define constants for directory separator and the path to the included PHP files
const INC = __DIR__ . '/lib/php/';
const DBG = true;  // Enable or disable debugging

/**
 * Autoload function to automatically load class files when instantiated.
 *
 * @param string $className Class name to load.
 *
 * @throws \LogicException If the class file is not found.
 */
spl_autoload_register(static function (string $className): void {
    // Create path to the class file
    $filePath = INC . $className[0] . DS . substr($className, 1) . '.php';

    // Check if the class file exists
    if (is_file($filePath)) {
        // Load the class file
        require $filePath;
    } else {
        // Throw an exception if the class file does not exist
        throw new \LogicException("Class $className not found");
    }
});

// Create a new Init object with an anonymous class for configuration
echo new Init(new class()
{
    public object $t; // Placeholder for a theme object

    // Configuration settings
    public array $cfg = [
        'email' => 'markc@renta.net', // Default email address
        'file'  => __DIR__ . DS . 'lib' . DS . '.ht_conf.php', // Path to config override file
        'hash'  => 'SHA512-CRYPT',  // Default hashing algorithm
        'host'  => '',              // Hostname (to be defined later)
        'perp'  => 25,              // Pagination setting
        'self'  => '/',             // Base URL or path
    ];

    // Input parameters, typically from user or API requests
    public array $in = [
        'a'     => '',              // API credentials (apiusr:apikey)
        'd'     => '',              // Domain name
        'g'     => null,            // Group or category
        'i'     => null,            // Item ID
        'l'     => '',              // Log message
        'm'     => 'list',          // Method or action
        'o'     => 'home',          // Object or content type
        'r'     => 'local',         // Remote server identifier
        't'     => 'bootstrap',     // Theme (default: bootstrap)
        'x'     => '',              // XMLHttpRequest flag
    ];

    // Output settings for rendering content
    public array $out = [
        'doc'   => 'NetServa',      // Document title
        'css'   => '',              // Additional CSS styles
        'log'   => '',              // Log output
        'nav1'  => '',              // First-level navigation
        'nav2'  => '',              // Second-level navigation
        'nav3'  => '',              // Third-level navigation
        'head'  => 'NetServa HCP',  // HTML head content
        'main'  => 'Error: missing page!',  // Main content area
        'foot'  => 'Copyright (C) 2015-2024 Mark Constable (AGPL-3.0)',  // Footer content
        'js'    => '',              // Additional JavaScript
        'end'   => '',              // End of document content
    ];

    // Database configuration settings
    public array $db = [
        'host'  => '127.0.0.1',     // Database host
        'name'  => 'sysadm',        // Database name
        'pass'  => 'lib' . DS . '.ht_pw',  // Path to MySQL password override file
        'path'  => 'sqlite/sysadm/sysadm.db',  // SQLite database path
        'port'  => '3306',          // Database port
        'sock'  => '',              // MySQL socket (optional)
        'type'  => 'sqlite',        // Database type (mysql | sqlite)
        'user'  => 'sysadm',        // Database username
    ];

    // First-level navigation settings
    public array $nav1 = [
        // Non-authenticated user menu
        'non' => [
            ['Webmail',     'webmail/',     'bi bi-envelope-fill'],
            ['Phpmyadmin',  'phpmyadmin/',  'bi bi-globe'],
        ],
        // Regular user menu
        'usr' => [
            ['Webmail',     'webmail/',     'bi bi-envelope-fill'],
            ['Phpmyadmin',  'phpmyadmin/',  'bi bi-globe'],
        ],
        // Administrator menu
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

    // Second-level navigation settings
    public array $nav2 = [
        ['local',   '?r=local', 'bi bi-globe'],
        ['mgo',     '?r=mgo',   'bi bi-globe'],
        ['vmd1',    '?r=vmd1',  'bi bi-globe'],
    ];

    // DNS configuration settings
    public array $dns = [
        'a'     => '127.0.0.1',     // Default A record
        'mx'    => '',              // Default MX record
        'ns1'   => 'ns1.',          // Primary nameserver
        'ns2'   => 'ns2.',          // Secondary nameserver
        'prio'  => 0,               // Priority setting
        'ttl'   => 300,             // Time to live
        // Start of Authority record settings
        'soa'   => [
            'primary'   => 'ns1.',  // Primary nameserver
            'email'     => 'admin.', // Domain email address
            'refresh'   => 7200,    // Refresh interval
            'retry'     => 540,     // Retry interval
            'expire'    => 604800,  // Expiration time
            'ttl'       => 3600,    // TTL for SOA record
        ],
        // Alternate DNS database settings
        'db'    => [
            'host'      => '127.0.0.1', // Alternate DNS DB host
            'name'      => 'pdns',  // Alternate DNS DB name
            'pass'      => 'lib' . DS . '.ht_dns_pw', // Path to DNS password override file
            'path'      => 'sqlite/sysadm/pdns.db', // Path to DNS SQLite DB
            'port'      => '3306',  // Alternate DNS DB port
            'sock'      => '',      // MySQL socket (optional)
            'type'      => 'sqlite',// DB type (mysql | sqlite | '' to disable)
            'user'      => 'sysadm',// Alternate DNS DB user
        ],
    ];

    // Access control levels (ACL) defining user roles
    public array $acl = [
        0   => 'SuperAdmin',        // Full access
        1   => 'Administrator',     // Admin access
        2   => 'User',              // Regular user access
        3   => 'Suspended',         // Suspended access
        9   => 'Anonymous',         // No authentication required
    ];
});
