<?php
// lib/php/plugins/records.php 20150101 - 20180523
// Copyright (C) 2015-2018 Mark Constable <markc@renta.net> (AGPL-3.0)

class Plugins_Records extends Plugin
{
    protected
    $tbl = 'records',
    $in = [
        'content'     => '',
        'name'        => '',
        'prio'        => 0,
        'ttl'         => 300,
        'type'        => '',
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

        if (util::is_post()) {
            if (empty($this->in['content'])) {
                util::log('Content must not be empty');
            } elseif ($this->in['name'] && !preg_match('/^[a-zA-Z0-9_-]/', $this->in['name'])) {
                util::log('Record name must only contain lower case letters, numbers, _ and -');
            } else {
                $this->in['ttl'] = intval($this->in['ttl']);
                $this->in['prio'] = intval($this->in['prio']);
                $domain = util::enc($_POST['domain']);
                $did = intval(util::enc($_POST['did']));
                $now = date('Y-m-d H:i:s');

                if ($this->in['type'] === 'TXT') {
                    $this->in['content'] = '"' . trim($this->in['content'], '"') . '"';
                }
                $this->in['updated'] = $now;
                $this->in['created'] = $now;
                $this->in['name'] = $this->in['name']
                    ? strtolower($this->in['name'] . '.' . $domain)
                    : $domain;
                $this->in['domain_id'] = $did;
                $lid = db::create($this->in);
                $this->update_domains($did, $now);
                util::log('Created DNS record ID: ' . $lid . ' for ' . $domain, 'success');
            }
            $this->g->in['i'] = $did;
            return $this->list();
        }
        return 'Error creating DNS record';
    }

    protected function update() : string
    {
error_log(__METHOD__);

        if (util::is_post()) {
            $this->in['ttl'] = intval($this->in['ttl']);
            $this->in['prio'] = intval($this->in['prio']);
            $dom = util::enc($_POST['domain']);
            $did = intval(util::enc($_POST['did']));
            $now = date('Y-m-d H:i:s');

            $this->in['updated'] = $now;
            db::update($this->in, [['id', '=', $this->g->in['i']]]);
            $this->update_domains($did, $now);
            util::log('Updated DNS record ID: ' . $this->g->in['i'] . ' for ' . $dom, 'success');
            $this->g->in['i'] = $did;
            return $this->list();
        }
        return 'Error updating DNS record';
    }

    protected function delete() : string
    {
error_log(__METHOD__);

        if (util::is_post()) {
            $dom = util::enc($_POST['domain']);
            $did = intval(util::enc($_POST['did']));
            $now = date('Y-m-d H:i:s');

            db::delete([['id', '=', $this->g->in['i']]]);
            $this->update_domains($did, $now);
            util::log('Deleted DNS record ID: ' . $this->g->in['i'] . ' from ' . $dom, 'success');
            $this->g->in['i'] = $did;
            return $this->list();
        }
        return 'Error deleting DNS record';
    }

    protected function list() : string
    {
error_log(__METHOD__);

        if ($this->g->in['x'] === 'json') {
            $columns = [
                ['dt' => 0,  'db' => 'name'],
                ['dt' => 1,  'db' => 'content'],
                ['dt' => 2,  'db' => 'type'],
                ['dt' => 3,  'db' => 'prio'],
                ['dt' => 4,  'db' => 'ttl'],
                ['dt' => 5,  'db' => 'id', 'formatter' => function($d) {
                    return '
                    <a class="update" href="" title="Update DNS record ID: ' . $d . '" data-rowid="' . $d . '">
                      <i class="fas fa-edit fa-fw cursor-pointer"></i></a>
                    <a class="delete" href="" title="Delete DNS record ID: ' . $d . '" data-rowid="' . $d . '">
                      <i class="fas fa-trash fa-fw cursor-pointer text-danger"></i></a>';
                }],
                ['dt' => 6,  'db' => 'active'],
                ['dt' => 7,  'db' => 'did'],
                ['dt' => 8,  'db' => 'domain'],
                ['dt' => 9,  'db' => 'updated'],
            ];
            return json_encode(db::simple($_GET, 'records_view', 'id', $columns, 'did=' . $_GET['did']), JSON_PRETTY_PRINT);
        }

        $domain = db::qry("
 SELECT name FROM domains
  WHERE id = :did", ['did' => $this->g->in['i']], 'col'); // i = domain id at this point

        return $this->t->list(['domain' => $domain, 'did' => $this->g->in['i']]);
    }

    private function update_domains(int $did, string $now) : bool
    {
error_log(__METHOD__);

        if ($did && $now) {
            $sql = "
 SELECT content
   FROM records
  WHERE type='SOA'
    AND domain_id=:did";

            $soa = util::inc_soa(db::qry($sql, ['did' => $did], 'col'));
            $sql = "
 UPDATE records
    SET content=:content
  WHERE type='SOA'
    AND domain_id=:did";

            db::qry($sql, ['did' => $did, 'content' => $soa]);
            db::$tbl = 'domains';
            return db::update(['updated' => $now], [['id', '=', $did]]);
        }
        return false;
    }
}

?>
