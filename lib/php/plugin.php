<?php

declare(strict_types=1);
// lib/php/plugin.php 20150101 - 20230627
// Copyright (C) 2015-2023 Mark Constable <markc@renta.net> (AGPL-3.0)

class Plugin
{
    public $g;

    public array $inp = [];

    protected mixed $dbh = null;

    protected string $buf = '';

    protected string $tbl = '';

    public function __construct($g)
    {
        $this->g = $g;

        $this->inp = util::esc($this->inp);

        $o = $this->g->in['o'];
        $m = $this->g->in['m'];

        if (!util::is_usr() && ('auth' !== $o || ('list' !== $m && 'create' !== $m && 'resetpw' !== $m))) {
            util::redirect($this->g->cfg['self'] . '?o=auth');
        }

        if ($this->tbl) {
            if (!is_null($this->dbh)) {
                db::$dbh = $this->dbh;
            } elseif (is_null(db::$dbh)) {
                db::$dbh = new db($this->g->db);
            }
            db::$tbl = $this->tbl;
        }

        $this->buf .= $this->{$this->g->in['m']}();
    }

    public function __toString(): string
    {
        return $this->buf;
    }

    public function __call(string $name, array $args): string
    {
        elog(__METHOD__ . '() name = ' . $name . ', args = ' . var_export($args, true));

        return 'Plugin::' . $name . '() not implemented';
    }

    protected function create(): ?string
    {
        if (util::is_post()) {
            $this->inp['updated'] = date('Y-m-d H:i:s');
            $this->inp['created'] = date('Y-m-d H:i:s');
            $lid = db::create($this->inp);
            util::log('Item number ' . $lid . ' created', 'success');
            util::relist();
        } else {
            return $this->g->t->create($this->inp);
        }
    }

    protected function read(): string
    {
        return $this->g->t->read(db::read('*', 'id', $this->g->in['i'], '', 'one'));
    }

    protected function update(): string
    {
        if (util::is_post()) {
            $this->inp['updated'] = date('Y-m-d H:i:s');
            if (db::update($this->inp, [['id', '=', $this->g->in['i']]])) {
                util::log('Item number ' . $this->g->in['i'] . ' updated', 'success');
                util::relist();
            } else {
                util::log('Error updating item.');
            }
        }

        return $this->read();
    }

    protected function delete(): ?string
    {
        if (util::is_post()) {
            if ($this->g->in['i']) {
                $res = db::delete([['id', '=', $this->g->in['i']]]);
                util::log('Item number ' . $this->g->in['i'] . ' removed', 'success');
                util::relist();
            }
        } else {
            return $this->g->t->delete($this->g->in);
        }

        util::log('Error deleting item');
    }

    protected function list(): string
    {
        return $this->g->t->list(db::read('*', '', '', 'ORDER BY `updated` DESC'));
    }
}
