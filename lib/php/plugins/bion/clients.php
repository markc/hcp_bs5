<?php
// lib/php/plugins/bion/clients.php 20190225 - 20190427
// Copyright (C) 2015-2019 Mark Constable <markc@renta.net> (AGPL-3.0)

class Plugins_Bion_Clients extends Plugin
{
    protected
    $tbl = 'bion_clients',
    $in = [
        'name'      => '',
        'login'     => '',
        'admin'     => '',
    ];

    protected function list() : string
    {
//error_log(__METHOD__.' x='.$this->g->in['x']);

        if ($this->g->in['x'] === 'json') {
            $columns = [
                ['dt' => null, 'db' => 'id'],
                ['dt' => 0, 'db' => 'name', 'formatter' => function($d, $row) {
                    return '
                    <b><a href="?o=bion_clients&m=read&i=' . $row['id'] . '">' . $d . '</a></b>';
                }],
                ['dt' => 1, 'db' => 'login'],
                ['dt' => 2, 'db' => 'admin'],
            ];
            return json_encode(db::simple($_GET, $this->tbl, 'id', $columns), JSON_PRETTY_PRINT);
        }
        return $this->t->list($this->in);
    }
}

?>
