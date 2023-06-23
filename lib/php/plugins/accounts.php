<?php

declare(strict_types=1);
// lib/php/plugins/users.php 20150101 - 20200414
// Copyright (C) 2015-2020 Mark Constable <markc@renta.net> (AGPL-3.0)

class Plugins_Accounts extends Plugin
{
    protected string $tbl = 'accounts';

    // get/post input parameters
    public array $inp = [
        'grp'       => 1,
        'acl'       => 2,
        'vhosts'    => 1,
        'login'     => '',
        'fname'     => '',
        'lname'     => '',
        'altemail'  => '',
    ];

    protected function read(): string
    {
        return $this->g->t->read(db::read('*', 'id', $this->g->in['i'], '', 'one'));

        //return $this->list(); // this might work?
    }

    protected function list(): string
    {
        if ('json' === $this->g->in['x']) {
            $columns = [
                ['dt' => null, 'db' => 'id'],
                ['dt' => 0, 'db' => 'login', 'formatter' => function ($d, $row) {
                    return '
                    <b><a class="bslink" href="?o=accounts&m=read&i=' . $row['id'] . '">' . $d . '</a></b>';
                }],
                ['dt' => 1, 'db' => 'fname'],
                ['dt' => 2, 'db' => 'lname'],
                ['dt' => 3, 'db' => 'altemail'],
                ['dt' => 4, 'db' => 'acl', 'formatter' => fn ($d) => $this->g->acl[$d]],
                ['dt' => 5, 'db' => 'grp'],
            ];

            return json_encode(db::simple($_GET, 'accounts', 'id', $columns), JSON_PRETTY_PRINT);
        }

        return $this->g->t->list($this->inp);
    }

    protected function switch_user(): void
    {
        if (util::is_adm() and !is_null($this->g->in['i'])) {
            $_SESSION['usr'] = db::read('id,acl,grp,login,fname,lname,webpw,cookie', 'id', $this->g->in['i'], '', 'one');
            util::log('Switch to user: ' . $_SESSION['usr']['login'], 'success');
        } else {
            util::log('Not authorized to switch users');
        }
        util::relist();
    }
}
