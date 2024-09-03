<?php

declare(strict_types=1);

// index.php 20150101 - 20240902
// Copyright (C) 2015-2024 Mark Constable <markc@renta.net> (AGPL-3.0)

// Define directory separator for cross-platform compatibility
const DS = DIRECTORY_SEPARATOR;

// Define the path to the included PHP files
const INC = __DIR__ . DS . 'lib' . DS . 'php' . DS;

// Enable or disable debugging via elog() function
const DBG = true;

/**
 * Autoload function to automatically load class files when instantiated.
 * This function is registered with spl_autoload_register() to handle class loading.
 *
 * @param string $className The name of the class to be loaded
 * @throws \LogicException If the class file is not found
 */
spl_autoload_register(static function (string $className): void {
    // Create the file path based on the class name
    // This assumes a specific directory structure and naming convention for class files
    $filePath = INC . str_replace(['\\', '_'], [DS, DS], strtolower($className)) . '.php';

    // Check if the class file exists
    if (is_file($filePath)) {
        // If the file exists, include it
        require $filePath;
    } else {
        // If the file doesn't exist, throw an exception
        throw new \LogicException("Class $className not found");
    }
});

/**
 * Enum for Access Control Levels (ACL)
 * This defines the different user roles and their corresponding integer values
 */
enum AclLevel: int {
    case SuperAdmin = 0;    // Highest level of access
    case Administrator = 1; // Admin level access
    case User = 2;          // Regular user access
    case Suspended = 3;     // Suspended user (limited access)
    case Anonymous = 9;     // Unauthenticated user
}

// Create a new Init object with an anonymous class for configuration
// This anonymous class is used to override any default values in the $cfg array
// The Init class is defined elsewhere and is used to set up the initial environment
// and configuration for the HCP web interface
// The constructor for Init expects an object with an array of configuration
// settings, which is what the anonymous class provides
// The constructor for Init will set up the initial environment and
// configuration for the HCP web interface based on the values provided in
// the $cfg array
// @see Init::__construct()
echo new Init(new class() {
    /**
     * Placeholder for a theme object, to be initialized later
     * @var object
     */
    public readonly object $t;

    /**
     * Constructor for the configuration class
     * All properties are defined as readonly to prevent modification after initialization
     */
    public function __construct(
        /**
         * Configuration settings
         *
         * The $cfg array contains the default configuration settings that can be
         * overridden by the contents of the file specified in $cfg['file'].
         *
         * @var array
         */
        public readonly array $cfg = [
            'email' => '',              // Default email address
            'admpw' => '',              // Default email address
            'file'  => __DIR__ . DS . 'lib' . DS . '.ht_conf.php',  // Path to config override file
            'hash'  => 'SHA512-CRYPT',  // Default hashing algorithm
            'host'  => '',              // Hostname (to be defined later)
            'perp'  => 25,              // Pagination setting
            'self'  => '/',             // Base URL or path
        ],
        /**
         * Input parameters, typically from user or API requests
         *
         * This is a placeholder for data that is passed into the HCP web interface
         * from the user or an API request.
         *
         * The keys in this array are the names of parameters that are passed in via
         * the URL or as HTTP request data. The values in this array are the default
         * values for each parameter.
         *
         * @var array
         */
        public readonly array $in = [
            'a' => '',                  // API credentials (apiusr:apikey)
            'd' => '',                  // Domain name
            'g' => null,                // Group or category
            'i' => null,                // Item ID
            'l' => '',                  // Log message
            'm' => 'list',              // Method or action
            'o' => 'home',              // Object or content type
            'r' => 'local',             // Remote server identifier
            't' => 'bootstrap',         // Theme
            'x' => '',                  // XMLHttpRequest flag
        ],
        /**
         * Output settings for rendering content
         * @var array
         */
        public readonly array $out = [
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
        ],
        /**
         * Database configuration settings
         * @var array
         */
        public readonly array $db = [
            'host'  => '127.0.0.1',     // Database host
            'name'  => 'sysadm',        // Database name
            'pass'  => 'lib' . DS . '.ht_pw',  // Path to MySQL password override file
            'path'  => 'sqlite/sysadm/sysadm.db',  // SQLite database path
            'port'  => '3306',          // Database port
            'sock'  => '',              // MySQL socket (optional)
            'type'  => 'sqlite',        // Database type (mysql | sqlite)
            'user'  => 'sysadm',        // Database username
        ],
        /**
         * First-level navigation settings
         * @var array
         */
        public readonly array $nav1 = [
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
        ],
        /**
         * Second-level navigation settings
         * @var array
         */
        public readonly array $nav2 = [
            ['local',   '?r=local', 'bi bi-globe'],
            ['mgo',     '?r=mgo',   'bi bi-globe'],
            ['vmd1',    '?r=vmd1',  'bi bi-globe'],
        ],
        /**
         * DNS configuration settings
         * @var array
         */
        public readonly array $dns = [
            'a'     => '127.0.0.1',     // Default A record
            'mx'    => '',              // Default MX record
            'ns1'   => 'ns1.',          // Primary nameserver
            'ns2'   => 'ns2.',          // Secondary nameserver
            'prio'  => 0,               // Priority setting
            'ttl'   => 300,             // Time to live
            // Start of Authority record settings
            'soa'   => [
                'primary'   => 'ns1.',  // Primary nameserver
                'email'     => 'admin.',// Domain email address
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
        ],
        /**
         * Access control levels (ACL) defining user roles
         * @var array
         */
        public readonly array $acl = [
            AclLevel::SuperAdmin->value    => AclLevel::SuperAdmin->name,
            AclLevel::Administrator->value => AclLevel::Administrator->name,
            AclLevel::User->value          => AclLevel::User->name,
            AclLevel::Suspended->value     => AclLevel::Suspended->name,
            AclLevel::Anonymous->value     => AclLevel::Anonymous->name,
        ]
    ) {}
});
