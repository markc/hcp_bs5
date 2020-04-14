<?php
// lib/php/plugins/vhosts.php 20200414
// Copyright (C) 2015-2020 Mark Constable <markc@renta.net> (AGPL-3.0)

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
        'cms'       => '',
        'ssl'       => '',
        'ip'        => '',
        'uuser'     => '',
    ];

    protected function create() : string
    {
elog(__METHOD__);

        if (util::is_post()) {
            extract($this->in);
//            $active = $active ? 1 : 0;

//            if(!util::is_valid_plan($plan)){
//                util::log('Invalid plan ' . $plan);
//                util::redirect($this->g->cfg['self'] . '?o=vhosts');
//            }

            if (file_exists('/home/u/' . $domain)) {
                util::log('/home/u/' . $domain . ' already exists', 'warning');
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

            $cms = ($cms === 'on') ? 'wp' : 'none';
            $ssl = ($ssl === 'on') ? 'self' : 'le';
            $vhost = $uuser ? $uuser . '@' . $domain : $domain;

            shell_exec("nohup sh -c 'sudo addvhost $vhost $cms $ssl $ip' > /tmp/addvhost.log 2>&1 &");
            util::log('Added ' . $domain . ', please wait another few minutes for the setup to complete', 'success');
            util::redirect($this->g->cfg['self'] . '?o=vhosts');
        }
        return $this->t->create($this->in);
    }

    protected function read() : string
    {
elog(__METHOD__);

        return $this->t->update(db::read('*', 'id', $this->g->in['i'], '', 'one'));
    }

    protected function update() : string
    {
elog(__METHOD__);

        if (util::is_post()) {
            extract($this->in);
            $diskquota *= 1000000;
            $mailquota *= 1000000;
            $active = $active ? 1 : 0;

            $domain = db::read('domain', 'id', $this->g->in['i'], '', 'col');

            if ($mailquota > $diskquota) {
                util::log('Mailbox quota exceeds disk quota');
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
            util::redirect( $this->cfg['self'] . '?o=' . $this->g->in['o'] . '&m=list');
        } elseif ($this->g->in['i']) {
            return $this->read();
        } else return 'Error updating item';
    }

    protected function delete() : string
    {
elog(__METHOD__);

        if (util::is_post() && $this->g->in['i']) {
            $domain = db::read('domain', 'id', $this->g->in['i'], '', 'col');
            if ($domain) {
                shell_exec("nohup sh -c 'sudo delvhost $domain' > /tmp/delvhost.log 2>&1 &");
                util::log('Removed ' . $domain, 'success');
                util::redirect($this->g->cfg['self'] . '?o=vhosts');
            } else util::log('ERROR: domain does not exist');
        }
        return 'Error deleting item';
    }

    protected function list() : string
    {
elog(__METHOD__);

       if ($this->g->in['x'] === 'json') {
            $columns = [
                ['dt' => 0,  'db' => 'domain',      'formatter' => function($d, $row) {
                    return '
                    <a class="editlink" href="?o=vhosts&m=update&i=' . $row['id'] . '" title="Update VHOST">
                      <b>' . $row['domain'] . '</b></a>';
                }],
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
                ['dt' => 13, 'db' => 'active',      'formatter' => function($d) {
                    return '<i class="fas ' . ($d ? 'fa-check text-success' : 'fa-times text-danger') . '"></i>';
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
