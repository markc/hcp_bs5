<?php
// lib/php/theme.php 20150101 - 20170305
// Copyright (C) 2015-2017 Mark Constable <markc@renta.net> (AGPL-3.0)

class Theme
{
    private
    $buf = '',
    $in  = [];

    public function __construct($g)
    {
error_log(__METHOD__);

        $this->g = $g;
    }

    public function __toString() : string
    {
error_log(__METHOD__);

        return $this->buf;
    }

    public function log() : string
    {
error_log(__METHOD__);

        list($lvl, $msg) = util::log();
        return $msg ? '
      <p class="alert ' . $lvl . '">' . $msg . '</p>' : '';
    }

    public function nav1() : string
    {
error_log(__METHOD__);

        $o = '?o='.$this->g->in['o'];
        return '
      <nav>' . join('', array_map(function ($n) use ($o) {
            $c = $o === $n[1] ? ' class="active"' : '';
            return '
        <a' . $c . ' href="' . $n[1] . '">' . $n[0] . '</a>';
        }, $this->g->nav1)) . '
      </nav>';
    }

    public function head() : string
    {
error_log(__METHOD__);

        return '
    <header>
      <h1>
        <a href="' . $this->g->self . '">' . $this->g->out['head'] . '</a>
      </h1>' . $this->g->out['nav1'] . '
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
    <footer class="text-center">
      <br>
      <p><em><small>' . $this->g->out['foot'] . '</small></em></p>
    </footer>';
    }

    public function end() : string
    {
error_log(__METHOD__);

        return '
    <pre>' . $this->g->out['end'] . '
    </pre>';
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
  <body>' . $head . $main . $foot . $end . '
  </body>
</html>
';
    }

    public static function dropdown(
        array $ary,
        string $name,
        string $sel = '',
        string $label = '',
        string $class = '',
        string $extra = '') : string
    {
error_log(__METHOD__);

        $opt = $label ? '
          <option value="">' . ucfirst($label) . '</option>' : '';
        $buf = '';
        $c = $class ? ' class="' . $class . '"' : '';
        foreach($ary as $k => $v) {
            $t = str_replace('?t=', '', $v[1]);
            $s = $sel === $t ? ' selected' : '';
            $buf .= '
          <option value="' . $t . '"' . $s . '>' . $v[0] . '</option>';
        }
        return '
        <select' . $c . ' name="' . $name . '" id="' . $name . '"' . $extra . '>' . $opt . $buf . '
        </select>';
    }

    public function __call(string $name, array $args) : string
    {
error_log(__METHOD__ . '() name = ' . $name . ', args = '. var_export($args,true));

        return 'Theme::' . $name . '() not implemented';
    }
}

?>
