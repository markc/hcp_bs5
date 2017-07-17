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

            $vhost = $uname ? "$uname@$domain" : $domain;
            $dtype = $this->g->db['type'];
            $pws = shell_exec("sudo newpw 3");
            $ret = shell_exec("sudo addvhost $vhost $dtype $pws");
            util::redirect($this->g->cfg->self . '?o=vhosts', 5, $ret);
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
            util::redirect($this->g->cfg->self . '?o=vhosts', 5, $ret);
            util::log('Removed ' . $vhost, 'success');
            shell_exec("nohup sh -c 'sudo serva restart web' > /tmp/serva.log 2>&1 &");
            exit;
        }
        return 'Error deleting item';
    }
}

?>
