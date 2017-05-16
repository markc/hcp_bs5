<?php
// lib/php/plugins/domains.php 20150101 - 20170423
// Copyright (C) 2015-2017 Mark Constable <markc@renta.net> (AGPL-3.0)

class Plugins_Domains extends Plugin
{
    protected
    $tbl = 'domains', // dnszones ?
    $in = [
        'name'        => '',
        'master'      => '',
        'last_check'  => '',
        'disabled'    => 0,
        'type'        => '',
        'notified_serial' => '',
        'account'     => '',
    ];

    protected function create() : string
    {
error_log(__METHOD__);

        if ($_POST) {
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
                'type'    => 'MASTER',
                'updated' => $created,
                'created' => $created,
            ]);
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
                'domain'  => '*.' . $domain,
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
            return $this->list();
        }
        return $this->t->create($this->in);
    }

    protected function update() : string
    {
error_log(__METHOD__);

        if ($_POST) {
            extract($_POST);

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
            // TODO check $res ???
            util::log('Updated DNS domain ID ' . $this->g->in['i'], 'success');
            return $this->list();
        } elseif ($this->g->in['i']) {
            $sql = "
 SELECT content as soa
   FROM records
  WHERE type='SOA'
    AND domain_id=:did";

            $soa = db::qry($sql, ['did' => $this->g->in['i']], 'one');
            $dom = db::read('name', 'id', $this->g->in['i'], '', 'one');
            // TODO check $soa and $dom ???
            return $this->t->update(array_merge($dom, $soa));
        }
        return 'Error updating item';
    }

    protected function delete() : string
    {
error_log(__METHOD__);

        if ($this->g->in['i']) {
            $sql = "
 DELETE FROM `records`
  WHERE  domain_id = :id";

            $res1 = db::qry($sql, ['id' => $this->g->in['i']]);
            $res2 = db::delete([['id', '=', $this->g->in['i']]]);
            // TODO check $res1 and $res2 ???
            util::log('Deleted DNS zone ID: ' . $this->g->in['i'], 'success');
            util::ses('p', '', '1');
            return $this->list();
        }
        return 'Error deleting item';
    }

    protected function list() : string
    {
error_log(__METHOD__);

        $pager = util::pager(
            (int) util::ses('p'),
            (int) $this->g->perp,
            (int) db::read('count(id)', '', '', '', 'col')
        );
// might be useful when permissions are needed
//   LEFT OUTER JOIN permissions P ON D.id = P.domain
//  WHERE (P.userid='1' OR 1)

        $sql = "
 SELECT D.id,D.name,D.type,count(R.domain_id) AS records
   FROM domains D
   LEFT OUTER JOIN records R ON D.id = R.domain_id
  GROUP BY D.id, D.name, D.type
 HAVING (D.name LIKE '' OR 1)
    AND (D.type='' OR 1)
  ORDER BY D.`updated` DESC LIMIT " . $pager['start'] . "," . $pager['perp'];

        return $this->t->list(array_merge(
            db::qry($sql),
            ['pager' => $pager]
        ));
    }
}

?>
