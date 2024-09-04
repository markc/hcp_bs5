<?php

declare(strict_types=1);

// lib/php/plugins/records.php 20150101 - 20240904
// Copyright (C) 2015-2024 Mark Constable <markc@renta.net> (AGPL-3.0)

class Plugins_Records extends Plugin
{
    protected string $tbl = 'records';

    public array $inp = [
        'content'   => '',
        'name'      => '',
        'prio'      => 0,
        'ttl'       => 300,
        'type'      => '',
    ];

    public function __construct($g)
    {
        if ($g->dns['db']['type']) {
            $this->dbh = new db($g->dns['db']);
        }
        parent::__construct($g);
    }

    protected function create(): string
    {
        if (util::is_post()) {
            $in = $this->validate($this->inp);
            if (!empty($in)) {
                $in['created'] = $in['updated'];
                $lid = db::create($in);
                $this->update_domains($in['domain_id'], $in['updated']);
                util::log("Created DNS record ID: $lid for {$in['name']}", 'success');
            }
            $i = intval(util::enc($_POST['did']));
            util::redirect("{$this->g->cfg['self']}?o={$this->g->in['o']}&m=list&i=$i");
        }

        return 'Error creating DNS record';
    }

    protected function update(): string
    {
        if (util::is_post()) {
            $in = $this->validate($this->inp);
            if (!empty($in)) {
                $dom = util::enc($_POST['domain']);
                $in['created'] = $in['updated'];
                db::update($in, [['id', '=', $this->g->in['i']]]);
                $this->update_domains($in['domain_id'], $in['updated']);
                util::log("Updated DNS record ID: {$this->g->in['i']} for $dom", 'success');
            }
            $i = intval(util::enc($_POST['did']));
            util::redirect("{$this->g->cfg['self']}?o={$this->g->in['o']}&m=list&i=$i");
        }

        return 'Error updating DNS record';
    }

    protected function delete(): ?string
    {
        if (util::is_post()) {
            $dom = util::enc($_POST['domain']);
            $did = intval(util::enc($_POST['did']));
            $now = date('Y-m-d H:i:s');

            db::delete([['id', '=', $this->g->in['i']]]);
            $this->update_domains($did, $now);
            util::log("Deleted DNS record ID: {$this->g->in['i']} from $dom", 'success');
            util::redirect("{$this->g->cfg['self']}?o={$this->g->in['o']}&m=list&i=$did");
        }
        util::log('Error deleting DNS record');
        return null;
    }

    protected function list(): string
    {
        if ($this->g->in['x'] === 'json') {
            $columns = [
                ['dt' => 0,  'db' => 'name'],
                ['dt' => 1,  'db' => 'content'],
                ['dt' => 2,  'db' => 'type'],
                ['dt' => 3,  'db' => 'prio'],
                ['dt' => 4,  'db' => 'ttl'],
                ['dt' => 5,  'db' => 'id', 'formatter' => fn($d) => "
                    <a class=\"update\" href=\"\" title=\"Update DNS record ID: $d\" data-rowid=\"$d\">
                      <i class=\"fas fa-edit fa-fw cursor-pointer\"></i></a>
                    <a class=\"delete\" href=\"\" title=\"Delete DNS record ID: $d\" data-rowid=\"$d\">
                      <i class=\"fas fa-trash fa-fw cursor-pointer text-danger\"></i></a>"],
                ['dt' => 6,  'db' => 'active'],
                ['dt' => 7,  'db' => 'did'],
                ['dt' => 8,  'db' => 'domain'],
                ['dt' => 9,  'db' => 'updated'],
            ];

            return json_encode(db::simple($_GET, 'records_view', 'id', $columns, "did={$_GET['did']}"), JSON_PRETTY_PRINT);
        }

        $domain = db::qry('SELECT name FROM domains WHERE id = :did', ['did' => $this->g->in['i']], 'col');

        return $this->g->t->list(['domain' => $domain, 'did' => $this->g->in['i']]);
    }

    private function update_domains(int $did, string $now): bool
    {
        if ($did && $now) {
            $sql = "SELECT content FROM records WHERE type='SOA' AND domain_id=:did";
            $soa = util::inc_soa(db::qry($sql, ['did' => $did], 'col'));
            $sql = "UPDATE records SET content=:content WHERE type='SOA' AND domain_id=:did";
            db::qry($sql, ['did' => $did, 'content' => $soa]);
            db::$tbl = 'domains';
            return db::update(['updated' => $now], [['id', '=', $did]]);
        }
        return false;
    }

    private function validate(array $in): array
    {
        if (empty($in['content'])) {
            util::log('Content must not be empty');
            return [];
        }
        if ($in['type'] === 'A' && !filter_var($in['content'], FILTER_VALIDATE_IP)) {
            util::log('An "A" record must contain a legitimate IP');
            return [];
        }
        if ($in['type'] === 'CAA' && !preg_match('/^[a-zA-Z0-9"]+/', $in['content'])) {
            util::log('CAA record content must only contain letters and numbers');
            return [];
        }
        if ($in['name'] && $in['name'] !== '*' && !preg_match('/^[a-zA-Z0-9_-]+/', $in['name'])) {
            util::log('Record name must contain letters, numbers, _ - or only *');
            return [];
        }

        if ($in['type'] === 'TXT') {
            $in['content'] = '"' . trim(htmlspecialchars_decode($in['content'], ENT_COMPAT), '"') . '"';
        }

        if ($in['type'] === 'CAA') {
            $in['content'] = htmlspecialchars_decode($in['content'], ENT_COMPAT);
        }

        $domain = strtolower(util::enc($_POST['domain']));
        $in['name'] = strtolower(rtrim(str_replace($domain, '', $in['name']), '.'));
        $in['name'] = $in['name'] ? "{$in['name']}.$domain" : $domain;

        $in['ttl'] = intval($in['ttl']);
        $in['prio'] = intval($in['prio']);
        $in['updated'] = date('Y-m-d H:i:s');
        $in['domain_id'] = intval(util::enc($_POST['did']));

        return $in;
    }
}
