<?php
// lib/php/plugins/bion/sites.php 20190225 - 20190427
// Copyright (C) 2015-2019 Mark Constable <markc@renta.net> (AGPL-3.0)

class Plugins_Bion_Sites extends Plugin
{
    protected
    $tbl = 'bion_sites',
    $in = [
        'name'          => '',
        'city'          => '',
        'postcode'      => '',
        'client'        => '',
    ];

    protected function list() : string
    {
//error_log(__METHOD__.' x='.$this->g->in['x']);

        if ($this->g->in['x'] === 'json') {
            $columns = [
                ['dt' => null, 'db' => 'id'],
                ['dt' => 0, 'db' => 'name', 'formatter' => function($d, $row) {
                    return '
                    <b><a href="?o=bion_sites&m=read&i=' . $row['id'] . '">' . $d . '</a></b>';
                }],
                ['dt' => 1, 'db' => 'city'],
                ['dt' => 2, 'db' => 'postcode'],
                ['dt' => 3, 'db' => 'client'],
/*
                ['dt' => 3, 'db' => 'clients_id', 'formatter' => function($d, $row) {
//error_log(var_export($row,true));
                    return '
                    <b><a href="?o=bion_clients&m=read&i=' . $d . '">' . $row['clients_name'] . '</a></b>';
                }],
*/
            ];
            return json_encode(db::simple($_GET, $this->tbl, 'id', $columns), JSON_PRETTY_PRINT);
        }
        return $this->t->list($this->in);
    }
}

?>
