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
//            $this->in['diskquota'] *= 1000000;
//            $this->in['mailquota'] *= 1000000;
            extract($this->in);
            $active = $active ? 1 : 0;

//            if (strpos($domain, '@'))
//                list($uname, $domain) = explode('@', $domain);

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

//            $sql = "
// SELECT COUNT(id)
//   FROM `vhosts`
//  WHERE `domain` = :domain";

//            $num_results = db::qry($sql, ['domain' => $domain], 'col');
            $num_results = db::read('COUNT(id)', 'domain', $domain, '', 'col');
//error_log("num_results=$num_results, type=".gettype($num_results));
            if ($num_results != 0) {
                util::log('Domain already exists');
                $_POST = []; return $this->t->create($this->in);
            }
// add plan, default to personal
            shell_exec("nohup sh -c 'sudo addvhost $domain $plan' > /tmp/addvhost.log 2>&1 &");
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
            shell_exec("nohup sh -c 'sudo delvhost $vhost' > /tmp/delvhost.log 2>&1 &");
            util::log('Removed ' . $vhost, 'success');
            util::redirect($this->g->cfg['self'] . '?o=vhosts');
        }
        return 'Error deleting item';
    }

    protected function list() : string
    {
error_log(__METHOD__);

//                 ['dt' => null, 'db' => 'id'],
       if ($this->g->in['x'] === 'json') {
            $columns = [
                ['dt' => 0,  'db' => 'domain'],
                ['dt' => 1,  'db' => 'num_aliases'],
                ['dt' => 3,  'db' => 'aliases'],
                ['dt' => 4,  'db' => 'num_mailboxes'],
                ['dt' => 5,  'db' => null,  'formatter' => function($d) { return '/'; } ],
                ['dt' => 6,  'db' => 'mailboxes'],
                ['dt' => 7,  'db' => 'size_mpath',  'formatter' => function($d) { return util::numfmt($d); }],
                ['dt' => 8,  'db' => null,  'formatter' => function($d) { return '/'; } ],
                ['dt' => 9,  'db' => 'mailquota', 'formatter' => function($d) { return util::numfmt($d); }],
                ['dt' => 10, 'db' => 'size_upath', 'formatter' => function($d) { return util::numfmt($d); }],
                ['dt' => 11, 'db' => null, 'formatter' => function($d) { return '/'; } ],
                ['dt' => 12, 'db' => 'diskquota', 'formatter' => function($d) { return util::numfmt($d); }],
                ['dt' => 13, 'db' => 'active', 'formatter' => function($d, $row) {
                    $active_buf = $d
                        ? '<i class="fas fa-check text-success"></i>'
                        : '<i class="fas fa-times text-danger"></i>';
                    return $active_buf . '
                      <a href="?o=vhosts&m=delete&i=' . $row['id'] . '" title="Remove Vhost" onClick="javascript: return confirm(\'Are you sure you want to remove: ' . $row['domain'] . '?\')">
                      <i class="fas fa-trash fa-fw cursor-pointer text-danger"></i></a>';
                }],
                ['dt' => 2,  'db' => null, 'formatter' => function($d) { return '/'; } ],
                ['dt' => null, 'db' => 'id'],
                ['dt' => null, 'db' => 'updated'],
            ];
/*
            $sql1 = "
 SELECT vh.id,
        vh.domain,
        vh.aliases,
        vh.mailboxes,
        vh.mailquota,
        vh.diskquota,
        vh.active,
        vh.updated,
        vl.size_mpath,
        vl.size_upath,
        count(distinct vm.id) num_mailboxes,
        count(distinct va.id) num_aliases
   FROM vhosts vh
        LEFT JOIN vhost_log vl ON vh.id=vl.hid
            LEFT JOIN vmails vm ON vh.id=vm.hid
                LEFT JOIN valias va ON vh.id=va.hid
  GROUP BY vh.id, vl.size_mpath, vl.size_upath";
*/
            return json_encode(db::simple($_GET, 'vhosts_view', 'id', $columns), JSON_PRETTY_PRINT);
        }
        return $this->t->list([]);
    }

}

?>
