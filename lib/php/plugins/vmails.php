<?php
// lib/php/plugins/vmails.php 20180321
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
        'quota'     => 1000000000,
        'passwd1'   => '',
        'passwd2'   => '',
        'password'  => '',
        'uid'       => 1000,
        'user'      => '',
        'spam'      => 1,
    ];

    function create() : string
    {
error_log(__METHOD__);

        if ($_POST) {
//            $this->in['quota'] *= 1048576;
            $this->in['quota'] *= 1000000;
            extract($this->in);
            $retArr = []; $retVal = null;
            exec("sudo addvmail $user $spam 2>&1", $retArr, $retVal);
            util::log('<pre>'.implode("\n", $retArr).'</pre>', $retVal ? 'danger' : 'success');
            util::ses('p', '', '1');
            return $this->list();
        }
        return $this->t->create($this->in);
    }

    protected function read() : string
    {
error_log(__METHOD__);

        return $this->t->update(db::read('*', 'id', $this->g->in['i'], '', 'one'));
    }

    function update() : string
    {
error_log(__METHOD__);

        if ($_POST) {
            extract($this->in);
//            $quota *= 1048576;
            $quota *= 1000000;
            $active = $active ? 1 : 0;

            if (!filter_var($user, FILTER_VALIDATE_EMAIL)) {
                util::log('Email address is invalid');
                $_POST = []; return $this->read();
            }

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
        `spam`      = :spam,
        `quota`     = :quota,
        `updated`   = :updated
  WHERE `id` = :id";

            $res = db::qry($sql, [
                'id'      => $this->g->in['i'],
                'active'  => $active,
                'spam'    => $spam,
                'quota'   => $quota,
                'updated' => date('Y-m-d H:i:s'),
            ]);

            util::log('Mailbox details for ' . $user . ' have been saved', 'success');
            util::ses('p', '', '1');
            return $this->list();
        } elseif ($this->g->in['i']) {
            return $this->read();
        } else return 'Error updating item';
    }

    function delete() : string
    {
error_log(__METHOD__);

        if ($this->g->in['i']) {
            $user = db::read('user', 'id', $this->g->in['i'], '', 'col');
            if ($user) {
                $retArr = []; $retVal = null;
                exec("sudo delvmail $user 2>&1", $retArr, $retVal);
                util::log('<pre>'.implode("\n", $retArr).'</pre>', $retVal ? 'danger' : 'success');
            } else {
                util::log('ERROR: user does not exist');
            }
        }
        util::ses('p', '', '1');
        return $this->list();
    }
}

?>
