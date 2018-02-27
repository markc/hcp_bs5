<?php
// lib/php/themes/bootstrap/theme.php 20150101 - 20170317
// Copyright (C) 2015-2017 Mark Constable <markc@renta.net> (AGPL-3.0)

class Themes_Bootstrap_Theme extends Theme
{
    public function css() : string
    {
error_log(__METHOD__);

        return '
    <link href="https://fonts.googleapis.com/css?family=Quicksand:100,300,400,500,300italic" rel="stylesheet" type="text/css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta.2/css/bootstrap.min.css" integrity="sha384-PsH8R72JQ3SOdhVi3uxftmaW6Vc51MKb0q5P2rRUpPvrszuE4W1povHYgTpBfshb" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.16/css/dataTables.bootstrap4.min.css">
    <style>
body{min-height:75rem;padding-top:5rem;}
.mw30{min-width:30rem;}
.mw60{min-width:60rem;}

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

  /*body{ background:yellow; }*/

@media (min-width:576px) {
  /*body{ background:red; }*/
}
@media (min-width:768px) {
  /*body{ background:blue; }*/

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

        list($lvl, $msg) = util::log();
        return $msg ? '
        <div class="col-12">
          <div class="alert alert-' . $lvl . ' alert-dismissible fade show" role="alert">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>' . $msg . '
          </div>
        </div>' : '';
    }

    public function head() : string
    {
error_log(__METHOD__);

        return '
    <nav class="navbar navbar-expand-md navbar-dark bg-dark fixed-top">
      <div class=container>
        <a class="navbar-brand" href="' . $this->g->cfg['self'] . '" title="Home Page">
          <b><i class="fa fa-server fa-fw"></i> ' . $this->g->out['head'] . '</b>
        </a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarsDefault" aria-controls="navbarsDefault" aria-expanded="false" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarsDefault">
          <ul class="navbar-nav mr-auto">' . $this->g->out['nav1'] . '
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
            $usr[] = ['Change Profile', '?o=accounts&m=update&i=' . $_SESSION['usr']['id'], 'fa fa-user fa-fw'];
            $usr[] = ['Change Password', '?o=auth&m=update&i=' . $_SESSION['usr']['id'], 'fa fa-key fa-fw'];
            $usr[] = ['Sign out', '?o=auth&m=delete', 'fa fa-sign-out fa-fw'];

            if (util::is_adm() && !util::is_acl(0)) $usr[] =
                ['Switch to sysadm', '?o=users&m=switch_user&i=' . $_SESSION['adm'], 'fa fa-user fa-fw'];

            return $this->nav_dropdown([$_SESSION['usr']['login'], $usr, 'fa fa-user fa-fw']);
        } else return '';
    }

    public function nav_dropdown(array $a = []) : string
    {
error_log(__METHOD__);

        $o = '?o=' . $this->g->in['o'];
        $i = isset($a[2]) ? '<i class="' . $a[2] . '"></i> ' : '';
        return '
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" id="dropdown01" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">' . $i . $a[0] . '</a>
            <div class="dropdown-menu" aria-labelledby="dropdown01">'.join('', array_map(function ($n) use ($o) {
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
<script defer src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
<script defer src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.3/umd/popper.min.js" integrity="sha384-vFJXuSJphROIrBnz7yo7oB41mKfc8JzQZiCq4NCceLEaO4IHwicKwpJf9c9IpFgh" crossorigin="anonymous"></script>
<script defer src="https://use.fontawesome.com/releases/v5.0.2/js/all.js"></script>
<script defer src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta.2/js/bootstrap.min.js" integrity="sha384-alpBpkh1PFOepccYVYDB4do5UnbKysX5WZXm3XxPqe5iKTfUKjNkCk9SaVuEZflJ" crossorigin="anonymous"></script>
<script defer src="https://cdn.datatables.net/1.10.16/js/jquery.dataTables.min.js"></script>
<script defer src="https://cdn.datatables.net/1.10.16/js/dataTables.bootstrap4.min.js"></script>';
    }

    protected function pager(array $ary) : string
    {
error_log(__METHOD__);

        extract($ary);

        $b = '';
        $o = util::ses('o');

        for($i = 1; $i <= $last; $i++) $b .= '
              <li class="page-item' . ($i === $curr ? ' active' : '') . '">
                <a class="page-link" href="?o=' . $o . '&m=list&p=' . $i . '">' . $i . '</a>
              </li>';

        return '
          <nav aria-label="Page navigation">
            <ul class="pagination pagination-sm pull-right">
              <li class="page-item' . ($curr === 1 ? ' disabled' : '') . '">
                <a class="page-link" href="?o=' . $o . '&m=list&p=' . $prev . '" aria-label="Previous">
                  <span aria-hidden="true">&laquo;</span>
                  <span class="sr-only">Previous</span>
                </a>
              </li>' . $b . '
              <li class="page-item' . ($curr === $last ? ' disabled' : '') . '">
                <a class="page-link" href="?o=' . $o . '&m=list&p=' . $next . '" aria-label="Next">
                  <span aria-hidden="true">&raquo;</span>
                  <span class="sr-only">Next</span>
                </a>
              </li>
            </ul>
          </nav>';
    }
}

?>
