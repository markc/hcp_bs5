<?php
// lib/php/plugins/dkim.php 20180511 - 20180515
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

        if (util::is_post()) {
error_log('this->in='.var_export($this->in,true));

            extract($this->in);
            exec("sudo dkim add $domain $select $keylen 2>&1", $retArr, $retVal);
            util::log('<pre>' . trim(implode("\n", $retArr)) . '</pre>', $retVal ? 'danger' : 'success');
        }
        return $this->list();
    }

     public function read() : string
    {
error_log(__METHOD__);

error_log(var_export($this->in, true));

        $buf = '';
        [$select, $domain] = explode('._domainkey.', $this->in['dnstxt']);
error_log('select='.$select.', domain='.$domain);

        exec("sudo dkim show $domain 2>&1", $retArr, $retVal);
        $buf .= '
        <b>' . $retArr[0] . '</b>
        <a href="?o=dkim&m=delete&dnstxt=' . $retArr[0] . '" title="Remove DKIM" onClick="javascript: return confirm(\'Are you sure you want to remove: ' . $retArr[0] . '?\')">
          <i class="fas fa-trash fa-fw cursor-pointer text-danger"></i>
        </a><br>
        <div style="word-break:break-all;font-family:monospace;width:100%;">' . $retArr[1] . '</div>';
        return $this->t->list(['buf' => $buf]);
    }

    public function list() : string
    {
error_log(__METHOD__);

        $buf = '';
        exec("sudo dkim show 2>&1", $retArr, $retVal);
        $cnt = count($retArr);
        for($i = 0; $i < $cnt; $i++) {
            $buf .= ($i % 2 == 0) ? '
        <a href="?o=dkim&m=read&dnstxt=' . $retArr[$i] . '">' . $retArr[$i] . '</a><br>' : '
        <div style="word-break:break-all;font-family:monospace;width:100%;">' . $retArr[$i] . '</div><hr>';
        }
        return $this->t->list(['buf' => $buf]);
    }
}

?>
