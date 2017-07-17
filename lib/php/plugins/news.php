<?php
// lib/php/plugins/news.php 20150101 - 20170317
// Copyright (C) 2015-2017 Mark Constable <markc@renta.net> (AGPL-3.0)

class Plugins_News extends Plugin
{
    protected
    $tbl = 'news',
    $in = [
        'title'     => '',
        'author'    => 1,
        'content'   => '',
    ];

    protected function read() : string
    {
error_log(__METHOD__);

        $sql = "
 SELECT n.*, u.id as uid, u.login, u.fname, u.lname
   FROM `news` n
        JOIN `accounts` u
            ON n.author=u.id
  WHERE n.id=:nid";

        return $this->t->read(db::qry($sql, ['nid' => $this->g->in['i']], 'one'));
    }

    protected function update() : string
    {
error_log(__METHOD__);

        if ($_POST) return parent::update();

        $sql = "
 SELECT n.*, u.id as uid, u.login, u.fname, u.lname
   FROM `news` n
        JOIN `accounts` u
            ON n.author=u.id
  WHERE n.id=:nid";

        return $this->t->update(db::qry($sql, ['nid' => $this->g->in['i']], 'one'));
    }

    protected function delete() : string
    {
error_log(__METHOD__);

        if (!util::is_adm()) {
            $author = db::read('author', 'id', $this->g->in['i'], '', 'col');
            if ($_SESSION['usr']['id'] !== $author) {
                util::log('You do not have permissions to delete this post');
                return $this->list();
            }
        }

        return parent::delete();
    }

    protected function list() : string
    {
error_log(__METHOD__);

        $pager = util::pager(
            (int) util::ses('p'),
            (int) $this->g->cfg->perp,
            (int) db::qry("SELECT count(*) FROM `news` n JOIN `accounts` u ON n.author=u.id", [], 'col')
        );

        $sql = "
 SELECT n.*, u.id as uid, u.login, u.fname, u.lname
   FROM `news` n
        JOIN `accounts` u
            ON n.author=u.id
  ORDER BY n.updated DESC LIMIT " . $pager['start'] . "," . $pager['perp'];

        return $this->t->list(array_merge(db::qry($sql), ['pager' => $pager]));
    }
}

?>
