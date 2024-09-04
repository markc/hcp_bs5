<?php

declare(strict_types=1);

// lib/php/plugins/domains.php 20150101 - 20240904
// Copyright (C) 2015-2024 Mark Constable <markc@renta.net> (AGPL-3.0)

class Plugins_Domains extends Plugin
{
    protected string $tbl = 'domains';

    public array $inp = [
        'name' => '',
        'master' => '',
        'last_check' => '',
        'type' => '',
        'notified_serial' => '',
        'account' => '',
        'ip' => '',
        'ns1' => '',
        'ns2' => '',
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
            $command = "addpdns $domain $ip $ns1 $ns2 $mxhost \"$spfip\"";
            util::log($command);
            elog($command);
            return $command;
        }
        return $this->g->t->create($this->inp);
    }

    protected function create2(): string
    {
        if (util::is_post()) {
            extract($_POST);
            extract($this->g->dns);
            $created = date('Y-m-d H:i:s');
            $soa_buf = "{$soa['primary']}$domain {$soa['email']}$domain. " . date('Ymd') . "00 {$soa['refresh']} {$soa['retry']} {$soa['expire']} {$soa['ttl']}";
            $did = db::create([
                'name' => $domain,
                'master' => $type === 'SLAVE' ? $master : '',
                'type' => $type ?: 'MASTER',
                'updated' => $created,
                'created' => $created,
            ]);

            if ($type === 'SLAVE') {
                util::log("Created DNS Zone: $domain", 'success');
                util::redirect("{$this->g->cfg['self']}?o={$this->g->in['o']}&m=list");
            }

            $sql = 'INSERT INTO `records` (content, created, disabled, domain_id, name, prio, ttl, type, updated) VALUES (:content, :created, :disabled, :did, :domain, :prio, :ttl, :type, :updated)';
            $common = [
                'created' => $created,
                'did' => $did,
                'disabled' => 0,
                'domain' => $domain,
                'prio' => $prio,
                'ttl' => $ttl,
                'updated' => $created,
            ];

            db::qry($sql, [...$common, 'content' => $soa_buf, 'type' => 'SOA']);
            db::qry($sql, [...$common, 'content' => $ns1 . $domain, 'type' => 'NS']);
            db::qry($sql, [...$common, 'content' => $ns2 . $domain, 'type' => 'NS']);
            db::qry($sql, [...$common, 'content' => $a, 'type' => 'A']);
            db::qry($sql, [...$common, 'content' => $a, 'domain' => "cdn.$domain", 'type' => 'A']);
            db::qry($sql, [...$common, 'content' => $a, 'domain' => "www.$domain", 'type' => 'A']);
            db::qry($sql, [...$common, 'content' => $mx . $domain, 'type' => 'MX']);

            util::log("Created DNS Zone: $domain", 'success');
            util::redirect("{$this->g->cfg['self']}?o={$this->g->in['o']}&m=list");
        }

        return $this->g->t->create($this->inp);
    }

    protected function update(): string
    {
        if ($this->inp['increment']) {
            $sql = "SELECT content as soa FROM records WHERE type='SOA' AND domain_id=:did";
            $oldsoa = explode(' ', db::qry($sql, ['did' => $this->g->in['i']], 'col'));
            [$primary, $email, $serial, $refresh, $retry, $expire, $ttl] = $oldsoa;

            $today = date('Ymd');
            $serial_day = substr($serial, 0, 8);
            $serial_rev = substr($serial, -2);

            $serial = $serial_day == $today
                ? $today . sprintf('%02d', (int)$serial_rev + 1)
                : $today . '00';

            $soa = "$primary $email $serial $refresh $retry $expire $ttl";

            $sql = "UPDATE records SET ttl = :ttl, content = :soa, updated = :updated WHERE type = 'SOA' AND domain_id = :did";
            db::qry($sql, [
                'did' => $this->g->in['i'],
                'soa' => $soa,
                'ttl' => $ttl,
                'updated' => date('Y-m-d H:i:s'),
            ]);

            if ($this->inp['increment']) {
                return $serial;
            }

            util::log('Updated DNS domain ID ' . $this->g->in['i'], 'success');
            util::redirect("{$this->g->cfg['self']}?o={$this->g->in['o']}&m=list");
        } elseif (util::is_post() && $this->g->in['i']) {
            extract($_POST);
            $dom = db::read('name,type,master', 'id', $this->g->in['i'], '', 'one');
            if ($dom['type'] === 'SLAVE') {
                return $this->g->t->update($dom);
            }
            $sql = "SELECT content as soa FROM records WHERE type='SOA' AND domain_id=:did";
            $soa = db::qry($sql, ['did' => $this->g->in['i']], 'one');
            return $this->g->t->update([...$dom, ...$soa]);
        }

        return 'Error updating item';
    }

    protected function delete(): ?string
    {
        if (util::is_post()) {
            $sql = 'DELETE FROM `records` WHERE domain_id = :id';
            db::qry($sql, ['id' => $this->g->in['i']]);
            db::delete([['id', '=', $this->g->in['i']]]);
            util::log('Deleted DNS zone ID: ' . $this->g->in['i'], 'success');
            util::redirect("{$this->g->cfg['self']}?o={$this->g->in['o']}&m=list");
        }
        return $this->g->t->delete($this->g->in);
    }

    protected function list(): string
    {
        if ($this->g->in['x'] === 'json') {
            $columns = [
                [
                    'dt' => 0,
                    'db' => 'name',
                    'formatter' => fn($d, $row) => $row['type'] !== 'SLAVE'
                        ? "<a href=\"?o=records&m=list&i={$row['id']}\" title=\"Update Domain SOA\"><b>$d</b></a>"
                        : "<b>$d</b>"
                ],
                ['dt' => 1, 'db' => 'type'],
                ['dt' => 2, 'db' => 'records'],
                [
                    'dt' => 3,
                    'db' => 'soa',
                    'formatter' => fn($d, $row) => $row['type'] !== 'SLAVE'
                        ? "<a class=\"serial\" href=\"?o=domains&m=update&i={$row['id']}\" title=\"Update Serial\">" . explode(' ', $row['soa'])[2] . "</a>"
                        : explode(' ', $row['soa'])[2]
                ],
                [
                    'dt' => 4,
                    'db' => 'id',
                    'formatter' => fn($d, $row) => "
                        <a href=\"?o=domains&m=shwho&name={$row['name']}\" class=\"bslink\" title=\"Whois Summary\">
                          <i class=\"bi bi-info-circle cursor-pointer\"></i></a>
                        <a href=\"?o=domains&m=delete&i=$d\" class=\"bslink\" title=\"Remove Domain ID: $d\" data-rowid=\"$d\" data-rowname=\"{$row['name']}\">
                          <i class=\"bi bi-trash cursor-pointer text-danger\"></i></a>"
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
