<?php

declare(strict_types=1);
// lib/php/plugin.php 20150101 - 20230604
// Copyright (C) 2015-2023 Mark Constable <markc@renta.net> (AGPL-3.0)

/**
 * Summary of Plugin
 * @author Mark Constable
 * @copyright (c) 2023
 */
class Plugin
{
    /**
     * Summary of buf
     * @var string
     */
    protected string $buf = '';
    /**
     * Summary of dbh
     * @var mixed
     */
    protected mixed $dbh = null;
    /**
     * Summary of tbl
     * @var string
     */
    protected string $tbl = '';
    /**
     * Summary of in
     * @var array
     */
    protected array $in = [];
    /**
     * Summary of g
     * @var object
     */
    protected Object $g;

    /**
     * Summary of __construct
     * @param Theme $t
     */
    public function __construct(public Theme $t)
    {
        elog(__METHOD__);

        $o = $t->g->in['o'];
        $m = $t->g->in['m'];

        if (!util::is_usr() && ('auth' !== $o || ('list' !== $m && 'create' !== $m && 'resetpw' !== $m))) {
            util::redirect($t->g->cfg['self'] . '?o=auth');
        }

        $this->t = $t;
        $this->g = $t->g;
        $this->in = util::esc($this->in);

        if ($this->tbl) {
            if (!is_null($this->dbh)) {
                db::$dbh = $this->dbh;
            } elseif (is_null(db::$dbh)) {
                db::$dbh = new db($t->g->db);
            }
            db::$tbl = $this->tbl;
        }

        $this->buf .= $this->{$t->g->in['m']}();
    }

    /**
     * Summary of __toString
     * @return string
     */
    public function __toString(): string
    {
        elog(__METHOD__);

        return $this->buf;
    }

    /**
     * Summary of __call
     * @param string $name
     * @param array $args
     * @return string
     */
    public function __call(string $name, array $args): string
    {
        elog(__METHOD__ . '() name = ' . $name . ', args = ' . var_export($args, true));

        return 'Plugin::' . $name . '() not implemented';
    }

    /**
     * Summary of create
     * @return mixed
     */
    protected function create(): ?string
    {
        elog(__METHOD__);

        if (util::is_post()) {
            $this->in['updated'] = date('Y-m-d H:i:s');
            $this->in['created'] = date('Y-m-d H:i:s');
            $lid = db::create($this->in);
            util::log('Item number ' . $lid . ' created', 'success');
            util::relist();
        } else {
            return $this->t->create($this->in);
        }
    }

    /**
     * Summary of read
     * @return string
     */
    protected function read(): string
    {
        elog(__METHOD__);

        return $this->t->read(db::read('*', 'id', $this->g->in['i'], '', 'one'));
    }

    /**
     * Summary of update
     * @return string
     */
    protected function update(): string
    {
        elog(__METHOD__);

        if (util::is_post()) {
            $this->in['updated'] = date('Y-m-d H:i:s');
            if (db::update($this->in, [['id', '=', $this->g->in['i']]])) {
                util::log('Item number ' . $this->g->in['i'] . ' updated', 'success');
                util::relist();
            } else {
                util::log('Error updating item.');
            }
        }

        return $this->read();
    }

    /**
     * Summary of delete
     * @return string
     */
    protected function delete(): string
    {
        elog(__METHOD__);

        dbg($this->t);

        if (util::is_post()) {
            if ($this->g->in['i']) {
                $res = db::delete([['id', '=', $this->g->in['i']]]);
                util::log('Item number ' . $this->g->in['i'] . ' removed', 'success');
                util::relist();
            }
        } else {
            $tmp = $this->t->delete($this->g->in['i']);
            elog("tmp = $tmp");
            return $tmp;
        }

        util::log('Error deleting item');
        return ''; // was and should be return type void
    }

    /**
     * Summary of list
     * @return string
     */
    protected function list(): string
    {
        elog(__METHOD__);

        return $this->t->list(db::read('*', '', '', 'ORDER BY `updated` DESC'));
    }
}
