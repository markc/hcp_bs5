<?php
// lib/php/plugins/about.php 20150101 - 20180503
// Copyright (C) 2015-2018 Mark Constable <markc@renta.net> (AGPL-3.0)

class Plugins_About extends Plugin
{
    public function list() : string
    {
error_log(__METHOD__);

        return $this->t->list([]);
    }
}

?>
