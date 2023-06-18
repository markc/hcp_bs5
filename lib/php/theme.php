<?php

declare(strict_types=1);
// lib/php/theme.php 20150101 - 20230604
// Copyright (C) 2015-2023 Mark Constable <markc@renta.net> (AGPL-3.0)

/**
 * Summary of Theme
 * @author Mark Constable
 * @copyright (c) 2023
 */
class Theme
{
    /**
     * Summary of buf
     * @var string
     */
    private string $buf = '';
    //private array $in = [];

    /**
     * Summary of __construct
     * @param object $g
     */
    public function __construct(public Object $g)
    {
        elog(__METHOD__);

        $this->g = $g;
    }

    /**
     * Summary of __toString
     * @return string
     */
    public function __toString(): string
    {
        elog(__METHOD__);

        return $this->buf;
    }

    /**
     * Summary of __call
     * @param string $name
     * @param array $args
     * @return string
     */
    public function __call(string $name, array $args): string
    {
        elog(__METHOD__ . '() name = ' . $name . ' class = ' . __CLASS__);

        return 'Theme::' . $name . '() not implemented';
    }

    /**
     * Summary of log
     * @return string
     */
    public function log(): string
    {
        elog(__METHOD__);

        $alts = '';
        foreach (util::log() as $lvl => $msg) {
            $alts .= $msg ? '<p class="alert ' . $lvl . '">' . $msg . "</p>\n" : '';
        }

        return $alts;
    }

    /**
     * Summary of nav1
     * @return string
     */
    public function nav1(): string
    {
        elog(__METHOD__);

        $o = '?o=' . $this->g->in['o'];

        return '
      <nav>' . implode('', array_map(function ($n) use ($o) {
            $c = $o === $n[1] ? ' class="active"' : '';

            return '
        <a' . $c . ' href="' . $n[1] . '">' . $n[0] . '</a>';
        }, $this->g->nav1)) . '
      </nav>';
    }

    /**
     * Summary of head
     * @return string
     */
    public function head(): string
    {
        elog(__METHOD__);

        return '
    <header>
      <h1>
        <a href="' . $this->g->cfg['self'] . '">' . $this->g->out['head'] . '</a>
      </h1>' . $this->g->out['nav1'] . '
    </header>';
    }

    /**
     * Summary of main
     * @return string
     */
    public function main(): string
    {
        elog(__METHOD__);

        return '
    <main>' . $this->g->out['log'] . $this->g->out['main'] . '
    </main>';
    }

    /**
     * Summary of foot
     * @return string
     */
    public function foot(): string
    {
        elog(__METHOD__);

        return '
    <footer class="text-center">
      <br>
      <p><em><small>' . $this->g->out['foot'] . '</small></em></p>
    </footer>';
    }

    /**
     * Summary of end
     * @return string
     */
    public function end(): string
    {
        elog(__METHOD__);

        return '
    <pre>' . $this->g->out['end'] . '
    </pre>';
    }

    /**
     * Summary of html
     * @return string
     */
    public function html(): string
    {
        elog(__METHOD__);

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

    /**
     * Summary of dropdown
     * @param array $ary
     * @param string $name
     * @param string $sel
     * @param string $label
     * @param string $class
     * @param string $extra
     * @return string
     */
    public static function dropdown(
        array $ary,
        string $name,
        string $sel = '',
        string $label = '',
        string $class = '',
        string $extra = ''
    ): string {
        elog(__METHOD__);

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
