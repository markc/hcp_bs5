<?php

declare(strict_types=1);
// lib/php/plugins/domains.php 20150101 - 20230604
// Copyright (C) 2015-2023 Mark Constable <markc@renta.net> (AGPL-3.0)

class Plugins_Domains extends Plugin
{
    //    protected mixed $dbh;

    protected string $tbl = 'domains';

    public array $inp = [
        'name'              => '',
        'master'            => '',
        'last_check'        => '',
        'type'              => '',
        'notified_serial'   => '',
        'account'           => '',
        'ip'                => '',
        'ns1'               => '',
        'ns2'               => '',
    ];

    public function __construct($g)
    {
        if ($g->dns['db']['type']) {
            $this->dbh = new db($g->dns['db']);
        }
        parent::__construct($g);
    }

    public function create(): string
    {
        if (util::is_post()) {
            extract($_POST);
            // Usage: addpdns domain ip ns1 ns2 [mx] [spfip] [sshkey]
            //util::exe("addpdns {$domain} {$ip} {$ns1} {$ns2} {$mxhost} \"{$spfip}\"");
            util::log("addpdns {$domain} {$ip} {$ns1} {$ns2} {$mxhost} \"{$spfip}\"");
            elog("addpdns {$domain} {$ip} {$ns1} {$ns2} {$mxhost} \"{$spfip}\"");
            return "addpdns {$domain} {$ip} {$ns1} {$ns2} {$mxhost} \"{$spfip}\"";
        } else {
            return $this->g->t->create($this->inp);
        }
    }

