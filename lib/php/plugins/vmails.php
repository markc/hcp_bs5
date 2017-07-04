<?php
// lib/php/plugins/vmails.php 20170228
// Copyright (C) 2015-2017 Mark Constable <markc@renta.net> (AGPL-3.0)

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
        'quota'     => 1073741824,
        'passwd1'   => '',
        'passwd2'   => '',
        'password'  => '',
        'uid'       => 1000,
        'user'      => '',
    ];

    function create() : string
    {
error_log(__METHOD__);

        if ($_POST) {
            $this->in['quota'] *= 1048576;
            extract($this->in);
            $password = 'changeme_N0W';
            list($lhs, $rhs) = explode('@', $user);

            if (!filter_var($user, FILTER_VALIDATE_EMAIL)) {
                util::log('Invalid email address');
                $_POST = []; return $this->t->create($this->in);
            }

            $sql = "
 SELECT `id`, `aid`, `uid`, `gid` FROM `vhosts`
  WHERE `domain` = :rhs";

            $d = db::qry($sql, ['rhs' => $rhs], 'one');

            if (empty($d['id'])) {
                util::log('Domain does not exist');
                $_POST = []; return $this->t->create($this->in);
            }

            if ($passwd1 && $passwd2) {
                if (!util::chkpw($passwd1, $passwd2)) {
                    $_POST = []; return $this->t->create($this->in);
                }
                $password = $passwd1;
            }
            $sql = "
 SELECT 1 FROM `valias`
  WHERE `source` = :catchall";

            $catchall = db::qry($sql, ['catchall' => '@'.$rhs], 'col');
//error_log("catchall=$catchall");

            if ($catchall === 1) {
                $sql = "
 INSERT INTO `valias` (
        `active`,
        `did`,
        `source`,
        `target`,
        `updated`,
        `created`
) VALUES (
        :active,
        :did,
        :source,
        :target,
        :updated,
        :created
)";
                $result = db::qry($sql, [
                    'active'  => $active ? 1 : 0,
                    'did'     => $d['id'],
                    'source'  => $user,
                    'target'  => $user,
                    'updated' => date('Y-m-d H:i:s'),
                    'created' => date('Y-m-d H:i:s')
                ]);
            }

            $sql = "
 INSERT INTO `vmails` (
        aid,
        created,
        did,
        gid,
        home,
        password,
        quota,
        uid,
        updated,
        user
) VALUES (
        :aid,
        :created,
        :did,
        :gid,
        :home,
        :password,
        :quota,
        :uid,
        :updated,
        :user
)";
            $res = db::qry($sql, [
                'aid'       => $d['aid'],
                'created'   => date('Y-m-d H:i:s'),
                'did'       => $d['id'],
                'gid'       => $d['gid'],
                'home'      => '/home/u/' . $rhs . '/home/' . $lhs,
                'password'  => util::mail_password($password),
                'quota'     => $quota,
                'uid'       => $d['uid'],
                'updated'   => date('Y-m-d H:i:s'),
                'user'      => $user,
            ]);
            // test $res ?

            util::log('Created mailbox for ' . $user, 'success');
            shell_exec("nohup sh -c 'sleep 1; sudo addvmail $user' > /tmp/addvmail.log 2>&1 &");
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
            $quota *= 1048576;
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
        `quota`     = :quota,
        `updated`   = :updated
  WHERE `id` = :id";

            $res = db::qry($sql, [
                'id'      => $this->g->in['i'],
                'active'  => $active,
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

            db::qry("DELETE FROM `vmails` WHERE `id` = :id", ['id' => $this->g->in['i']]);
            db::qry("DELETE FROM `valias` WHERE `target` = :user", ['user' => $user]);
            db::qry("DELETE FROM `logging` WHERE `name` = :user", ['user' => $user]);

            util::log('Removed ' . $user, 'success');
            shell_exec("nohup sh -c 'sleep 1; sudo delvmail $user' > /tmp/delvmail.log 2>&1 &");
            util::ses('p', '', '1');
            return $this->list();
        }
        return 'Error deleting item';
    }
}

?>
