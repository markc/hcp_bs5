<?php
// lib/php/plugins/vhosts.php 20170225
// Copyright (C) 2015-2017 Mark Constable <markc@renta.net> (AGPL-3.0)

class Plugins_Vhosts extends Plugin
{
    protected
    $tbl = 'vhosts',
    $in = [
        'active'    => 0,
        'aid'       => 0,
        'aliases'   => 10,
        'diskquota' => 2147483648,
        'domain'    => '',
        'gid'       => 1000,
        'mailboxes' => 2,
        'mailquota' => 1073741824,
        'uid'       => 1000,
        'uname'     => '',
    ];

    protected function create() : string
    {
error_log(__METHOD__);

        if ($_POST) {
            $this->in['diskquota'] *= 1048576;
            $this->in['mailquota'] *= 1048576;
            extract($this->in);
            $active = $active ? 1 : 0;

            if (strpos($domain, '@'))
                list($uname, $domain) = explode('@', $domain);

            if (file_exists('/home/u/' . $domain)) {
                util::log('/home/u/' . $domain . ' already exists', 'warning');
//                $_POST = []; return $this->t->create($this->in);
            }

            if (!filter_var(gethostbyname($domain . '.'), FILTER_VALIDATE_IP)) {
                util::log('Invalid domain name');
                $_POST = []; return $this->t->create($this->in);
            }

            if ($mailquota > $diskquota) {
                util::log('Mailbox quota exceeds domain disk quota');
                $_POST = []; return $this->t->create($this->in);
            }

            $sql = "
 SELECT `domain` FROM `vhosts`
  WHERE `domain` = :domain";

            $num_results = db::qry($sql, ['domain' => $domain], 'one');

            if ($num_results != 0) {
                util::log('Domain already exists');
                $_POST = []; return $this->t->create($this->in);
            }

            $sql = "
 INSERT INTO `vhosts` (
        `active`,
        `aliases`,
        `created`,
        `diskquota`,
        `domain`,
        `gid`,
        `mailboxes`,
        `mailquota`,
        `uid`,
        `uname`,
        `updated`
) VALUES (
        :active,
        :aliases,
        :created,
        :diskquota,
        :domain,
        :gid,
        :mailboxes,
        :mailquota,
        :uid,
        :uname,
        :updated
)";
            $res = db::qry($sql, [
                'active'      => (int) $active,
                'aliases'     => (int) $aliases,
                'created'     => date('Y-m-d H:i:s'),
                'diskquota'   => (int) $diskquota,
                'domain'      => $domain,
                'gid'         => $gid,    // updated via addvhost
                'mailboxes'   => (int) $mailboxes,
                'mailquota'   => (int) $mailquota,
                'uid'         => $uid,    // updated via addvhost
                'uname'       => $uname,  // updated via addvhost
                'updated'     => date('Y-m-d H:i:s'),
            ]);
            // test $res ?
            $lid = db::$dbh->lastInsertId(); // ?

            $sql = "
 INSERT INTO `vmails` (did, uid, gid, user, home, password, updated, created)
 VALUES (:did, :uid, :gid, :user, :home, :password, :updated, :created)";

            $res = db::qry($sql, [
                'did'         => $lid,
                'uid'         => 1000,
                'gid'         => 1000,
                'user'        => 'admin@' . $domain,
                'home'        => '/home/u/' . $domain . '/home/admin',
                'password'    => util::mail_password('changeme_N0W'),
                'updated'     => date('Y-m-d H:i:s'),
                'created'     => date('Y-m-d H:i:s'),
            ]);

            $sql = "
 INSERT INTO `valias` (did, source, target, updated, created)
 VALUES (:did, :source, :target, :updated, :created)";

            $res = db::qry($sql, [
                'did'         => $lid,
                'source'      => 'abuse@' . $domain,
                'target'      => 'admin@' . $domain,
                'updated'     => date('Y-m-d H:i:s'),
                'created'     => date('Y-m-d H:i:s'),
            ]);

            $res = db::qry($sql, [
                'did'         => $lid,
                'source'      => 'postmaster@' . $domain,
                'target'      => 'admin@' . $domain,
                'updated'     => date('Y-m-d H:i:s'),
                'created'     => date('Y-m-d H:i:s'),
            ]);

            $res = db::qry($sql, [
                'did'         => $lid,
                'source'      => 'webmaster@' . $domain,
                'target'      => 'admin@' . $domain,
                'updated'     => date('Y-m-d H:i:s'),
                'created'     => date('Y-m-d H:i:s'),
            ]);

            $sql = "
 INSERT INTO `logging` (did, name, month)
 VALUES (:did, :name, :month)";

            $res = db::qry($sql, [
                'did'         => $lid,
                'name'        => $domain,
                'month'       => date('Ym'),
            ]);

            $res = db::qry($sql, [
                'did'         => $lid,
                'name'        => 'admin@' . $domain,
                'month'       => date('Ym'),
            ]);

            $vhost = $uname ? "$uname@$domain" : $domain;
            $dtype = $this->g->db['type'];
            $ret = shell_exec("sudo addvhost $vhost $dtype");
            util::redirect($this->g->self . '?o=vhosts', 5, $ret);
            util::log('Created ' . $vhost, 'success');
            shell_exec("nohup sh -c 'sudo serva restart web' > /tmp/serva.log 2>&1 &");
            exit;
        }
        return $this->t->create($this->in);
    }

