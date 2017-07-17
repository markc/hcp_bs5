<?php
// lib/php/themes/bootstrap/news.php 20170225 - 20170317
// Copyright (C) 2015-2017 Mark Constable <markc@renta.net> (AGPL-3.0)

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

        return '
          <h3 class="min600">
            <a href="?o=news&m=list" title="Go back to list">
              <i class="fa fa-newspaper-o fa-fw"></i> ' . $title . '
            </a>
          </h3>
          <div class="table-responsive">
            <table class="table min600">
              <tbody>
                <tr>
                  <td>' . nl2br($content) . '</td>
                  <td class="text-center nowrap w200">
                    <small>
                      by <b><a href="?o=accounts&m=update&i=' . $uid . '">' . $author_buf . '</a></b><br>
                      <i>' . util::now($updated) . '</i>
                    </small>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          <div class="row">
            <div class="col-12 text-right">
              <div class="btn-group">
                <a class="btn btn-secondary" href="?o=news&m=list">&laquo; Back</a>
                <a class="btn btn-danger" href="?o=news&m=delete&i=' . $id . '" title="Remove this item" onClick="javascript: return confirm(\'Are you sure you want to remove ' . $title . '?\')">Remove</a>
                <a class="btn btn-primary" href="?o=news&m=update&i=' . $id . '">Update</a>
              </div>
            </div>
          </div>';
    }

    public function update(array $in) : string
    {
error_log(__METHOD__);

error_log('bootstrap news update ='.var_export($in , true));

        if (!util::is_adm() && ($_SESSION['usr']['id'] !== $in['author'])) {
            util::log('You do not have permissions to update this post');
            return $this->read($in);
        }

        return $this->editor($in);
    }

    public function list(array $in) : string
    {
error_log(__METHOD__);

        $buf = $pgr_top = $pgr_end = '';
        $pgr = $in['pager']; unset($in['pager']);

        if ($pgr['last'] > 1) {
            $pgr_top ='
          <div class="col-md-6">' . $this->pager($pgr) . '
          </div>';
            $pgr_end = '
          <div class="row">
            <div class="col-12">' . $this->pager($pgr) . '
            </div>
          </div>';
        }

        foreach($in as $row) {
            extract($row);
            $author_buf = $fname && $lname
                ? $fname . ' ' . $lname
                : ($fname && empty($lname) ? $fname : $login);
            $buf .= '
                <tr>
                  <td class="nowrap">
                    <a href="?o=news&m=read&i=' . $id . '" title="Show item ' . $id . '">
                      <strong>' . $title . '</strong>
                    </a>
                  </td>
                  <td class="text-center nowrap bg-primary text-white w200" rowspan="2">
                    <small>
                      by <b><a class="text-white" href="?o=accounts&m=update&i=' . $uid . '">' . $author_buf . '</a></b><br>
                      <i>' . util::now($updated) . '</i>
                    </small>
                  </td>
                </tr>
                <tr>
                  <td><p>' . nl2br($content) . '</p></td>
                </tr>';
        }

        return '
        <div class="row">
          <div class="col-md-6">
            <h3 class="min600">
              <a href="?o=news&m=create" title="Add news item">
                <i class="fa fa-newspaper-o fa-fw"></i> News
                <small><i class="fa fa-plus-circle fa-fw"></i></small>
              </a>
            </h3>
          </div>' . $pgr_top . '
        </div>
        <div class="table-responsive">
          <table class="table table-bordered min600">
            <tbody>' . $buf . '
            </tbody>
          </table>
        </div>' . $pgr_end;
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

        return '
          <h3 class="min600">
            <a href="?o=news&m=list">
              <i class="fa fa-newspaper-o fa-fw"></i> ' . $header . '
            </a>
          </h3>
          <form method="post" action="' . $this->g->cfg->self . '">
            <input type="hidden" name="o" value="' . $this->g->in['o'] . '">
            <input type="hidden" name="m" value="' . $this->g->in['m'] . '">
            <input type="hidden" name="author" value="' . $uid . '">
            <div class="row">
              <div class="col-md-4">
                <div class="form-group">
                  <label for="title">Title</label>
                  <input type="text" class="form-control" id="title" name="title" value="' . $title . '" required>
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
