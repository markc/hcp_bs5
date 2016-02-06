<?php declare(strict_types = 1);?>
<?php error_log(__FILE__);?>
<?php
// model.php 20151015 (C) 2015 Mark Constable <markc@renta.net> (AGPL-3.0)

class Model
{
    public function __construct(View $t, $g)
    {
error_log(__METHOD__);

        $o = INC.'pages/'.str_replace('_', DS, $g->in['o']).'.php';

        if (method_exists($this, $g->in['o'])) {
            $g->out['main'] = $this->{$g->in['o']}($t, $g);
        } elseif (file_exists($o)) {
            $g->out['main'] = include $o;
        } elseif (class_exists($g->in['o'])) {
            db::$dbh = $g->dbh = new db($g->db);
            util::remember($g->db);
            $g->out['main'] = new $g->in['o']($t, $g);
        }
    }

//    function home() { return '<h2>Home Page</h2><p>Lorem ipsum home.</p>'; }
//    function about() { return '<h2>About Page</h2><p>Lorem ipsum about.</p>'; }
//    function contact() { return '<h2>Contact Page</h2><p>Lorem ipsum contact.</p>'; }
}