    protected function read() : string
    {
error_log(__METHOD__);

        return $this->t->update(db::read('*', 'id', $this->g->in['i'], '', 'one'));
    }

    protected function update() : string
    {
error_log(__METHOD__);

        if ($_POST) {
            extract($this->in);
            $diskquota *= 1048576;
            $mailquota *= 1048576;
            $active = $active ? 1 : 0;

            if ($mailquota > $diskquota) {
                util::log('Mailbox quota exceeds disk quota');
                $_POST = []; return $this->read();
            }

            $size_upath = db::qry("
 SELECT size_upath
   FROM logging
  WHERE name = :name", ['name' => $domain], 'col');

//            if ($mailquota < $size_upath) {
//                util::log('Mailbox quota must be greater than current used diskspace of ' . util::numfmt($size_upath));
//                $_POST = []; return $this->read();
//            }

            if (!filter_var(gethostbyname($domain . '.'), FILTER_VALIDATE_IP)) {
                util::log('Domain name is invalid');
                $_POST = []; return $this->read();
            }

            $sql = "
 UPDATE `vhosts` SET
        `active`    = :active,
        `aliases`   = :aliases,
        `diskquota` = :diskquota,
        `domain`    = :domain,
        `mailboxes` = :mailboxes,
        `mailquota` = :mailquota,
        `updated`   = :updated
  WHERE `id` = :id";

            $res = db::qry($sql, [
                'id'        => $this->g->in['i'],
                'active'    => $active,
                'aliases'   => $aliases,
                'diskquota' => $diskquota,
                'domain'    => $domain,
                'mailboxes' => $mailboxes,
                'mailquota' => $mailquota,
                'updated'   => date('Y-m-d H:i:s'),
            ]);
            // test $res ?

            util::log('Vhost ID ' . $this->g->in['i'] . ' updated', 'success');
            util::ses('p', '', '1');
            return $this->list();
        } elseif ($this->g->in['i']) {
            return $this->read();
        } else return 'Error updating item';
    }

    protected function delete() : string
    {
error_log(__METHOD__);

        if ($this->g->in['i']) {

            $vhost = db::read('domain', 'id', $this->g->in['i'], '', 'col');

            db::qry("DELETE FROM `vhosts` WHERE `id` = :did", ['did' => $this->g->in['i']]);
            db::qry("DELETE FROM `valias` WHERE `did` = :did", ['did' => $this->g->in['i']]);
            db::qry("DELETE FROM `vmails` WHERE `did` = :did", ['did' => $this->g->in['i']]);
            db::qry("DELETE FROM `logging` WHERE `did` = :did", ['did' => $this->g->in['i']]);

            $ret = shell_exec("sudo delvhost $vhost " . $this->g->db['type']);
            util::redirect($this->g->self . '?o=vhosts', 5, $ret);
            util::log('Removed ' . $vhost, 'success');
            shell_exec("nohup sh -c 'sudo serva restart web' > /tmp/serva.log 2>&1 &");
            exit;
        }
        return 'Error deleting item';
    }
}

?>
