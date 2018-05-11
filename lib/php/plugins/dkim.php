<?php
// lib/php/plugins/dkim.php 20180511 - 20180511
// Copyright (C) 2015-2018 Mark Constable <markc@renta.net> (AGPL-3.0)

class Plugins_Dkim extends Plugin
{
    public function list() : string
    {
error_log(__METHOD__);

        return $this->t->list([]);
    }

}

?>
