<?php

declare(strict_types=1);
// lib/php/theme.php 20150101 - 20240904
// Copyright (C) 2015-2024 Mark Constable <markc@renta.net> (AGPL-3.0)

class Theme
{
    private string $buf = '';

    public function __construct(public object $g)
    {
    }

    public function __toString(): string
    {
        return $this->buf;
    }

    public function __call(string $name, array $args): string
    {
        elog(__METHOD__ . "() name = $name class = " . __CLASS__);
        return "Theme::$name() not implemented";
    }

    public function log(): string
    {
        return implode('', array_map(
            fn($lvl, $msg) => $msg ? "<p class=\"alert $lvl\">$msg</p>\n" : '',
            array_keys(util::log()),
            util::log()
        ));
    }

    public function nav1(): string
    {
        $o = "?o={$this->g->in['o']}";
        $navItems = array_map(
            fn($n) => sprintf(
                '<a%s href="%s">%s</a>',
                $o === $n[1] ? ' class="active"' : '',
                $n[1],
                $n[0]
            ),
            $this->g->nav1
        );

        return "<nav>" . implode('', $navItems) . "</nav>";
    }

    public function head(): string
    {
        return <<<HTML
        <header>
            <h1><a href="{$this->g->cfg['self']}">{$this->g->out['head']}</a></h1>
            {$this->g->out['nav1']}{$this->g->out['nav2']}
        </header>
        HTML;
    }

    public function main(): string
    {
        return "<main>{$this->g->out['log']}{$this->g->out['main']}</main>";
    }

    public function foot(): string
    {
        return <<<HTML
        <footer class="text-center">
            <br>
            <p><em><small>{$this->g->out['foot']}</small></em></p>
        </footer>
        HTML;
    }

    public function end(): string
    {
        return "<pre>{$this->g->out['end']}</pre>";
    }

    public function html(): string
    {
        extract($this->g->out, EXTR_SKIP);
        return <<<HTML
        <!DOCTYPE html>
        <html lang="en" data-bs-theme="auto">
            <head>
                <script src="lib/js/color-modes.js"></script>
                <meta charset="utf-8">
                <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
                <title>$doc</title>$css
            </head>
            <body>$head$main$foot$end$js</body>
        </html>
        HTML;
    }

    public static function dropdown(
        array $ary,
        string $name,
        string $sel = '',
        string $label = '',
        string $class = '',
        string $extra = ''
    ): string {
        $opt = $label ? "<option value=\"\">" . ucfirst($label) . "</option>" : '';
        $c = $class ? " class=\"$class\"" : '';
        
        $options = array_map(function($v) use ($sel) {
            $t = str_replace('?t=', '', (string)$v[1]);
            $s = $sel === $t ? ' selected' : '';
            return "<option value=\"$t\"$s>{$v[0]}</option>";
        }, $ary);

        return <<<HTML
        <select$c name="$name" id="$name"$extra>
            $opt
            {implode('', $options)}
        </select>
        HTML;
    }
}
