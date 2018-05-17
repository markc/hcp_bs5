<?php
// lib/php/plugins/news.php 20150101 - 20180517
// Copyright (C) 2015-2018 Mark Constable <markc@renta.net> (AGPL-3.0)

class Plugins_News extends Plugin
{
    protected
    $tbl = 'news_view',
    $in  = [
        'title'     => '',
        'media'     => '',
        'author'    => 1,
        'content'   => '',
    ];

    protected function read() : string
    {
error_log(__METHOD__);


        return $this->t->read(db::read('*', 'id', $this->g->in['i'], '', 'one'));
    }

    protected function update() : string
    {
error_log(__METHOD__);

        if (util::is_post()) return parent::update();
        return $this->t->update(db::read('*', 'id', $this->g->in['i'], '', 'one'));
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

    //id | title | content | media| author | updated | created | uid | login
    protected function list() : string
    {
error_log(__METHOD__);

        if ($this->g->in['x'] === 'json') {
            $columns = [
                ['dt' => 0, 'db' => 'id'],
                ['dt' => 1, 'db' => 'title',     'formatter' => function($d, $row) {
                    return '
                    <img class="mb-2" src="' . $row['media'] . '" alt="' . $row['title'] . '" style="width:300px;">
                    <p class="text-center">
                      <small>' . util::now($row['updated']) . '<br> by ' . $row['login'] . '</small>
                    </p>';
                }],
                ['dt' => 2, 'db' => 'content',     'formatter' => function($d, $row) {
                    return '
                    <h4><a href="?o=news&m=read&i=' . $row['id'] . '" title="Show post ' . $row['id'] . '">' . $row['title'] . '</a></h4>
                    <p>' . nl2br(strtok(wordwrap($row['content'], 700, "...\n"), "\n")) . '</p>';
                }],
                ['dt' => 3, 'db' => 'media'],
                ['dt' => 4, 'db' => 'updated'],
                ['dt' => 5, 'db' => 'login'],
            ];
            return json_encode(db::simple($_GET, 'news_view', 'id', $columns), JSON_PRETTY_PRINT);
        }
        return $this->t->list([]);
    }
}

?>
