<?php
// lib/php/plugins/domains.php 20150101 - 20170423
// Copyright (C) 2015-2017 Mark Constable <markc@renta.net> (AGPL-3.0)

class Plugins_Domains extends Plugin
{
    protected
    $dbh = null,
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

    public function __construct(Theme $t)
    {
error_log(__METHOD__);

        if ($t->g->dns['db']['type'])
            $this->dbh = new db($t->g->dns['db']);
        parent::__construct($t);
    }

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
                'master'  => $type === 'SLAVE' ? $master : '',
                'type'    => $type ? $type : 'MASTER',
                'updated' => $created,
                'created' => $created,
            ]);

            if ($type === 'SLAVE') {
                util::log('Created DNS Zone: ' . $domain, 'success');
                return $this->list();
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

        if ($this->g->in['x'] !== 'json')
          return $this->t->list([]);

        extract($this->t->g->in);

        $search = $search ? "
 HAVING (D.name LIKE '%$search%')
     OR (D.type LIKE '%$search%')" : '';

        if ($sort === 'name') $orderby = 'D.`name`';
        elseif ($sort === 'type') $orderby = 'D.`type`';
        elseif ($sort === 'records') $orderby = '`records`';
        else $orderby = 'D.`updated`';

        $sql = "
 SELECT D.id,D.name,D.type,count(R.domain_id) AS records
   FROM domains D
   LEFT OUTER JOIN records R ON D.id = R.domain_id
  GROUP BY D.name, D.type $search
  ORDER BY $orderby $order LIMIT $offset,$limit";

        return json_encode(array_merge(
            ['total' => db::read('count(id)', '', '', '', 'col')],
            ['rows' => db::qry($sql)]
        ));
    }
}

?>
