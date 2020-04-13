<?php
// lib/php/plugins/dkim.php 20180511 - 20180529
// Copyright (C) 2015-2018 Mark Constable <markc@renta.net> (AGPL-3.0)

class Plugins_Dkim extends Plugin
{
    protected
    $in = [
        'dnstxt'  => '',
        'domain'  => '',
        'keylen'  => '2048',
        'select'  => 'mail',
    ];

    public function create() : string
    {
elog(__METHOD__);

        if (util::is_post()){
            $domain = escapeshellarg($this->in['domain']);
            $select = escapeshellarg($this->in['select']);
            $keylen = escapeshellarg($this->in['keylen']);
            util::exe('dkim add ' . $domain . ' ' . $select . ' ' . $keylen);
        }
        util::redirect( $this->cfg['self'] . '?o=' . $this->g->in['o'] . '&m=list');
    }

    public function read() : string
    {
elog(__METHOD__);

        $domain = explode('._domainkey.', $this->in['dnstxt'])[1]; // too fragile?
        $domain_esc = escapeshellarg($domain);
        exec("sudo dkim show $domain_esc 2>&1", $retArr, $retVal);
        $buf = '
        <b>' . $retArr[0] . '</b><br>
        <div style="word-break:break-all;font-family:monospace;width:100%;">' . $retArr[1] . '</div>';
        return $this->t->read(['buf' => $buf, 'domain' => $domain]);
    }

    public function update() : string
    {
elog(__METHOD__);

        //return $this->list(); // override parent update()
        util::redirect( $this->cfg['self'] . '?o=' . $this->g->in['o'] . '&m=list');
    }

    public function delete() : string
    {
elog(__METHOD__);

        if (util::is_post()){
            $domain = escapeshellarg($this->in['domain']);
            util::exe('dkim del ' . $domain);
        }
        util::redirect( $this->cfg['self'] . '?o=' . $this->g->in['o'] . '&m=list');
    }

    public function list() : string
    {
elog(__METHOD__);

        $buf = '<p style="columns:350px 3;column-rule: 1px dotted #ddd;text-align:center;">';
        exec("sudo dkim list 2>&1", $retArr, $retVal);
        foreach($retArr as $line) $buf .= '
            <a href="?o=dkim&m=read&dnstxt=' . $line . '"><b>' . $line . '</b></a>';
        return $this->t->list(['buf' => $buf . '</p>']);
    }
}

?>
