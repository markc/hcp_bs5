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

    public function nav1() : string
    {
error_log(__METHOD__);

        $o = '?o='.$this->g->in['o'];
        return '
      <nav>' . join('', array_map(function ($n) use ($o) {
            if (is_array($n[1])) {
                return join('', array_map(function ($x) use ($o) {
                    $c = $o === $x[1] ? ' class="active"' : '';
                    return '
        <a'.$c.' href="'.$x[1].'">'.$x[0].'</a>';
                }, $n[1]));
            } else {
                $c = $o === $n[1] ? ' class="active"' : '';
                return '
        <a' . $c . ' href="' . $n[1] . '">' . $n[0] . '</a>';
            }
        }, array_merge(util::nav($this->g->nav1), $this->g->nav2))) . '
      </nav>';
    }

    public function head() : string
    {
error_log(__METHOD__);

        return '
    <header>
      <h1>' . $this->g->out['head'] . '</h1>' . $this->g->out['nav1'] . '
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
