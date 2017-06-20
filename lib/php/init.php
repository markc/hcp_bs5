<?php
// lib/php/init.php 20150101 - 20170305
// Copyright (C) 2015-2017 Mark Constable <markc@renta.net> (AGPL-3.0)

class Init
{
    private $t = null;

    public function __construct($g)
    {
error_log(__METHOD__);

        session_start();
        //$_SESSION = []; // to reset session for testing
error_log('GET=' . var_export($_GET, true));

error_log('POST=' . var_export($_POST, true));

error_log('SESSION=' . var_export($_SESSION, true));

        util::cfg($g);
        $g->in = util::esc($g->in);
        $g->self = str_replace('index.php', '', $_SERVER['PHP_SELF']);
        util::ses('l');
        $t = util::ses('t', '', $g->in['t']);
        $t1 = 'themes_' . $t . '_' . $g->in['o'];
        $t2 = 'themes_' . $t . '_theme';

        $this->t = $thm = class_exists($t1) ? new $t1($g)
            : (class_exists($t2) ? new $t2($g) : new Theme($g));

        $p  = 'plugins_' . $g->in['o'];
        if (class_exists($p)) {
            util::remember($g);
            $g->out['main'] = (string) new $p($thm);
        } else $g->out['main'] = "Error: no plugin object!";

//        $g->out['end'] = var_export($_SESSION['usr'], true); // debug

        if (empty($g->in['x']) || ($g->in['x'] !== 'json'))
            foreach ($g->out as $k => $v)
                $g->out[$k] = method_exists($thm, $k) ? $thm->$k() : $v;
    }

    public function __toString() : string
    {
error_log(__METHOD__);
//error_log(var_export($this->t->g->out,true));

        $g = $this->t->g;
error_log(var_export($g->in,true));
        if ($g->in['x'] === 'json') {
//            $xhr = $g->out[$g->in['x']] ?? '';
            header('Content-Type: application/json');
            return $g->out['main'];
//            exit;
//            if ($xhr) {
//                if ($xhr == 'json') return $g->out['main'];
//                else return $xhr;
//            }
//            return json_encode($g->out, JSON_PRETTY_PRINT);
        }
        return $this->t->html();
    }

    public function __destruct()
    {
//dbg($this->t->g);
        error_log($_SERVER['REMOTE_ADDR'].' '.round((microtime(true)-$_SERVER['REQUEST_TIME_FLOAT']), 4));
    }
}

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
?>
