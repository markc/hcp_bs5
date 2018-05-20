<?php
// lib/php/plugins/dkim.php 20180511 - 20180520
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

        $domain = trim(escapeshellarg(explode('._domainkey.', $this->in['dnstxt'])[1]), "'"); // too fragile?
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

        $buf = '<p style="columns:350px 3;column-rule: 1px dotted #ddd;text-align:center;">';
        exec("sudo dkim list 2>&1", $retArr, $retVal);
        foreach($retArr as $line) $buf .= '
            <a href="?o=dkim&m=read&dnstxt=' . $line . '"><b>' . $line . '</b></a>';
        return $this->t->list(['buf' => $buf . '</p>']);
    }
}

?>
