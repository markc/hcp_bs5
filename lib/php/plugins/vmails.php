<?php

declare(strict_types=1);
// lib/php/plugins/vmails.php 20180826 - 20200414
// Copyright (C) 2015-2020 Mark Constable <markc@renta.net> (AGPL-3.0)

class Plugins_Vmails extends Plugin
{
    protected string $tbl = 'vmails';
    protected array $in = [
        'newpw' => 0,
        'password' => '',
        'shpw' => 0,
        'user' => '',
    ];

    protected function create(): string
    {
        if (util::is_post()) {
            if (!filter_var($this->in['user'], FILTER_VALIDATE_EMAIL)) {
                util::log('Email address (' . $this->in['user'] . ') is invalid');
                $_POST = [];

                return $this->read();
            }
            util::exe('addvmail ' . $this->in['user']);
        }
        util::relist();
    }

    protected function update(): string
    {
        extract($this->in);

        if ($shpw) {
            return util::run("shpw {$user}");
        }
        if ($newpw) {
            return util::run('newpw');
        }
        if (util::is_post()) {
            $password = html_entity_decode($password, ENT_QUOTES, 'UTF-8');
            if (util::chkpw($password)) {
                util::exe("chpw {$user} '{$password}'");
            }
        }
        util::relist();
    }

    protected function delete(): void
    {
        if (util::is_post()) {
            util::exe('delvmail ' . $this->in['user']);
        }
        util::relist();
    }

    protected function list(): string
    {
        if ('json' === $this->g->in['x']) {
            $columns = [
                ['dt' => null, 'db' => 'id'],
                ['dt' => 0, 'db' => 'user', 'formatter' => function ($d, $row) {
                    return '
                    <a href="" title="Change password for ' . $d . '" data-id="' . $row['id'] . '" data-user="' . $d . '" data-toggle="modal" data-target="#updatemodal">
                      <b>' . $d . ' </b></a>';
                }],
                ['dt' => 1, 'db' => 'size_mail', 'formatter' => fn ($d) => util::numfmt(intval($d))],
                ['dt' => 2, 'db' => 'num_total', 'formatter' => fn ($d) => number_format(intval($d))],
                ['dt' => 3, 'db' => null, 'formatter' => function ($d, $row) {
                    return '<a href="" title="Remove this Mailbox" data-removeuser="' . $row['user'] . '" data-toggle="modal" data-target="#removemodal">
                    <small><i class="fas fa-trash fa-fw cursor-pointer text-danger"></i></small>
                  </a>';
                }],
                ['dt' => 4, 'db' => 'updated'],
            ];

            return json_encode(db::simple($_GET, 'vmails_view', 'id', $columns), JSON_PRETTY_PRINT);
        }

        return $this->g->t->list([]);
    }
}
