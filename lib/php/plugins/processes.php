<?php
// plugins/processes.php 20170225 - 20180430
// Copyright (C) 2015-2018 Mark Constable <markc@renta.net> (AGPL-3.0)

class Plugins_Processes extends Plugin
{
    public function list() : string
    {
elog(__METHOD__);

        return $this->t->list(['procs' => shell_exec('sudo processes')]);
    }
}

?>
