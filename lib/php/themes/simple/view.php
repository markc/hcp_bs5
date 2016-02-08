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

    public function nav1(array $a = []) : string
    {
error_log(__METHOD__);

        $a = isset($a[0]) ? $a : util::nav($this->g->nav1);
        $o = '?o='.$this->g->in['o'];
        $t = '?t='.$this->g->in['t'];
        return '
      <nav>'.join('', array_map(function ($n) use ($o) {
            if (is_array($n[1])) {
                return '
        <ul>
          <li>
            <a href="#">'.$n[0].'</a>
            <ul>'.join('', array_map(function ($x) use ($o) {
            $c = $o === $x[1] ? ' class="active"' : '';
            return '
              <li><a'.$c.' href="'.$x[1].'">'.$x[0].'</a></li>';
        }, $n[1])).'
            </ul>
          </li>
        </ul>';
            } else {
            $c = $o === $n[1] ? ' class="active"' : '';
            return '
        <a'.$c.' href="'.$n[1].'">'.$n[0].'</a>';
        }}, $a)).'
        <ul>
          <li>
            <a href="#">Themes</a>
            <ul>'.join('', array_map(function ($n) use ($t) {
            $c = $t === $n[1] ? ' class="active"' : '';
            return '
              <li><a'.$c.' href="'.$n[1].'">'.$n[0].'</a></li>';
        }, $this->g->nav2)).'
            </ul>
          </li>
        </ul>
      </nav>';
    }

    public function veto_a(string $href, string $label, string $class, string $extra) : array
    {
error_log(__METHOD__);

        return ['class' => 'btn '.$class];
    }
}
