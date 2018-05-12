<?php
// lib/php/plugins/vhosts.php 20180512
// Copyright (C) 2015-2018 Mark Constable <markc@renta.net> (AGPL-3.0)

class Plugins_Vhosts extends Plugin
{
    protected
    $tbl = 'vhosts',
    $in = [
        'active'    => 0,
        'aid'       => 0,
        'aliases'   => 10,
        'diskquota' => 1000000000,
        'domain'    => '',
        'gid'       => 1000,
        'mailboxes' => 1,
        'mailquota' => 500000000,
        'uid'       => 1000,
        'uname'     => '',
        'plan'      => 'personal',
    ];

    protected function create() : string
    {
error_log(__METHOD__);

        if ($_POST) {
            extract($this->in);
            $active = $active ? 1 : 0;

            if (file_exists('/home/u/' . $domain)) {
                util::log('/home/u/' . $domain . ' already exists', 'warning');
                $_POST = []; return $this->t->create($this->in);
            }

            if (!filter_var(gethostbyname($domain . '.'), FILTER_VALIDATE_IP)) {
                util::log("Invalid domain name: gethostbyname($domain)");
                $_POST = []; return $this->t->create($this->in);
            }

//            if ($mailquota > $diskquota) {
//                util::log('Mailbox quota exceeds domain disk quota');
//                $_POST = []; return $this->t->create($this->in);
//            }

            $num_results = db::read('COUNT(id)', 'domain', $domain, '', 'col');

            if ($num_results != 0) {
                util::log('Domain already exists');
                $_POST = []; return $this->t->create($this->in);
            }

            $plan_esc = trim(escapeshellarg($plan), "'");
            $domain_esc = trim(escapeshellarg($domain), "'");
            shell_exec("nohup sh -c 'sudo addvhost $domain_esc $plan_esc' > /tmp/addvhost.log 2>&1 &");
            util::log('Added ' . $domain . ', please wait another few minutes for the setup to complete', 'success');
            util::redirect($this->g->cfg['self'] . '?o=vhosts');
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
            $diskquota *= 1000000;
            $mailquota *= 1000000;
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
            $vhost_esc = trim(escapeshellarg($vhost), "'");
            shell_exec("nohup sh -c 'sudo delvhost $vhost_esc' > /tmp/delvhost.log 2>&1 &");
            util::log('Removed ' . $vhost, 'success');
            util::redirect($this->g->cfg['self'] . '?o=vhosts');
        }
        return 'Error deleting item';
    }

    protected function list() : string
    {
error_log(__METHOD__);

       if ($this->g->in['x'] === 'json') {
            $columns = [
                ['dt' => 0,  'db' => 'domain',      'formatter' => function($d) { return "<b>$d</b>"; }],
                ['dt' => 1,  'db' => 'num_aliases'],
                ['dt' => 2,  'db' => null,          'formatter' => function($d) { return '/'; } ],
                ['dt' => 3,  'db' => 'aliases'],
                ['dt' => 4,  'db' => 'num_mailboxes'],
                ['dt' => 5,  'db' => null,          'formatter' => function($d) { return '/'; } ],
                ['dt' => 6,  'db' => 'mailboxes'],
                ['dt' => 7,  'db' => 'size_mpath',  'formatter' => function($d) { return util::numfmt(intval($d)); }],
                ['dt' => 8,  'db' => null,          'formatter' => function($d) { return '/'; } ],
                ['dt' => 9,  'db' => 'mailquota',   'formatter' => function($d) { return util::numfmt(intval($d)); }],
                ['dt' => 10, 'db' => 'size_upath',  'formatter' => function($d) { return util::numfmt(intval($d)); }],
                ['dt' => 11, 'db' => null,          'formatter' => function($d) { return '/'; } ],
                ['dt' => 12, 'db' => 'diskquota',   'formatter' => function($d) { return util::numfmt(intval($d)); }],
                ['dt' => 13, 'db' => 'active',      'formatter' => function($d, $row) {
                    $active_buf = $d
                        ? '<i class="fas fa-check text-success"></i>'
                        : '<i class="fas fa-times text-danger"></i>';
                    return $active_buf . '
                    <a class="editlink" href="?o=vhosts&m=update&i=' . $row['id'] . '" title="Update entry for ' . $row['domain'] . '">
                      <i class="fas fa-edit fa-fw cursor-pointer"></i></a>
                    <a href="?o=vhosts&m=delete&i=' . $row['id'] . '" title="Remove Vhost" onClick="javascript: return confirm(\'Are you sure you want to remove: ' . $row['domain'] . '?\')">
                      <i class="fas fa-trash fa-fw cursor-pointer text-danger"></i></a>';
                }],
                ['dt' => 14, 'db' => 'id'],
                ['dt' => 15, 'db' => 'updated'],
            ];
            return json_encode(db::simple($_GET, 'vhosts_view', 'id', $columns), JSON_PRETTY_PRINT);
        }
        return $this->t->list([]);
    }
}

?>
