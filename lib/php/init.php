<?php

declare(strict_types=1);

// lib/php/init.php 20150101 - 20240904
// Copyright (C) 2015-2024 Mark Constable <markc@renta.net> (AGPL-3.0)

class Init
{
    public function __construct(public object $g)
    {
        $this->initializeSession();
        $this->logRequestData();
        $this->configureEnvironment();
        $this->handleAuthentication();
        $this->initializeThemeAndPlugin();
        $this->generateOutput();
    }

    private function initializeSession(): void
    {
        session_start();
        $_SESSION['c'] ??= Util::random_token(32);
    }

    private function logRequestData(): void
    {
        elog('GET=' . var_export($_GET, true));
        elog('POST=' . var_export($_POST, true));
        elog('SESSION=' . var_export($_SESSION, true));
        elog('REQUEST=' . var_export($_REQUEST, true));
    }

    private function configureEnvironment(): void
    {
        $this->g->cfg['host'] ??= getenv('HOSTNAME');
        $this->g->cfg['self'] = str_replace('index.php', '', $_SERVER['PHP_SELF']);
        util::cfg($this->g);
        $this->g->in = util::esc($this->g->in);
    }

    private function handleAuthentication(): void
    {
        $this->g->in['a'] ? util::chkapi($this->g) : util::remember($this->g);
        util::ses('o');
        util::ses('m');
        util::ses('l');
        util::ses('r', '', 'local');
    }

    private function initializeThemeAndPlugin(): void
    {
        $thm = util::ses('t', '', $this->g->in['t']);
        $t1 = 'themes_' . $thm . '_' . $this->g->in['o'];
        $t2 = 'themes_' . $thm . '_theme';
        $p = 'plugins_' . $this->g->in['o'];

        $this->g->t = class_exists($t1)
            ? new $t1($this->g)
            : (class_exists($t2) ? new $t2($this->g) : new Theme($this->g));

        $this->g->out['main'] = class_exists($p)
            ? (string) new $p($this->g)
            : "Error: plugin '$p' does not exist!";
    }

    private function generateOutput(): void
    {
        if (empty($this->g->in['x'])) {
            foreach ($this->g->out as $k => $v) {
                $this->g->out[$k] = method_exists($this->g->t, $k)
                    ? $this->g->t->{$k}()
                    : $v;
            }
        }
    }

    public function __destruct() {
        static $start;

        if (!isset($start)) {
            $start = microtime(true);
        } else {
            elog(
                __FILE__
                . ' '
                . $_SERVER['REMOTE_ADDR']
                . ' '
                . round(microtime(true) - $start, 4)
                . "\n"
            );
        }
    }

    public function __toString(): string
    {
        $x = $this->g->in['x'];
        $out = $this->g->out;

        if ($x === 'html') {
            return $out['main'];
        }

        if ($x === 'text') {
            return trim(
                preg_replace(
                    '/^\h*\v+/m',
                    '',
                    strip_tags($out['main'])
                )
            );
        }

        if ($x === 'json' || array_key_exists($x, $out)) {
            header('Content-Type: application/json');

            if ($x === 'json') {
                return json_encode($out['main'], JSON_PRETTY_PRINT);
            }

            return json_encode($out[$x], JSON_PRETTY_PRINT);
        }

        return $this->g->t->html();
    }
}

function elog(string $content): void
{
    if (DBG) {
        error_log($content);
    }
}
