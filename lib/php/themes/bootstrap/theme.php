<?php
// lib/php/themes/bootstrap/theme.php 20150101 - 20190427
// Copyright (C) 2015-2018 Mark Constable <markc@renta.net> (AGPL-3.0)

class Themes_Bootstrap_Theme extends Theme
{
    public function css() : string
    {
error_log(__METHOD__);

        return '
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.20/css/dataTables.bootstrap4.min.css">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.1.3/css/bootstrap.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.20/css/dataTables.bootstrap4.min.css">

    <style>
body{min-height:75rem;padding-top:5rem;}
table,form{width:100%;}
table.dataTable{border-collapse: collapse !important;}

.media {
  flex-direction: column;
  align-items: center;
  margin-top: 1.5rem;
  margin-bottom: 1.5rem;
}
.media-img {
  display: flex;
  flex-direction: column;
  align-items: center;
  margin-bottom: 0.75rem;
}
.media img {
  max-width: 100%;
  height: auto;
  /*margin-bottom: 0.75rem;*/
}
.media-blank {
  width: 300px;
}
.media-title {
  margin-bottom: 0.75rem;
}
.alert pre {
  margin: 0;
}
  .columns {column-gap:1.5em;columns:1;}
/*body{ background:yellow; }*/

@media (min-width:576px) {
  /*body{ background:red; }*/
}
@media (min-width:768px) {
  /*body{ background:blue; }*/
  .columns {column-gap:1.5em;columns:2;}

  .media {
    flex-direction: row;
    align-items: flex-start;
  }
  .media-body {
    margin-left: 1.5rem;
  }
  .media-img, .media-blank, .media img {
    max-width: 200px;
  }
}
@media (min-width:992px) {
  /*body{ background: green; }*/
  .media-img, .media-blank, .media img {
    max-width: 100%;
  }
}
@media (min-width:1200px) {
  /*body{ background: white; }*/
  .columns {column-gap:1.5em;columns:3;}
  .media-title {
    display: flex;
    flex-direction: row;
    justify-content: space-between;
    align-items: flex-end;
  }
}
    </style>';
    }

    public function log() : string
    {
error_log(__METHOD__);

        $alts = '';
        foreach (util::log() as $lvl => $msg) {
            $alts .= $msg ? '
            <div class="col-12">
              <div class="alert alert-' . $lvl . ' alert-dismissible fade show" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>' . $msg . '
              </div>
            </div>' : '';
        }
        return $alts;
    }

    public function head() : string
    {
error_log(__METHOD__);

        return '
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top bg-dark">
      <div class=container>
        <a class="navbar-brand" href="' . $this->g->cfg['self'] . '">
          <b><i class="fa fa-server fa-fw"></i> ' . $this->g->out['head'] . '</b>
        </a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarsDefault" aria-controls="navbarsDefault" aria-expanded="false" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarsDefault">
          <ul class="navbar-nav mr-auto">' . $this->g->out['nav1'] . '
          </ul>
          <ul class="navbar-nav ml-auto">' . $this->g->out['nav3'] . '
          </ul>
        </div>
      </div>
    </nav>';
    }

    public function nav1(array $a = []) : string
    {
error_log(__METHOD__);

        $a = isset($a[0]) ? $a : util::get_nav($this->g->nav1);
        $o = '?o=' . $this->g->in['o'];
        $t = '?t=' . util::ses('t');
        return join('', array_map(function ($n) use ($o, $t) {
            if (is_array($n[1])) return $this->nav_dropdown($n);
            $c = $o === $n[1] || $t === $n[1] ? ' active' : '';
            $i = isset($n[2]) ? '<i class="' . $n[2] . '"></i> ' : '';
            return '
            <li class="nav-item' . $c . '"><a class="nav-link" href="' . $n[1] . '">' . $i . $n[0] . '</a></li>';
        }, $a));
    }

    public function nav2() : string
    {
error_log(__METHOD__);

        return $this->nav_dropdown(['Theme', $this->g->nav2, 'fa fa-th fa-fw']);
    }

