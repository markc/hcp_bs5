<?php
// lib/php/plugins/dkim.php 20180511 - 20180511
// Copyright (C) 2015-2018 Mark Constable <markc@renta.net> (AGPL-3.0)

class Plugins_Dkim extends Plugin
{
    protected
    $in = [
        'domain'    => '',
        'selector'  => 'dkim',
        'bits'      => '1024',
    ];

    public function create() : string
    {
error_log(__METHOD__);

        if ($_POST) {
error_log('this->in='.var_export($this->in,true));
            // check if domain is in vhosts
            extract($this->in);
            exec("sudo dkim add $selector $domain $bits 2>&1", $retArr, $retVal);
            util::log('<pre>' . trim(implode("\n", $retArr)) . '</pre>', $retVal ? 'danger' : 'success');
        }
        return $this->list();
    }

    public function list() : string
    {
error_log(__METHOD__);

//        $retArr = []; $retVal = null;
        exec("sudo dkim show 2>&1", $retArr, $retVal);
        $buf = '';
        $cnt = count($retArr);
        for($i = 0; $i < $cnt; $i++) {
            $buf .= ($i % 2 == 0) ? '
        <b>'.$retArr[$i].'</b><br>' : '
        <div style="word-break:break-all;font-family:monospace;width:100%;">' . $retArr[$i] . '</div><hr>';
        }
        return $this->t->list(['buf' => $buf]);
    }

}

?>
