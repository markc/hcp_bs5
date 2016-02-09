<?php declare(strict_types = 1);?>
<?php error_log(__FILE__);?>
<?php
// bootstrap/w/news/item.php 20151030 (C) 2015-2016 Mark Constable <markc@renta.net> (AGPL-3.0)

$ext = util::is_adm() ? '
        <br>
        <div class="col-md-12 text-right">'
          . $this->a('?o=w_news', '&laquo; Back', 'default') .'
          <a class="btn btn-success" href="?o=w_news&m=update&i=' . $id . '" title="Update">Edit</a>
          <a class="btn btn-danger" href="?o=w_news&m=delete&i=' . $id . '" title="Delete" onClick="javascript: return confirm(\'Are you sure you want to remove '.$id.'?\')">Delete</a>
        </div>' : '';

$ext2 = util::is_adm() ? '
        <br><a href="?o=w_news&m=update&i=' . $id . '" title="Edit">Edit</a>' : '';

return '
        <h2>
          <i class="fa fa-newspaper-o fa-fw"></i>
          <a href="?o=w_news">' . $title . '</a>
        </h2>
        <div class="row">
          <div class="col-md-2 text-center">
            <small>
              <em>' . util::now($updated) . ' by ' . $author . '</em>' . $ext2 . '
            </small>
          </div>
          <div class="col-md-10">' . nl2br($content) . '
          </div>
        </div>';
