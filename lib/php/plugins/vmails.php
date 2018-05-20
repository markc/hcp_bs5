<?php
// lib/php/plugins/vmails.php 20180530
// Copyright (C) 2015-2018 Mark Constable <markc@renta.net> (AGPL-3.0)

class Plugins_Vmails extends Plugin
{
    protected
    $tbl = 'vmails',
    $in = [
        'active'    => 0,
        'aid'       => 1,
        'did'       => 1,
        'gid'       => 1000,
        'home'      => '',
        'passwd1'   => '',
        'passwd2'   => '',
        'password'  => '',
        'quota'     => 1000000000,
        'spamf'     => 0,
        'uid'       => 1000,
        'user'      => '',
    ];

    protected function create() : string
    {
error_log(__METHOD__);

        if (util::is_post())
            util::exe('addvmail ' . $this->in['user'] . ($this->in['spamf'] ? '' : ' nospam'));
        return $this->list();
    }

    protected function read() : string
    {
error_log(__METHOD__);

        return $this->t->update(db::read('*', 'id', $this->g->in['i'], '', 'one'));
    }

    protected function update() : string
    {
error_log(__METHOD__);

        if (util::is_post()) {
            extract($this->in);
            $quota *= 1000000;
            $active = $active ? 1 : 0;
            $spamf  = $spamf ? 1 : 0;

//            if (!filter_var($user, FILTER_VALIDATE_EMAIL)) {
//                util::log('Email address is invalid');
//                $_POST = []; return $this->read();
//            }

            if ($passwd1 && $passwd2) {
                if (!util::chkpw($passwd1, $passwd2)) {
                    $_POST = []; return $this->read();
                }

                $sql = "
 UPDATE `vmails` SET
        `password`  = :password,
        `updated`   = :updated
  WHERE `id` = :id";

                $res = db::qry($sql, [
                    'id'        => $this->g->in['i'],
                    'updated'   => date('Y-m-d H:i:s'),
                    'password'  => util::mail_password($passwd1),
                ]);
            }

            $sql = "
 UPDATE `vmails` SET
        `active`    = :active,
        `quota`     = :quota,
        `updated`   = :updated
  WHERE `id` = :id";

            $res = db::qry($sql, [
                'id'      => $this->g->in['i'],
                'active'  => $active,
                'quota'   => $quota,
                'updated' => date('Y-m-d H:i:s'),
            ]);

            $spamf_old = db::read('spamf', 'id', $this->g->in['i'], '', 'col');
            $spamf_buf = '';

            if ($spamf_old !== $spamf) {
                $user_esc = trim(escapeshellarg($user), "'");
                $spamf_str = ($spamf === 1) ? 'on' : 'off';
                exec("sudo spamf $user_esc $spamf_str 2>&1", $retArr, $retVal);
                $spamf_buf = trim(implode("\n", $retArr));
                $spamf_buf = $spamf_buf ? '<pre>' . $spamf_buf . '</pre>' : '';
            }
            util::log($spamf_buf . 'Mailbox details for ' . $user . ' have been saved', 'success');
            return $this->list();
//        } elseif ($this->g->in['i']) {
//            return $this->read();
        }
        return 'Error updating item';
    }

    protected function delete() : string
    {
error_log(__METHOD__);

        if (util::is_post() && $this->g->in['i']) {
            $user = db::read('user', 'id', $this->g->in['i'], '', 'col');
            if ($user) util::exe("delvmail $user");
            else util::log('ERROR: user does not exist');
        }
        return $this->list();
    }

    protected function list() : string
    {
error_log(__METHOD__);

        if ($this->g->in['x'] === 'json') {
            $columns = [
                ['dt' => null, 'db' => 'id'],
                ['dt' => 0, 'db' => 'user',       'formatter' => function($d, $row) {
                    return '
                    <a href="?o=vmails&m=read&i=' . $row['id'] . '" title="Update entry for ' . $d . '">
                      <b>' . $d . ' </b></a>';
                }],
                ['dt' => 1, 'db' => 'domain'],
                ['dt' => 2, 'db' => '',           'formatter' => function($d, $row) {
                    $pcnt = round(($row['size_mail'] / $row['quota']) * 100);
                    $pbuf = $pcnt > 9 ? $pcnt.'%' : '';
                    $pbar = $pcnt >= 90 ? 'bg-danger' : ($pcnt >= 75 ? 'bg-warning' : '');
                    return '
                      <div class="progress">
                        <div class="progress-bar ' . $pbar . '" role="progressbar" aria-valuenow="' . $pcnt . '" aria-valuemin="0" aria-valuemax="100" style="width: ' . $pcnt . '%;">
                          ' . $pbuf . '
                        </div>
                      </div>';
                }],
                ['dt' => 3, 'db' => 'size_mail',  'formatter' => function($d) { return util::numfmt(intval($d)); }],
                ['dt' => 4, 'db' => null,         'formatter' => function($d) { return '/'; }],
                ['dt' => 5, 'db' => 'quota',      'formatter' => function($d) { return util::numfmt(intval($d)); }],
                ['dt' => 6, 'db' => 'num_total',  'formatter' => function($d) { return number_format(intval($d)); }],
                ['dt' => 7, 'db' => 'active',     'formatter' => function($d) {
                    return '<i class="fas ' . ($d ? 'fa-check text-success' : 'fa-times text-danger') . '"></i>';
                }],
                ['dt' => 8, 'db' => 'updated'],
            ];
            return json_encode(db::simple($_GET, 'vmails_view', 'id', $columns), JSON_PRETTY_PRINT);
        }
        return $this->t->list([]);
    }
}

?>
