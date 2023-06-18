<?php

declare(strict_types=1);
// lib/php/init.php 20150101 - 20200414
// Copyright (C) 2015-2020 Mark Constable <markc@renta.net> (AGPL-3.0)

/**
 * Summary of Init
 * @author Mark Constable
 * @copyright (c) 2023
 */
class Init
{
    /**
     * Summary of t
     * @var
     */
    private $t;

    /**
     * Summary of __construct
     */
    public function __construct()
    {
        elog(__METHOD__);

        $g = gbl::getInstance();

        session_start();

        elog('GET=' . var_export($_GET, true));
        elog('POST=' . var_export($_POST, true));
        elog('SESSION=' . var_export($_SESSION, true));

        //$_SESSION = []; // to reset session for testing

        $g->cfg['host'] ??= getenv('HOSTNAME');

        util::cfg($g);
        $g->in = util::esc($g->in);
        $g->cfg['self'] = str_replace('index.php', '', $_SERVER['PHP_SELF']);

        if (!isset($_SESSION['c'])) {
            $_SESSION['c'] = Util::random_token(32);
        }
        util::ses('o');
        util::ses('m');
        util::ses('l');

        $t = util::ses('t', '', $g->in['t']);
        $t1 = 'themes_' . $t . '_' . $g->in['o'];
        $t2 = 'themes_' . $t . '_theme';

        $this->t = $thm = class_exists($t1)
            ? new $t1($g)
            : (class_exists($t2) ? new $t2($g) : new Theme($g));

        $p = 'plugins_' . $g->in['o'];

        if (class_exists($p)) {
            $g->in['a'] ? util::chkapi($g) : util::remember($g);
            $g->out['main'] = (string) new $p($thm);
        } else {
            $g->out['main'] = 'Error: no plugin object!';
        }

        if (empty($g->in['x'])) {
            foreach ($g->out as $k => $v) {
                $g->out[$k] = method_exists($thm, $k) ? $thm->{$k}() : $v;
            }
        }
    }

    /**
     * Summary of __destruct
     */
    public function __destruct()
    {
        //error_log('SESSION=' . var_export($_SESSION, true));
        elog(__FILE__ . ' ' . $_SERVER['REMOTE_ADDR'] . ' ' . round((microtime(true) - $_SERVER['REQUEST_TIME_FLOAT']), 4) . "\n");
    }

    /**
     * Summary of __toString
     * @return string
     */
    public function __toString(): string
    {
        elog(__METHOD__);

        $g = $this->t->g;
        $x = $g->in['x'];
        if ('html' === $x) {
            elog($g->out['main']);
            return $g->out['main'];
        }
        if ('text' === $x) {
            return preg_replace('/^\h*\v+/m', '', strip_tags($g->out['main']));
        }
        if ('json' === $x) {
            header('Content-Type: application/json');

            return $g->out['main'];
        }
        if ($x) {
            $out = $g->out[$x] ?? '';
            if ($out) {
                header('Content-Type: application/json');

                return json_encode($out, JSON_PRETTY_PRINT);
            }
        }

        return $this->t->html();
    }
}