    protected function create2(): string
    {
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
                'name' => $domain,
                'master' => 'SLAVE' === $type ? $master : '',
                'type' => $type ? $type : 'MASTER',
                'updated' => $created,
                'created' => $created,
            ]);

            if ('SLAVE' === $type) {
                util::log('Created DNS Zone: ' . $domain, 'success');
                util::redirect($this->g->cfg['self'] . '?o=' . $this->g->in['o'] . '&m=list');
            }

            $sql = '
 INSERT INTO `records` (
        content, created, disabled, domain_id, name, prio, ttl, type, updated
) VALUES (
        :content, :created, :disabled, :did, :domain, :prio, :ttl, :type, :updated
)';
            db::qry($sql, [
                'content' => $soa_buf,
                'created' => $created,
                'did' => $did,
                'disabled' => $disable,
                'domain' => $domain,
                'prio' => $prio,
                'ttl' => $ttl,
                'type' => 'SOA',
                'updated' => $created,
            ]);
            db::qry($sql, [
                'content' => $ns1 . $domain,
                'created' => $created,
                'did' => $did,
                'disabled' => $disable,
                'domain' => $domain,
                'prio' => $prio,
                'ttl' => $ttl,
                'type' => 'NS',
                'updated' => $created,
            ]);
            db::qry($sql, [
                'content' => $ns2 . $domain,
                'created' => $created,
                'did' => $did,
                'disabled' => $disable,
                'domain' => $domain,
                'prio' => $prio,
                'ttl' => $ttl,
                'type' => 'NS',
                'updated' => $created,
            ]);
            db::qry($sql, [
                'content' => $a,
                'created' => $created,
                'did' => $did,
                'disabled' => $disable,
                'domain' => $domain,
                'prio' => $prio,
                'ttl' => $ttl,
                'type' => 'A',
                'updated' => $created,
            ]);
            db::qry($sql, [
                'content' => $a,
                'created' => $created,
                'did' => $did,
                'disabled' => $disable,
                'domain' => 'cdn.' . $domain,
                'prio' => $prio,
                'ttl' => $ttl,
                'type' => 'A',
                'updated' => $created,
            ]);
            db::qry($sql, [
                'content' => $a,
                'created' => $created,
                'did' => $did,
                'disabled' => $disable,
                'domain' => 'www.' . $domain,
                'prio' => $prio,
                'ttl' => $ttl,
                'type' => 'A',
                'updated' => $created,
            ]);
            db::qry($sql, [
                'content' => $mx . $domain,
                'created' => $created,
                'did' => $did,
                'disabled' => $disable,
                'domain' => $domain,
                'prio' => $prio,
                'ttl' => $ttl,
                'type' => 'MX',
                'updated' => $created,
            ]);
            util::log('Created DNS Zone: ' . $domain, 'success');
            util::redirect($this->g->cfg['self'] . '?o=' . $this->g->in['o'] . '&m=list');
        }

        return $this->g->t->create($this->inp);
    }

    protected function update(): string
    {
        if ($this->inp['increment']) {
            //            if ($this->inp['increment']) {
            $sql = "
 SELECT content as soa
   FROM records
  WHERE type='SOA'
    AND domain_id=:did";

            $oldsoa = explode(' ', db::qry($sql, ['did' => $this->g->in['i']], 'col'));
            $primary = $oldsoa[0];
            $email = $oldsoa[1];
            $serial = $oldsoa[2];
            $refresh = $oldsoa[3];
            $retry = $oldsoa[4];
            $expire = $oldsoa[5];
            $ttl = $oldsoa[6];
            //            } else {
            //                extract($_POST);
            //            }

            $today = date('Ymd');
            $serial_day = substr($serial, 0, 8);
            $serial_rev = substr($serial, -2);

            $serial = ($serial_day == $today)
                ? "{$today}" . sprintf('%02d', $serial_rev + 1)
                : "{$today}" . '00';

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

            if ($this->inp['increment']) {
                return $serial;
            }

            // TODO check $res ???
            util::log('Updated DNS domain ID ' . $this->g->in['i'], 'success');
            util::redirect($this->g->cfg['self'] . '?o=' . $this->g->in['o'] . '&m=list');
        } elseif (util::is_post() && $this->g->in['i']) {
            extract($_POST);

            $dom = db::read('name,type,master', 'id', $this->g->in['i'], '', 'one');
            if ('SLAVE' === $dom['type']) {
                return $this->g->t->update($dom);
            }
            $sql = "
 SELECT content as soa
   FROM records
  WHERE type='SOA'
    AND domain_id=:did";

            $soa = db::qry($sql, ['did' => $this->g->in['i']], 'one');

            return $this->g->t->update(array_merge($dom, $soa));
        }

        return 'Error updating item';
    }

    protected function delete(): ?string
    {
        if (util::is_post()) {
            $sql = '
 DELETE FROM `records`
  WHERE  domain_id = :id';

            $res1 = db::qry($sql, ['id' => $this->g->in['i']]);
            $res2 = db::delete([['id', '=', $this->g->in['i']]]);
            // TODO check $res1 and $res2 ???
            util::log('Deleted DNS zone ID: ' . $this->g->in['i'], 'success');
            util::redirect($this->g->cfg['self'] . '?o=' . $this->g->in['o'] . '&m=list');
        } else {
            return $this->g->t->delete($this->g->in);
        }
    }

    protected function list(): string
    {
        if ('json' === $this->g->in['x']) {
            $columns = [
                [
                    'dt' => 0,
                    'db' => 'name',
                    'formatter' => function ($d, $row) {
                        return ('SLAVE' !== $row['type']) ? '
                    <a href="?o=records&m=list&i=' . $row['id'] . '" title="Update Domain SOA">
                      <b>' . $d . '</b></a>' : '<b>' . $d . '</b>';
                    }
                ],
                ['dt' => 1, 'db' => 'type'],
                ['dt' => 2, 'db' => 'records'],
                [
                    'dt' => 3,
                    'db' => 'soa',
                    'formatter' => function ($d, $row) {
                        $soa = explode(' ', $row['soa']);

                        return ('SLAVE' !== $row['type']) ? '
                    <a class="serial" href="?o=domains&m=update&i=' . $row['id'] . '" title="Update Serial">' . $soa[2] . '</a>' : $soa[2];
                    }
                ],
                [
                    'dt' => 4,
                    'db' => 'id',
                    'formatter' => function ($d, $row) {
                        return '
                    <a href="?o=domains&m=shwho&name=' . $row['name'] . '" class="bslink" title="Whois Summary">
                      <i class="bi bi-info-circle cursor-pointer"></i></a>
                    <a href="?o=domains&m=delete&i=' . $row['id'] . '" class="bslink" title="Remove Domain ID: ' . $d . '" data-rowid="' . $d . '" data-rowname="' . $row['name'] . '">
                      <i class="bi bi-trash cursor-pointer text-danger"></i></a>';
                    }
                ],
                ['dt' => 5, 'db' => 'updated'],
            ];

            return json_encode(db::simple($_GET, 'domains_view2', 'id', $columns), JSON_PRETTY_PRINT);
        }

        return $this->g->t->list([]);
    }

    protected function shwho(): string
    {
        return $this->g->t->shwho(
            $this->inp['name'],
            shell_exec('sudo shwho ' . $this->inp['name'])
        );
    }

    protected function incsoa(): string
    {
        return shell_exec('sudo incsoa ' . $this->inp['name']);
    }
}
