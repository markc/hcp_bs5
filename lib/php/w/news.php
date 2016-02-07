<?php declare(strict_types = 1);?>
<?php error_log(__FILE__);?>
<?php
// w_news.php 20151018 (C) 2015-2016 Mark Constable <markc@renta.net> (AGPL-3.0)

class w_News extends Crud
{
    protected $b2  = '
    <h2>News</h2>
    <p>
This is a simple note system, you can
<a href="?o=w_news&m=create" title="Create">create</a> a new item or
<a href="?o=w_news&m=read" title="Read">read</a> them at your leisure.
    </p>';
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
