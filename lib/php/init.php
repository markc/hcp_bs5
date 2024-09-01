<?php

declare(strict_types=1);

// lib/php/init.php 20150101 - 20240901
// Copyright (C) 2015-2024 Mark Constable <markc@renta.net> (AGPL-3.0)

class Init
{
    /**
     * Constructor to initialize the application.
     *
     * @param object $g Global configuration and state object
     */
    public function __construct(public object $g)
    {
        // Start the session
        session_start();

        // Log request data for debugging
        elog('GET=' . var_export($_GET, true));
        elog('POST=' . var_export($_POST, true));
        elog('SESSION=' . var_export($_SESSION, true));
        elog('REQUEST=' . var_export($_REQUEST, true));

        //$_SESSION = []; // Uncomment to reset session for testing

        // Set the host from environment variable if not already set
        $this->g->cfg['host'] ??= getenv('HOSTNAME');

        // Determine the base path of the application
        $this->g->cfg['self'] = str_replace('index.php', '', $_SERVER['PHP_SELF']);

        // Load configuration overrides if they exist
        util::cfg($this->g);

        // Escape input parameters
        $this->g->in = util::esc($this->g->in);

        // Handle API request or restore previous session data
        $this->g->in['a'] ? util::chkapi($this->g) : util::remember($this->g);

        // Generate a session token if not already set
        if (!isset($_SESSION['c'])) {
            $_SESSION['c'] = Util::random_token(32);
        }

        // Manage session variables
        util::ses('o');
        util::ses('m');
        util::ses('l');
        util::ses('r', '', 'local'); // Default remote target is 'local'

        // Determine the theme to use
        $thm = util::ses('t', '', $this->g->in['t']);

        // Determine the appropriate theme and plugin classes to use
        $t1 = 'themes_' . $thm . '_' . $this->g->in['o'];
        $t2 = 'themes_' . $thm . '_theme';
        $p = 'plugins_' . $this->g->in['o'];

        // Instantiate the theme object
        $this->g->t = class_exists($t1)
        ? new $t1($this->g)
        : (class_exists($t2) ? new $t2($this->g) : new Theme($this->g));

        // Process the main content using the plugin class if it exists
        $this->g->out['main'] = class_exists($p)
        ? (string) new $p($this->g)
        : "Error: plugin '$p' does not exist!";

        // If not an XHR request, process additional content output
        if (empty($this->g->in['x'])) {
            foreach ($this->g->out as $k => $v) {
                $this->g->out[$k] = method_exists($this->g->t, $k)
                ? $this->g->t->{$k}() : $v;
            }
        }
    }

    /**
     * Destructor to log execution time and any necessary cleanup.
     */
    public function __destruct()
    {
        // Log execution time and remote address for debugging
        elog(__FILE__ . ' ' . $_SERVER['REMOTE_ADDR'] . ' ' . round((microtime(true) - $_SERVER['REQUEST_TIME_FLOAT']), 4) . "\n");
    }

    /**
     * Convert the object to a string representation based on the request type.
     *
     * @return string The response content in the appropriate format
     */
    public function __toString(): string
    {
        $x = $this->g->in['x'];

        // Return HTML content
        if ($x === 'html') {
            elog($this->g->out['main']);
            return $this->g->out['main'];
        }

        // Return plain text content
        if ($x === 'text') {
            return preg_replace('/^\h*\v+/m', '', strip_tags($this->g->out['main']));
        }

        // Return JSON content
        if ($x === 'json') {
            header('Content-Type: application/json');
            return $this->g->out['main'];
        }

        // Return specific output as JSON if specified
        if ($x) {
            $out = $this->g->out[$x] ?? '';
            if ($out) {
                header('Content-Type: application/json');
                return json_encode($out, JSON_PRETTY_PRINT);
            }
        }

        // Default to rendering the HTML view
        return $this->g->t->html();
    }
}

/**
 * Logs a message to the error log if debugging is enabled.
 *
 * @param string $content The message to log
 */
function elog(string $content): void
{
    if (DBG) {
        error_log($content);
    }
}
