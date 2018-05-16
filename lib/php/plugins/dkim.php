<?php
// lib/php/plugins/dkim.php 20180511 - 20180516
// Copyright (C) 2015-2018 Mark Constable <markc@renta.net> (AGPL-3.0)

class Plugins_Dkim extends Plugin
{
    protected
    $in = [
        'dnstxt'  => '',
        'domain'  => '',
        'keylen'  => '1024',
        'select'  => 'dkim',
    ];

    public function create() : string
    {
error_log(__METHOD__);

        if (util::is_post())
            util::exe('dkim add ' . $this->in['domain'] . ' ' . $this->in['select']. ' ' . $this->in['keylen']);
        return $this->list();
    }

    public function read() : string
    {
error_log(__METHOD__);

        $domain = trim(escapeshellarg(explode('._domainkey.', $this->in['dnstxt'])[1]), "'");
        exec("sudo dkim show $domain 2>&1", $retArr, $retVal);
        $buf = '
        <b>' . $retArr[0] . '</b><br>
        <div style="word-break:break-all;font-family:monospace;width:100%;">' . $retArr[1] . '</div>';
        return $this->t->read(['buf' => $buf, 'domain' => $domain]);
    }

    public function update() : string
    {
error_log(__METHOD__);

        return $this->list(); // override parent update()
    }

    public function delete() : string
    {
error_log(__METHOD__);

        if (util::is_post())
            util::exe('dkim del ' . $this->in['domain']);
        return $this->list();
    }

    public function list() : string
    {
error_log(__METHOD__);

        $buf = '';
        exec("sudo dkim show 2>&1", $retArr, $retVal);
        $cnt = count($retArr);
        for($i = 0; $i < $cnt; $i++) $buf .= ($i % 2 == 0) ? '
        <a href="?o=dkim&m=read&dnstxt=' . $retArr[$i] . '"><b>' . $retArr[$i] . '</b></a><br>' : '
        <div style="word-break:break-all;font-family:monospace;width:100%;">' . $retArr[$i] . '</div><hr>';
        return $this->t->list(['buf' => $buf]);
    }
}

?>
