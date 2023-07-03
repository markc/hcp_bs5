<?php

declare(strict_types=1);
// lib/php/theme.php 20150101 - 20230627
// Copyright (C) 2015-2023 Mark Constable <markc@renta.net> (AGPL-3.0)

class Theme
{
    public $g;

    private string $buf = '';

    public function __construct($g)
    {
        $this->g = $g;
    }

    public function __toString(): string
    {
        return $this->buf;
    }

    public function __call(string $name, array $args): string
    {
        elog(__METHOD__ . '() name = ' . $name . ' class = ' . __CLASS__);

        return 'Theme::' . $name . '() not implemented';
    }

    public function log(): string
    {
        $logs = '';
        foreach (util::log() as $lvl => $msg) {
            $logs .= $msg ? '<p class="alert ' . $lvl . '">' . $msg . "</p>\n" : '';
        }

        return $logs;
    }

    public function nav1(): string
    {
        $o = '?o=' . $this->g->in['o'];

        return '
      <nav>' . implode('', array_map(function ($n) use ($o) {
            $c = $o === $n[1] ? ' class="active"' : '';

            return '
        <a' . $c . ' href="' . $n[1] . '">' . $n[0] . '</a>';
        }, $this->g->nav1)) . '
      </nav>';
    }

    public function head(): string
    {
        return '
    <header>
      <h1>
        <a href="' . $this->g->cfg['self'] . '">' . $this->g->out['head'] . '</a>
      </h1>' . $this->g->out['nav1'] . '
    </header>';
    }

    public function main(): string
    {
        return '
    <main>' . $this->g->out['log'] . $this->g->out['main'] . '
    </main>';
    }

    public function foot(): string
    {
        return '
    <footer class="text-center">
      <br>
      <p><em><small>' . $this->g->out['foot'] . '</small></em></p>
    </footer>';
    }

    public function end(): string
    {
        return '
    <pre>' . $this->g->out['end'] . '
    </pre>';
    }

    public function html(): string
    {
        extract($this->g->out, EXTR_SKIP);

        return '<!DOCTYPE html>
<html lang="en" data-bs-theme="auto">
  <head>
    <script src="lib/js/color-modes.js"></script>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>' . $doc . '</title>' . $css . '
  </head>
  <body>' . $head . $main . $foot . $end . $js . '
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
        string $extra = ''
    ): string {
        $opt = $label ? '
                <option value="">' . ucfirst($label) . '</option>' : '';
        $buf = '';
        $c = $class ? ' class="' . $class . '"' : '';
        foreach ($ary as $k => $v) {
            $t = str_replace('?t=', '', (string) $v[1]);
            $s = $sel === $t ? ' selected' : '';
            $buf .= '
                        <option value="' . $t . '"' . $s . '>' . $v[0] . '</option>';
        }

        return '
                      <select' . $c . ' name="' . $name . '" id="' . $name . '"' . $extra . '>' . $opt . $buf . '
                      </select>';
    }
}
