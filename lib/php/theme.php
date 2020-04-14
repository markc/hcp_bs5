<?php
// lib/php/theme.php 20150101 - 20200414
// Copyright (C) 2015-2020 Mark Constable <markc@renta.net> (AGPL-3.0)

class Theme
{
    private
    $buf = '',
    $in  = [];

    public function __construct(object $g)
    {
elog(__METHOD__);

        $this->g = $g;
    }

    public function __toString() : string
    {
elog(__METHOD__);

        return $this->buf;
    }

    public function log() : string
    {
elog(__METHOD__);

        $alts = '';
        foreach (util::log() as $lvl => $msg) {
            $alts .= $msg ?  '<p class="alert ' . $lvl . '">' . $msg . "</p>\n" : '';
        }
        return $alts;
    }

    public function nav1() : string
    {
elog(__METHOD__);

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
elog(__METHOD__);

        return '
    <header>
      <h1>
        <a href="' . $this->g->cfg['self'] . '">' . $this->g->out['head'] . '</a>
      </h1>' . $this->g->out['nav1'] . '
    </header>';
    }

    public function main() : string
    {
elog(__METHOD__);

        return '
    <main>' . $this->g->out['log'] . $this->g->out['main'] . '
    </main>';
    }

    public function foot() : string
    {
elog(__METHOD__);

        return '
    <footer class="text-center">
      <br>
      <p><em><small>' . $this->g->out['foot'] . '</small></em></p>
    </footer>';
    }

    public function end() : string
    {
elog(__METHOD__);

        return '
    <pre>' . $this->g->out['end'] . '
    </pre>';
    }

    public function html() : string
    {
elog(__METHOD__);

        extract($this->g->out, EXTR_SKIP);
        return '<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>' . $doc . '</title>' . $css . $js . '
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
elog(__METHOD__);

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
elog(__METHOD__ . '() name = ' . $name);

        return 'Theme::' . $name . '() not implemented';
    }
}

?>
