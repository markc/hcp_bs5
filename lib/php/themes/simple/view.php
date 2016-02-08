<?php declare(strict_types = 1);?>
<?php error_log(__FILE__);?>
<?php
// simple.php 20151015 (C) 2015 Mark Constable <markc@renta.net> (AGPL-3.0)

class Themes_Simple_View extends View
{
    function css()
    {
        return '
    <link href="//fonts.googleapis.com/css?family=Roboto:500,400,300,300italic,100,100italic" rel="stylesheet" type="text/css">
    <link href="lib/css/simple.css" media="all" rel="stylesheet">';
    }

    // FIXME: this is messy
    public function nav(array $a = []) : string
    {
error_log(__METHOD__);

        $o = '?o='.$this->g->in['o'];
        $t = '?t='.$this->g->in['t'];
        return join('', array_map(function ($n) use ($o, $t) {
            if (is_array($n[1])) return $this->nav_dropdown($n[1], $n[0], $n[2]);
            $c = ($o === $n[1] || $t === $n[1]) ? ' class="active"' : '';
            return '
        <a' . $c . ' href="' . $n[1] . '">' . $n[0] . '</a>';
        }, $a));
    }

    public function nav_dropdown(array $a = [], $label = '', $icon = '', $rhs = '') : string
    {
error_log(__METHOD__);

        $o = '?o='.$this->g->in['o'];
        $t = '?t='.$this->g->in['t'];
        return '
        <ul>
          <li>
            <a href="#">' . $label . '</a>
            <ul>' . join('', array_map(function ($x) use ($o, $t) {
            $c = ($o === $x[1] || $t === $x[1]) ? ' class="active"' : '';
            return '
              <li><a' . $c . ' href="' . $x[1] . '">' . $x[0] . '</a></li>';
        }, $a)) . '
            </ul>
          </li>
        </ul>';
    }

    public function nav1() : string
    {
error_log(__METHOD__);

        $a = util::nav($this->g->nav1);
        array_shift($a);
        return '
      <nav>' . $this->nav($a);
    }

    public function nav2() : string
    {
error_log(__METHOD__);

        $n = $this->g->nav2[0];
        return $this->nav_dropdown($n[1], $n[0], $n[2], '') . '
      </nav>';
    }

    public function veto_a(string $href, string $label, string $class, string $extra) : array
    {
error_log(__METHOD__);

        return ['class' => 'btn '.$class];
    }
}
