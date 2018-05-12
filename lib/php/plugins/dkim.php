<?php
// lib/php/plugins/dkim.php 20180511 - 20180511
// Copyright (C) 2015-2018 Mark Constable <markc@renta.net> (AGPL-3.0)

class Plugins_Dkim extends Plugin
{
    public function list() : string
    {
error_log(__METHOD__);

        $retArr = []; $retVal = null;
        exec("sudo dkim show 2>&1", $retArr, $retVal);
//        util::log('<pre>' . trim(implode("\n", $retArr)) . '</pre>', $retVal ? 'danger' : 'success');
        return $this->t->list(['buf' =>  trim(implode("\n", $retArr))]);
    }

}

?>
