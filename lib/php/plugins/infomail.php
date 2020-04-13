<?php
// lib/php/plugins/mail/infomail.php 20170225 - 20170514
// Copyright (C) 2015-2017 Mark Constable <markc@renta.net> (AGPL-3.0)

class Plugins_InfoMail extends Plugin
{
    protected $pflog = '/tmp/pflogsumm.log';

    public function list() : string
    {
elog(__METHOD__);

        return $this->t->list([
            'mailq' => shell_exec('mailq'),
            'pflogs' => is_readable($this->pflog)
                ? file_get_contents($this->pflog)
                : 'none',
            'pflog_time' => is_readable($this->pflog)
                ? round(abs(date('U') - filemtime($this->pflog)) / 60, 0) . ' min.'
                : '0 min.',
        ]);
    }

    public function pflog_renew()
    {
elog(__METHOD__);

        $this->pflogs = shell_exec('sudo pflogs');
        return $this->list();
    }
}

?>
