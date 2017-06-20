<?php
// lib/php/plugins/records.php 20150101 - 20170423
// Copyright (C) 2015-2017 Mark Constable <markc@renta.net> (AGPL-3.0)

class Plugins_Records extends Plugin
{
    protected
    $tbl = 'records',
    $in = [
        'auth'        => 1, // ?
        'change_date' => '', // ?
        'content'     => '',
        'disabled'    => 0, // ?
        'domain_id'   => null,
        'name'        => '',
        'ordername'   => '', // ?
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

        if ($_POST) {
            $sql = "
 SELECT name FROM domains
  WHERE id = :did";

            $domain = db::qry($sql, ['did' => $this->in['domain_id']], 'col');
            $this->in['updated'] = date('Y-m-d H:i:s');
            $this->in['created'] = date('Y-m-d H:i:s');
            $this->in['name'] = $this->in['name']
                ? $this->in['name'] . '.' . $domain
                : $domain;
            $lid = db::create($this->in);
            util::log('Created DNS record ID: ' . $lid, 'success');
            $this->g->in['i'] = $this->in['domain_id'];
            return $this->list();
        }
        return $this->t->create($this->in);
    }

    protected function update() : string
    {
error_log(__METHOD__);

        if ($_POST) {
            $this->in['disabled'] = isset($_POST['active']) && $_POST['active'] ? 0 : 1;
            $res = db::update($this->in, [['id', '=', $this->g->in['i']]]);
            // TODO check $res ???
            util::log('Updated DNS record ID: ' . $this->g->in['i'], 'success');
            $this->g->in['i'] = $this->in['domain_id'];
            return $this->list();
        } elseif ($this->g->in['i']) {
            return $this->list();
        }
        return 'Error updating DNS record';
    }

    protected function delete() : string
    {
error_log(__METHOD__);

        if ($this->g->in['i']) {
            $res = db::delete([['id', '=', $this->g->in['i']]]);
            // TODO check $res ???
            util::log('Deleted DNS record ID: ' . $this->g->in['i'], 'success');
            util::ses('p', '', '1');
            $this->g->in['i'] = $this->in['domain_id'];
            return $this->list();
        }
        return 'Error deleting item';
    }

    protected function list() : string
    {
error_log(__METHOD__);

          $sql = "
 SELECT name FROM domains
  WHERE id = :did";

        $domain = db::qry($sql, ['did' => $this->g->in['i']], 'col');

        $sql = "
 SELECT id,name,type,content,ttl,disabled,prio AS priority
   FROM records
  WHERE (name LIKE '' OR 1) AND
        (content LIKE '' OR 1) AND
        (domain_id = :did) AND
        (type != 'SOA')";

        return $this->t->update(array_merge(
            ['domain' => $domain],
            ['domain_id' => $this->g->in['i']],
            db::qry($sql, ['did' => $this->g->in['i']])
        ));
    }
}

?>
