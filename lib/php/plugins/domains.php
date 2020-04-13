<?php
// lib/php/plugins/domains.php 20150101 - 20190320
// Copyright (C) 2015-2019 Mark Constable <markc@renta.net> (AGPL-3.0)

class Plugins_Domains extends Plugin
{
    protected
    $dbh = null,
    $tbl = 'domains',
    $in = [
        'name'          => '',
        'master'        => '',
        'last_check'    => '',
        'disabled'      => 0,
        'type'          => '',
        'notified_serial' => '',
        'account'       => '',
        'increment'     => 0,
        'ip'            => '',
        'ns1'           => '',
        'ns2'           => '',
    ];

    public function __construct(Theme $t)
    {
elog(__METHOD__);

        if ($t->g->dns['db']['type'])
            $this->dbh = new db($t->g->dns['db']);
        parent::__construct($t);
    }

    protected function create() : string
    {
elog(__METHOD__);

        if (util::is_post()) {
            extract($_POST);

//            $err = var_export($_POST, true);
//util::log('<pre>'.$err.'</pre>');
//            $cms = ($cms === 'on') ? 'wp' : 'none';
//            $ssl = ($ssl === 'on') ? 'self' : 'le';
//            $vhost = $uuser ? $uuser . '@' . $domain : $domain;
// wtf, we are not creating a new vhost!!!
//            shell_exec("nohup sh -c 'sudo addvhost $vhost $cms $ssl $ip' > /tmp/addvhost.log 2>&1 &");
//            util::log('Added ' . $domain . ', please wait another few minutes for the setup to complete', 'success');
//            util::redirect($this->g->cfg['self'] . '?o=vhosts');

// Usage: addpdns domain ip ns1 ns2 [mx] [spfip] [sshkey]

            util::exe("addpdns $domain $ip $ns1 $ns2 $mxhost $spfip");
            return $this->t->create($this->in);
        }
    }

    protected function create2() : string
    {
elog(__METHOD__);

        if (util::is_post()) {
            extract($_POST);
            extract($this->g->dns);
            $created = date('Y-m-d H:i:s');
            $disable = 0;
            $soa_buf =
              $soa['primary'] . $domain . ' ' .
              $soa['email'] . $domain . '. ' .
              date('Ymd') . '00' . ' ' .
              $soa['refresh'] . ' ' .
              $soa['retry'] . ' ' .
              $soa['expire'] . ' ' .
              $soa['ttl'];
            $did = db::create([
                'name'    => $domain,
                'master'  => $type === 'SLAVE' ? $master : '',
                'type'    => $type ? $type : 'MASTER',
                'updated' => $created,
                'created' => $created,
            ]);

            if ($type === 'SLAVE') {
                util::log('Created DNS Zone: ' . $domain, 'success');
                util::redirect( $this->cfg['self'] . '?o=' . $this->g->in['o'] . '&m=list');
            }

            $sql = "
 INSERT INTO `records` (
        content, created, disabled, domain_id, name, prio, ttl, type, updated
) VALUES (
        :content, :created, :disabled, :did, :domain, :prio, :ttl, :type, :updated
)";
            db::qry($sql, [
                'content' => $soa_buf,
                'created' => $created,
                'did'     => $did,
                'disabled'=> $disable,
                'domain'  => $domain,
                'prio'    => $prio,
                'ttl'     => $ttl,
                'type'    => 'SOA',
                'updated' => $created,
            ]);
            db::qry($sql, [
                'content' => $ns1 . $domain,
                'created' => $created,
                'did'     => $did,
                'disabled'=> $disable,
                'domain'  => $domain,
                'prio'    => $prio,
                'ttl'     => $ttl,
                'type'    => 'NS',
                'updated' => $created,
            ]);
            db::qry($sql, [
                'content' => $ns2 . $domain,
                'created' => $created,
                'did'     => $did,
                'disabled'=> $disable,
                'domain'  => $domain,
                'prio'    => $prio,
                'ttl'     => $ttl,
                'type'    => 'NS',
                'updated' => $created,
            ]);
            db::qry($sql, [
                'content' => $a,
                'created' => $created,
                'did'     => $did,
                'disabled'=> $disable,
                'domain'  => $domain,
                'prio'    => $prio,
                'ttl'     => $ttl,
                'type'    => 'A',
                'updated' => $created,
            ]);
            db::qry($sql, [
                'content' => $a,
                'created' => $created,
                'did'     => $did,
                'disabled'=> $disable,
                'domain'  => 'cdn.' . $domain,
                'prio'    => $prio,
                'ttl'     => $ttl,
                'type'    => 'A',
                'updated' => $created,
            ]);
            db::qry($sql, [
                'content' => $a,
                'created' => $created,
                'did'     => $did,
                'disabled'=> $disable,
                'domain'  => 'www.' . $domain,
                'prio'    => $prio,
                'ttl'     => $ttl,
                'type'    => 'A',
                'updated' => $created,
            ]);
            db::qry($sql, [
                'content' => $mx . $domain,
                'created' => $created,
                'did'     => $did,
                'disabled'=> $disable,
                'domain'  => $domain,
                'prio'    => $prio,
                'ttl'     => $ttl,
                'type'    => 'MX',
                'updated' => $created,
            ]);
            util::log('Created DNS Zone: ' . $domain, 'success');
            util::redirect( $this->cfg['self'] . '?o=' . $this->g->in['o'] . '&m=list');
        }
        return $this->t->create($this->in);
    }

