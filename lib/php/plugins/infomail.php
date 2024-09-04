<?php

declare(strict_types=1);

// lib/php/plugins/mail/infomail.php 20170225 - 20240904
// Copyright (C) 2015-2024 Mark Constable <markc@renta.net> (AGPL-3.0)

class Plugins_InfoMail extends Plugin
{
    private const PFLOG = '/tmp/pflogsumm.log';

    protected function list(): string
    {
        $isReadable = is_readable(self::PFLOG);
        
        return $this->g->t->list([
            'mailq' => shell_exec('mailq') ?: '',
            'pflogs' => $isReadable ? file_get_contents(self::PFLOG) : 'none',
            'pflog_time' => $isReadable 
                ? round(abs(time() - filemtime(self::PFLOG)) / 60) . ' min.'
                : '0 min.',
        ]);
    }

    protected function pflog_renew(): string
    {
        shell_exec('sudo pflogs');
        return $this->list();
    }
}
