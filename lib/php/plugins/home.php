<?php

declare(strict_types=1);

// lib/php/plugins/home.php 20150101 - 20240904
// Copyright (C) 2015-2024 Mark Constable <markc@renta.net> (AGPL-3.0)

class Plugins_Home extends Plugin
{
    public array $inp = [];

    public function list(): string
    {
        if (file_exists(INC . 'home.tpl')) {
            return (function () {
                ob_start();
                include INC . 'home.tpl';
                return ob_get_clean();
            })();
        }

        return $this->g->t->list([]);
    }
}
