<?php declare(strict_types = 1);?>
<?php error_log(__FILE__);?>
<?php
// w/news/list.php 20151030 (C) 2015 Mark Constable <markc@renta.net> (AGPL-3.0)

dbg($this);

$buf = '';
$hdr = util::is_adm() ? '
    <p>
This is a simple news system, you can
<a href="?o=w_news&m=create" title="Create">create</a> a new item or
<a href="?o=w_news&m=read" title="Read">read</a> them at your leisure.
    </p>': '';

foreach ($data as $d) $buf .= w_news_row($d);
return '
        <h2><i class="fa fa-newspaper-o fa-fw"></i> News</h2>' . $hdr . $buf;

    function w_news_row($ary) : string
    {
        extract($ary);

        $ext = util::is_adm() ? '
              <a class="btn btn-success btn-xs" href="?o=w_news&m=update&i=' . $id . '" title="Update">E</a>
              <a class="btn btn-danger btn-xs" href="?o=w_news&m=delete&i=' . $id . '" title="Delete" onClick="javascript: return confirm(\'Are you sure you want to remove '.$id.'?\')">X</a>' : '';

        return '
        <div class="row">

          <div class="col-md-6">
            <h4><a href="?o=w_news&m=read&i=' . $id . '">' . $title . '</a><h4>
          </div>

          <div class="col-md-6 text-right">
            <small>
              <i>' . util::now($updated) . ' by ' . $author . '</i> ' . $ext . '
            </small>
          </div>

        </div>
        <div class="row">

          <div class="col-md-12"><p>' . nl2br($content) . '</p><hr>
          </div>

        </div>
          ';
    }

