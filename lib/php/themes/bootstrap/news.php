<?php
// lib/php/themes/bootstrap/news.php 20170225 - 20180512
// Copyright (C) 2015-2018 Mark Constable <markc@renta.net> (AGPL-3.0)

class Themes_Bootstrap_News extends Themes_Bootstrap_Theme
{
    public function create(array $in) : string
    {
error_log(__METHOD__);

        return $this->editor($in);
    }

    public function read(array $in) : string
    {
error_log(__METHOD__);

        extract($in);

        $author_buf = $fname && $lname
            ? $fname . ' ' . $lname
            : ($fname && empty($lname) ? $fname : $login);

        $media = $media ?
              '<img class="mb-3" src="' . $media . '" alt="' . $title . ' Image">' :
              '<div class="media-blank"></div>';

        return '
          <div class="col-12">
            <h2 class=my-0><a href="?o=news&m=list" title="Go back to list">&laquo;</a> ' . $title . '
            </h2>
          </div>
          <div class="col-12">
            <div class="media text-muted">
                <div class="media-img">' . $media . '
                  <small class="text-center">
                    <i>by <a href="?o=accounts&m=update&i=' . $uid . '">' . $author_buf . '</a>
                    <br>' . util::now($updated) . '
                    </i>
                  </small>
                </div>
              <div class="media-body">' . nl2br($content) . '
              </div>
            </div>
          </div>
          <div class="col-12 text-right mt-4">
            <div class="btn-group">
              <a class="btn btn-secondary" href="?o=news&m=list">&laquo; Back</a>
              <a class="btn btn-danger" href="?o=news&m=delete&i=' . $id . '" title="Remove this item" onClick="javascript: return confirm(\'Are you sure you want to remove ' . $title . '?\')">Remove</a>
              <a class="btn btn-primary" href="?o=news&m=update&i=' . $id . '">Update</a>
            </div>
          </div>';
    }

    public function update(array $in) : string
    {
error_log(__METHOD__);

        if (!util::is_adm() && ($_SESSION['usr']['id'] !== $in['author'])) {
            util::log('You do not have permissions to update this post');
            return $this->read($in);
        }

        return $this->editor($in);
    }

    public function list(array $in) : string
    {
error_log(__METHOD__);

        return '
        <div class="col-12">
          <h3>
            <i class="fas fa-newspaper fa-fw"></i> News
            <a href="?o=news&m=create" title="Add new post">
              <small><i class="fas fa-plus-circle fa-fw"></i></small>
            </a>
          </h3>
        </div>
      </div><!-- END UPPER ROW -->
      <div class="row">
        <div class="table-responsive">
          <table id=news class="table table-sm" style="min-width:1100px;">
             <tbody>
            </tbody>
         </table>
        </div>
        <script>
$(document).ready(function() {
  $("#news").DataTable({
    "processing": true,
    "serverSide": true,
    "ajax": "?x=json&o=news&m=list",
//    "order": [[ 0, "desc" ]],
    "columnDefs": [
      {"targets":0, "visible":false},
      {"targets":1, "width":"25%", "className":"pt-4"},
      {"targets":2, "width":"75%", "className":"pl-4 pt-4"},
      {"targets":3, "visible":false},
      {"targets":4, "visible":false},
      {"targets":5, "visible":false},
    ],
    fnDrawCallback: function() {
      $("#news thead").remove();
    }
  });
});
        </script>';
    }

    private function editor(array $in) : string
    {
error_log(__METHOD__);

        if ($this->g->in['m'] === 'create') {
            extract($_SESSION['usr']);
            $author = $uid = $id;
            $header = 'Add News';
            $submit = '
                <a class="btn btn-secondary" href="?o=news&m=list">&laquo; Back</a>
                <button type="submit" class="btn btn-primary">Add This Item</button>';
        } else {
            extract($in);
            $header = 'Update News';
            $submit = '
                <a class="btn btn-secondary" href="?o=news&m=read&i=' . $id . '">&laquo; Back</a>
                <a class="btn btn-danger" href="?o=news&m=delete&i=' . $id . '" title="Remove this item" onClick="javascript: return confirm(\'Are you sure you want to remove ' . $title . '?\')">Remove</a>
                <button type="submit" name="i" value="' . $id . '" class="btn btn-primary">Update</button>';
        }

        $author_label = $fname && $lname
            ? $fname . ' ' . $lname
            : ($fname && empty($lname) ? $fname : $login);

        $author_buf = '
                  <p class="form-control-static"><b><a href="?o=accounts&m=update&i=' . $uid . '">' . $author_label . '</a></b></p>';
        $media = $media ?? '';

        return '
          <div class="col-12">
            <h2 class=my-0>
              <a href="?o=news&m=list" title="Go back to list">&laquo;</a> ' . $header . '
            </h2>
          </div>
            <form class="col-12" method="post" action="' . $this->g->cfg['self'] . '">
              <input type="hidden" name="c" value="' . $_SESSION['c'] . '">
              <input type="hidden" name="o" value="' . $this->g->in['o'] . '">
              <input type="hidden" name="m" value="' . $this->g->in['m'] . '">
              <input type="hidden" name="author" value="' . $uid . '">
              <div class="form-row">
                <div class="col-md-4">
                  <div class="form-group">
                    <label for="title">Title</label>
                    <input type="text" class="form-control" id="title" name="title" value="' . $title . '" required>
                  </div>
                  <div class="form-group">
                    <label for="media">Media</label>
                    <input type="text" class="form-control" id="media" name="media" value="' . $media . '">
                  </div>
                  <div class="form-group">
                    <label for="author">Author</label>' . $author_buf . '
                  </div>
                </div>
                <div class="col-md-8">
                  <div class="form-group">
                    <label for="content">Content</label>
                    <textarea class="form-control" id="content" name="content" rows="12" required>' . $content . '</textarea>
                  </div>
                </div>
              </div>
              <div class="row">
                <div class="col-12 text-right">
                  <div class="btn-group">' . $submit . '
                  </div>
                </div>
              </div>
            </form>';
    }
}

?>
