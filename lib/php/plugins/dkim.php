<?php

declare(strict_types=1);

// lib/php/plugins/dkim.php 20180511 - 20240904
// Copyright (C) 2015-2024 Mark Constable <markc@renta.net> (AGPL-3.0)

class Plugins_Dkim extends Plugin
{
    public array $inp = [
        'dnstxt' => '',
        'domain' => '',
        'keylen' => '2048',
        'select' => 'mail',
    ];

    public function create(): string
    {
        if (util::is_post()) {
            $domain = escapeshellarg($this->inp['domain']);
            $select = escapeshellarg($this->inp['select']);
            $keylen = escapeshellarg($this->inp['keylen']);
            util::exe("dkim add $domain $select $keylen");
        }
        util::redirect("{$this->g->cfg['self']}?o={$this->g->in['o']}&m=list");
        return 'Redirect';
    }

    public function read(): string
    {
        $domain = explode('._domainkey.', $this->inp['dnstxt'])[1] ?? '';
        $domain_esc = escapeshellarg($domain);
        exec("sudo dkim show {$domain_esc} 2>&1", $retArr, $retVal);
        $buf = "<b>{$retArr[0]}</b><br><div style=\"word-break:break-all;font-family:monospace;width:100%;\">{$retArr[1]}</div>";
        return $this->g->t->read(['buf' => $buf, 'domain' => $domain]);
    }

    public function update(): string
    {
        util::redirect("{$this->g->cfg['self']}?o={$this->g->in['o']}&m=list");
        return "Update";
    }

    public function delete(): string
    {
        if (util::is_post()) {
            $domain = escapeshellarg($this->inp['domain']);
            util::exe("dkim del $domain");
        }
        util::redirect("{$this->g->cfg['self']}?o={$this->g->in['o']}&m=list");
        return '';
    }

    public function list(): string
    {
        $buf = '<p style="columns:350px 3;column-rule: 1px dotted #ddd;text-align:center;">';
        exec('sudo dkim list 2>&1', $retArr, $retVal);
        foreach ($retArr as $line) {
            $buf .= match ($retVal) {
                0 => "<a href=\"?o=dkim&m=read&dnstxt=$line\"><b>$line</b></a>",
                default => "<b>$line</b><br>",
            };
        }
        return $this->g->t->list(['buf' => $buf . '</p>']);
    }
}