    public function nav3() : string
    {
error_log(__METHOD__);

        if (util::is_usr()) {
            $usr[] = ['Change Profile', '?o=accounts&m=read&i=' . $_SESSION['usr']['id'], 'fas fa-user fa-fw'];
            $usr[] = ['Change Password', '?o=auth&m=update&i=' . $_SESSION['usr']['id'], 'fas fa-key fa-fw'];
            $usr[] = ['Sign out', '?o=auth&m=delete', 'fas fa-sign-out-alt fa-fw'];

            if (util::is_adm() && !util::is_acl(0)) $usr[] =
                ['Switch to sysadm', '?o=accounts&m=switch_user&i=' . $_SESSION['adm'], 'fas fa-user fa-fw'];

            return $this->nav_dropdown([$_SESSION['usr']['login'], $usr, 'fas fa-user fa-fw']);
        } else return '';
    }

    public function nav_dropdown(array $a = []) : string
    {
error_log(__METHOD__);

        $o = '?o=' . $this->g->in['o'];
        $i = isset($a[2]) ? '<i class="' . $a[2] . '"></i> ' : '';
        return '
            <li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle" href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">' . $i . $a[0] . '</a>
              <div class="dropdown-menu">' . join('', array_map(function ($n) use ($o) {
            $c = $o === $n[1] ? ' active' : '';
            $i = isset($n[2]) ? '<i class="' . $n[2] . '"></i> ' : '';
            return '
                <a class="dropdown-item" href="' . $n[1] . '">' . $i . $n[0] . '</a>';
        }, $a[1])).'
              </div>
            </li>';
    }

    public function main() : string
    {
error_log(__METHOD__);

        return '
    <main class="container">
      <div class="row">' . $this->g->out['log'] . $this->g->out['main'] . '
      </div>
    </main>';
    }

    public function js() : string
    {
error_log(__METHOD__);

        return '
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
<!--
    <script src="https://code.jquery.com/jquery-3.4.1.slim.min.js" integrity="sha384-J6qa4849blE2+poT4WnyKhv5vZF5SrPo0iEjwBvKU7imGFAV0wwj1yYfoRSJoZ+n" crossorigin="anonymous"></script>
-->
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js" integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.13.0/js/fontawesome.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.13.0/js/solid.min.js"></script>

    <script src="https://cdn.datatables.net/1.10.20/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.20/js/dataTables.bootstrap4.min.js"></script>



    ';
    }

    public function js_old() : string
    {
error_log(__METHOD__);

        return '
    <script src="https://code.jquery.com/jquery-1.12.4.js"></script>
    <script src="https://cdn.datatables.net/1.10.16/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.16/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
    <script src="https://use.fontawesome.com/releases/v5.0.11/js/solid.js" integrity="sha384-Y5YpSlHvzVV0DWYRcgkEu96v/nptti7XYp3D8l+PquwfpOnorjWA+dC7T6wRgZFI" crossorigin="anonymous"></script>
    <script src="https://use.fontawesome.com/releases/v5.0.11/js/fontawesome.js" integrity="sha384-KPnpIFJjPHLMZMALe0U04jClDmqlLhkBM6ZEkFvs9AiWRYwaDXPhn2D5lr8sypQ+" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/tail.select@0.5.10/js/tail.select.min.js"></script>';
    }

    protected function modal(array $ary) : string
    {
error_log(__METHOD__);

        extract($ary);
        $hidden = isset($hidden) && $hidden ? $hidden : '';
        $footer = $footer ? '
                <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                  <button type="submit" class="btn btn-primary">' . $footer . '</button>
                </div>' : '';

        return '
        <div class="modal fade" id="' . $id . '" tabindex="-1" role="dialog" aria-labelledby="' . $id . '" aria-hidden="true">
          <div class="modal-dialog" role="document">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title">' . $title . '</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>
              <form method="post" action="' . $this->g->cfg['self'] . '">
                <input type="hidden" name="c" value="' . $_SESSION['c'] . '">
                <input type="hidden" name="o" value="' . $this->g->in['o'] . '">
                <input type="hidden" name="m" value="' . $action . '">
                <input type="hidden" id="i" name="i" value="' . $this->g->in['i'] . '">' . $hidden . '
                <div class="modal-body">' . $body . '
                </div>' . $footer . '
              </form>
            </div>
          </div>
        </div>';
    }

}

?>
