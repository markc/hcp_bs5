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
            $in = $this->validate($this->in);
            if (!empty($in)) {
                $in['created'] = $in['updated'];
                $lid = db::create($in);
                $this->update_domains($in['domain_id'], $in['updated'] );
                util::log('Created DNS record ID: ' . $lid . ' for ' . $in['name'], 'success');
            }
            $i = intval(util::enc($_POST['did']));
            util::redirect( $this->cfg['self'] . '?o=' . $this->g->in['o'] . '&m=list&i=' . $i);
        }
        return 'Error creating DNS record';
    }

    protected function update() : string
    {
error_log(__METHOD__);

        if (util::is_post()) {
            $in = $this->validate($this->in);
            if (!empty($in)) {
                $in['created'] = $in['updated'];
                db::update($this->in, [['id', '=', $this->g->in['i']]]);
                $this->update_domains($in['domain_id'], $in['updated'] );
                util::log('Updated DNS record ID: ' . $this->g->in['i'] . ' for ' . $dom, 'success');
            }
            $i = intval(util::enc($_POST['did']));
            util::redirect( $this->cfg['self'] . '?o=' . $this->g->in['o'] . '&m=list&i=' . $i);
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
            $i = $did;
            util::redirect( $this->cfg['self'] . '?o=' . $this->g->in['o'] . '&m=list&i=' . $i);
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

    private function validate(array $in) : array
    {
error_log(__METHOD__);

        if (empty($in['content'])) {
            util::log('Content must not be empty');
            return [];
        } elseif ($in['name'] && !preg_match('/^[a-zA-Z0-9_-]/', $in['name'])) {
            util::log('Record name must only contain letters, numbers, _ and -');
            return [];
        } elseif (($in['type'] === 'A') && !filter_var($in['content'], FILTER_VALIDATE_IP)) {
            util::log('An "A" record must contain a legitimate IP');
            return [];
        }
        if ($in['type'] === 'TXT')
            $in['content'] = '"' . trim($in['content'], '"') . '"';

        $domain = strtolower(util::enc($_POST['domain']));
        $in['name'] = strtolower(rtrim(str_replace($domain, $in['name']), '.'));
        $in['name'] = $in['name'] ? $in['name'] . '.' . $domain : $domain;

        $in['ttl'] = intval($in['ttl']);
        $in['prio'] = intval($in['prio']);
        $in['updated'] = date('Y-m-d H:i:s');
        $in['domain_id'] = intval(util::enc($_POST['did']));

        return $in;
    }
}

?>