    protected function update() : string
    {
elog(__METHOD__);

        if ($this->in['increment']) {
//            if ($this->in['increment']) {
                $sql = "
 SELECT content as soa
   FROM records
  WHERE type='SOA'
    AND domain_id=:did";

                $oldsoa   = explode(' ', db::qry($sql, ['did' => $this->g->in['i']], 'col'));
                $primary  = $oldsoa[0];
                $email    = $oldsoa[1];
                $serial   = $oldsoa[2];
                $refresh  = $oldsoa[3];
                $retry    = $oldsoa[4];
                $expire   = $oldsoa[5];
                $ttl      = $oldsoa[6];
//            } else {
//                extract($_POST);
//            }

            $today = date('Ymd');
            $serial_day = substr($serial, 0, 8);
            $serial_rev = substr($serial, -2);

            $serial = ($serial_day == $today)
                ? "$today" . sprintf("%02d", $serial_rev + 1)
                : "$today" . "00";

            $soa =
              $primary . ' ' .
              $email . ' ' .
              $serial . ' ' .
              $refresh . ' ' .
              $retry . ' ' .
              $expire . ' ' .
              $ttl;

            $sql = "
 UPDATE records SET
        ttl     = :ttl,
        content = :soa,
        updated = :updated
  WHERE type = 'SOA'
    AND domain_id = :did";

            $res = db::qry($sql, [
                'did' => $this->g->in['i'],
                'soa' => $soa,
                'ttl' => $ttl,
                'updated' => date('Y-m-d H:i:s'),
            ]);

            if ($this->in['increment']) return $serial;

            // TODO check $res ???
            util::log('Updated DNS domain ID ' . $this->g->in['i'], 'success');
            util::redirect( $this->g->cfg['self'] . '?o=' . $this->g->in['o'] . '&m=list');

        } elseif (util::is_post() && $this->g->in['i']) {

            extract($_POST);

            $dom = db::read('name,type,master', 'id', $this->g->in['i'], '', 'one');
            if ($dom['type'] === 'SLAVE') {
                return $this->t->update($dom);
            } else {
                $sql = "
 SELECT content as soa
   FROM records
  WHERE type='SOA'
    AND domain_id=:did";

                $soa = db::qry($sql, ['did' => $this->g->in['i']], 'one');
                return $this->t->update(array_merge($dom, $soa));
            }
        }
        return 'Error updating item';
    }

    protected function delete() : string
    {
elog(__METHOD__);

        if ($this->g->in['i']) {
            $sql = "
 DELETE FROM `records`
  WHERE  domain_id = :id";

            $res1 = db::qry($sql, ['id' => $this->g->in['i']]);
            $res2 = db::delete([['id', '=', $this->g->in['i']]]);
            // TODO check $res1 and $res2 ???
            util::log('Deleted DNS zone ID: ' . $this->g->in['i'], 'success');
            util::redirect( $this->cfg['self'] . '?o=' . $this->g->in['o'] . '&m=list');
        }
        return 'Error deleting item';
    }

    protected function list() : string
    {
elog(__METHOD__);

        if ($this->g->in['x'] === 'json') {
            $columns = [
                ['dt' => 0,   'db' => 'name',       'formatter' => function($d, $row) {
                    return ($row['type'] !== 'SLAVE') ? '
                    <a href="?o=records&m=list&i=' . $row['id'] . '" title="Update Domain SOA">
                      <b>' . $d . '</b></a>' : '<b>' . $d . '</b>';
                }],
                ['dt' => 1,   'db' => 'type'],
                ['dt' => 2,   'db' => 'records'],
                ['dt' => 3,   'db' => 'soa',        'formatter' => function($d, $row) {
                    $soa = explode(' ', $row['soa']);
                    return ($row['type'] !== 'SLAVE') ? '
        <a class="serial" href="?o=domains&m=update&i=' . $row['id'] . '" title="Update Serial">' . $soa[2] . '</a>' : $soa[2];
                }],
                ['dt' => 4,  'db' => 'id', 'formatter' => function($d, $row) {
                    return '
                    <a href="" class="shwho" data-toggle="modal" data-target="#shwhomodal" title="Show Domain Info" data-rowid="' . $d . '" data-rowname="' . $row['name'] . '">
                      <i class="fas fa-info-circle fa-fw cursor-pointer"></i></a>
                    <a href="" class="delete" data-toggle="modal" data-target="#removemodal" title="Remove Domain ID: ' . $d . '" data-rowid="' . $d . '" data-rowname="' . $row['name'] . '">
                      <i class="fas fa-trash fa-fw cursor-pointer text-danger"></i></a>';
                }],
                ['dt' => 5,   'db' => 'updated'],
            ];
            return json_encode(db::simple($_GET, 'domains_view2', 'id', $columns), JSON_PRETTY_PRINT);
        }
        return $this->t->list([]);
    }

    protected function shwho() : string
    {
elog(__METHOD__);

        return shell_exec('sudo shwho ' . $this->in['name']);
    }

    protected function incsoa() : string
    {
elog(__METHOD__);

        return shell_exec('sudo incsoa ' . $this->in['name']);
    }
}

?>
