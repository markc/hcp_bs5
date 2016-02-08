<?php declare(strict_types = 1);?>
<?php error_log(__FILE__);?>
<?php
// view.php 20151015 (C) 2015 Mark Constable <markc@renta.net> (AGPL-3.0)

class View extends Widgets
{
    public $g = null;

    public function __construct($g)
    {
error_log(__METHOD__);

        $this->g = $g;
    }

    public function __call(string $name, array $args) : string
    {
error_log(__METHOD__);

        $t1 = INC.'themes' . DS . $_SESSION['t'] . DS . str_replace('_', DS, $name).'.php';
        $t2 = INC.'themes' . DS . 'none' . DS . str_replace('_', DS, $name).'.php';

        if (isset($args[0]) and is_array($args[0]))
            extract($args[0]);
        else extract($args);

        if (file_exists($t1)) return include $t1;
        elseif (file_exists($t2)) return include $t2;
        elseif (method_exists($this, $name)) return $this->$name($args);
        else return 'Error: widget view does not exist: ' . $name;
    }

    public function log() : string
    {
error_log(__METHOD__);

        list($l, $m) = util::log();
        return $m ? '
      <p class="alert ' . $l . '">' . $m . '</p>' : '';
    }

    public function nav(array $a) : string
    {
error_log(__METHOD__);

        $o = '?o='.$this->g->in['o'];
        $t = '?t='.$this->g->in['t'];
        return join('', array_map(function ($n) use ($o, $t) {
error_log(var_export($n, true));
            $l = ($o === $n[1] || $t === $n[1]) ? '<b>' . $n[0] . '</b>' : $n[0];
            return '
        <a href="' . $n[1] . '">' . $l . '</a>';
        }, $a));
    }

    public function nav1() : string
    {
error_log(__METHOD__);

        $a = util::nav($this->g->nav1);
        array_shift($a);
error_log(var_export($a, true));
        $b = '';
        foreach($a as $n)
//            $b .= is_array($n[1]) ? ' is_array ' : ' NOT array ';
            $b .= is_array($n[1]) ? $this->nav($n[1]) : $this->nav([$n]);

        return $b;
    }

    public function nav2() : string
    {
error_log(__METHOD__);

        return $this->nav($this->g->nav2[0][1]);
    }

    public function head() : string
    {
error_log(__METHOD__);

        $n = util::nav($this->g->nav1)[0];
        return '
    <header>
      <h1>
        <a href="' . $n[1] . '">' . $n[0] . '</a>
      </h1>
      <nav>' . $this->g->out['nav1'] . $this->g->out['nav2'] . '</nav>
    </header>';
    }

    public function main() : string
    {
error_log(__METHOD__);

        return '
    <main>' . $this->g->out['log'] . $this->g->out['main'] . '
    </main>';
    }

    public function foot() : string
    {
error_log(__METHOD__);

        return '
    <footer>
      <em><small>' . $this->g->out['foot'] . '</small></em>
    </footer>';
    }

    public function html() : string
    {
error_log(__METHOD__);

        extract($this->g->out, EXTR_SKIP);
        return '<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>' . $doc . '</title>' . $css . '
  </head>
  <body>' . $top . $head . $main . $foot . $end . '
  </body>
</html>
';
    }
}
