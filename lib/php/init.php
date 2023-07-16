<?php

declare(strict_types=1);
// lib/php/init.php 20150101 - 20200414
// Copyright (C) 2015-2020 Mark Constable <markc@renta.net> (AGPL-3.0)

class Init
{
    public function __construct(
        public object $g
    ) {
        session_start();

        elog('GET=' . var_export($_GET, true));
        elog('POST=' . var_export($_POST, true));
        elog('SESSION=' . var_export($_SESSION, true));
        elog('REQUEST=' . var_export($_REQUEST, true));

        //$_SESSION = []; // to reset session for testing

        $this->g->cfg['host'] ??= getenv('HOSTNAME');
        $this->g->cfg['self'] = str_replace('index.php', '', $_SERVER['PHP_SELF']);
        util::cfg($this->g); // include config override file if exists

        $this->g->in = util::esc($this->g->in);

        $this->g->in['a'] ? util::chkapi($this->g) : util::remember($this->g);

        if (!isset($_SESSION['c'])) {
            $_SESSION['c'] = Util::random_token(32);
        }
        util::ses('o');
        util::ses('m');
        util::ses('l');
        util::ses('r');

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

        if (empty($this->g->in['x'])) {
            foreach ($this->g->out as $k => $v) {
                $this->g->out[$k] = method_exists($this->g->t, $k)
                    ? $this->g->t->{$k}() : $v;
            }
        }
    }

    public function __destruct()
    {
        //error_log('SESSION=' . var_export($_SESSION, true));
        elog(__FILE__ . ' ' . $_SERVER['REMOTE_ADDR'] . ' ' . round((microtime(true) - $_SERVER['REQUEST_TIME_FLOAT']), 4) . "\n");
    }

    public function __toString(): string
    {
        $x = $this->g->in['x'];
        if ('html' === $x) {
            elog($this->g->out['main']);
            return $this->g->out['main'];
        }
        if ('text' === $x) {
            return preg_replace('/^\h*\v+/m', '', strip_tags($this->g->out['main']));
        }
        if ('json' === $x) {
            header('Content-Type: application/json');

            return $this->g->out['main'];
        }
        if ($x) {
            $out = $this->g->out[$x] ?? '';
            if ($out) {
                header('Content-Type: application/json');

                return json_encode($out, JSON_PRETTY_PRINT);
            }
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
