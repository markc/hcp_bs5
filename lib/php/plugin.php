<?php
// lib/php/plugin.php 20150101 - 20180512
// Copyright (C) 2015-2018 Mark Constable <markc@renta.net> (AGPL-3.0)

class Plugin
{
    protected
    $buf = '',
    $dbh = null,
    $tbl = '',
    $in  = [];

    public function __construct(Theme $t)
    {
error_log(__METHOD__);

        $o = $t->g->in['o'];
        $m = $t->g->in['m'];

        if (!util::is_usr() && $o !== 'auth' && $m !== 'list' && $m !== 'read') {
            util::log('You must <a href="?o=auth">Sign in</a> to create, update or delete items');
            header('Location: ' . $t->g->cfg['self'] . '?o=auth');
            exit();
        }

        $this->t  = $t;
        $this->g  = $t->g;
        $this->in = util::esc($this->in);
        if ($this->tbl) {
            if (!is_null($this->dbh))
                db::$dbh = $this->dbh;
            elseif (is_null(db::$dbh))
                db::$dbh = new db($t->g->db);
            db::$tbl = $this->tbl;
        }
        $this->buf .= $this->{$t->g->in['m']}();
    }

    public function __toString() : string
    {
error_log(__METHOD__);

        return $this->buf;
    }

    protected function create() : string
    {
error_log(__METHOD__);

        if ($_POST) {
            $this->in['updated'] = date('Y-m-d H:i:s');
            $this->in['created'] = date('Y-m-d H:i:s');
            $lid = db::create($this->in);
            util::log('Item number ' . $lid . ' created', 'success');
            util::ses('p', '', '1');
            return $this->list();
        } else return $this->t->create($this->in);
    }

    protected function read() : string
    {
error_log(__METHOD__);

        return $this->t->read(db::read('*', 'id', $this->g->in['i'], '', 'one'));
    }

    protected function update() : string
    {
error_log(__METHOD__);

        if ($_POST) {
            $this->in['updated'] = date('Y-m-d H:i:s');
            db::update($this->in, [['id', '=', $this->g->in['i']]]);
            util::log('Item number ' . $this->g->in['i'] . ' updated', 'success');
            util::ses('p', '', '1');
            return $this->list();
        } elseif ($this->g->in['i']) {
            return $this->t->update(db::read('*', 'id', $this->g->in['i'], '', 'one'));
        } else return 'Error updating item';
    }

    protected function delete() : string
    {
error_log(__METHOD__);

        if ($this->g->in['i']) {
            $res = db::delete([['id', '=', $this->g->in['i']]]);
            util::log('Item number ' . $this->g->in['i'] . ' removed', 'success');
            util::ses('p', '', '1');
            return $this->list();
        } else return 'Error deleting item';
    }

    protected function list() : string
    {
error_log(__METHOD__);

        return $this->t->list(db::read('*', '', '', 'ORDER BY `updated` DESC'));
    }

    public function __call(string $name, array $args) : string
    {
error_log(__METHOD__ . '() name = ' . $name . ', args = '. var_export($args,true));

        return 'Plugin::' . $name . '() not implemented';
    }
}

?>
