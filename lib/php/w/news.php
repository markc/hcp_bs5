<?php declare(strict_types = 1);?>
<?php error_log(__FILE__);?>
<?php
// w_news.php 20151018 (C) 2015-2016 Mark Constable <markc@renta.net> (AGPL-3.0)

class w_News extends Crud
{
    protected $in = [
        'title'     => '',
        'author'    => '',
        'content'   => '',
        'updated'   => '',
        'created'   => '',
    ];
    protected $acl = 0;

    public function delete()
    {
error_log(__METHOD__);

        util::acl(1);
        parent::delete();
    }
}
