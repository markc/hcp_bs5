<?php
// lib/php/plugins/home.php 20150101 - 20180614
// Copyright (C) 2015-2018 Mark Constable <markc@renta.net> (AGPL-3.0)

class Plugins_Home extends Plugin
{
    public function list() : string
    {
elog(__METHOD__);

        if (file_exists(INC . 'home.tpl')) {
            ob_start();
            include INC . 'home.tpl';
            return ob_get_clean();
        }
        return $this->t->list([]);
    }
}

?>
