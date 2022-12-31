<?php

declare(strict_types=1);
// lib/php/plugins/mail/infomail.php 20170225 - 20170514
// Copyright (C) 2015-2017 Mark Constable <markc@renta.net> (AGPL-3.0)

class Plugins_InfoMail extends Plugin
{
    private const PFLOG = '/tmp/pflogsumm.log';

    protected function list(): string
    {
        elog(__METHOD__);

        return $this->t->list([
            'mailq' => shell_exec('mailq'),
            'pflogs' => is_readable(self::PFLOG)
                ? file_get_contents(self::PFLOG)
                : 'none',
            'pflog_time' => is_readable(self::PFLOG)
                ? round(abs(date('U') - filemtime(self::PFLOG)) / 60, 0).' min.'
                : '0 min.',
        ]);
    }

    protected function pflog_renew() : string
    {
        elog(__METHOD__);

        shell_exec('sudo pflogs');
        return $this->list();
    }
}
