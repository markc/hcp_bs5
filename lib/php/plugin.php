<?php
// lib/php/plugin.php 20150101 - 20200414
// Copyright (C) 2015-2020 Mark Constable <markc@renta.net> (AGPL-3.0)

class Plugin
{
    protected
    $buf = '',
    $dbh = null,
    $tbl = '',
    $in  = [];

    public function __construct(Theme $t)
    {
elog(__METHOD__);

        $o = $t->g->in['o'];
        $m = $t->g->in['m'];
        if(!util::is_usr() && ($o !== 'auth' || ($m !== 'list' && $m !== 'create' && $m !== 'resetpw'))){
            util::redirect($t->g->cfg['self'] . '?o=auth');
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
elog(__METHOD__);

        return $this->buf;
    }

    protected function create() : string
    {
elog(__METHOD__);

        if (util::is_post()) {
            $this->in['updated'] = date('Y-m-d H:i:s');
            $this->in['created'] = date('Y-m-d H:i:s');
            $lid = db::create($this->in);
            util::log('Item number ' . $lid . ' created', 'success');
            util::relist();
        } else return $this->t->create($this->in);
    }

    protected function read() : string
    {
elog(__METHOD__);

        return $this->t->read(db::read('*', 'id', $this->g->in['i'], '', 'one'));
    }

    protected function update() : string
    {
elog(__METHOD__);

        if (util::is_post()) {
            $this->in['updated'] = date('Y-m-d H:i:s');
            if(db::update($this->in, [['id', '=', $this->g->in['i']]])){
                util::log('Item number ' . $this->g->in['i'] . ' updated', 'success');
                util::relist();
            }else{
                util::log('Error updating item.');
            }
        }
        return $this->read();
    }

    protected function delete() : string
    {
elog(__METHOD__);

        if (util::is_post()) {
            if ($this->g->in['i']) {
                $res = db::delete([['id', '=', $this->g->in['i']]]);
                util::log('Item number ' . $this->g->in['i'] . ' removed', 'success');
                util::relist();
            }
        }
        return 'Error deleting item';
    }

    protected function list() : string
    {
elog(__METHOD__);

        return $this->t->list(db::read('*', '', '', 'ORDER BY `updated` DESC'));
    }

    public function __call(string $name, array $args) : string
    {
elog(__METHOD__ . '() name = ' . $name . ', args = '. var_export($args,true));

        return 'Plugin::' . $name . '() not implemented';
    }
}

?>
