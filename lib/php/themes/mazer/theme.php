<?php

declare(strict_types=1);
// lib/php/themes/mazer/theme.php 20150101 - 20230605
// Copyright (C) 2015-2023 Mark Constable <markc@renta.net> (AGPL-3.0)

class Themes_Mazer_Theme extends Theme
{
    public function css(): string
    {
        elog(__METHOD__);

        return '
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>DataTable - Mazer Admin Dashboard</title>

    <link rel="shortcut icon" href="lib/img/favicon.svg" type="image/x-icon" />
    <link rel="shortcut icon"
        href="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACEAAAAiCAYAAADRcLDBAAAEs2lUWHRYTUw6Y29tLmFkb2JlLnhtcAAAAAAAPD94cGFja2V0IGJlZ2luPSLvu78iIGlkPSJXNU0wTXBDZWhpSHpyZVN6TlRjemtjOWQiPz4KPHg6eG1wbWV0YSB4bWxuczp4PSJhZG9iZTpuczptZXRhLyIgeDp4bXB0az0iWE1QIENvcmUgNS41LjAiPgogPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4KICA8cmRmOkRlc2NyaXB0aW9uIHJkZjphYm91dD0iIgogICAgeG1sbnM6ZXhpZj0iaHR0cDovL25zLmFkb2JlLmNvbS9leGlmLzEuMC8iCiAgICB4bWxuczp0aWZmPSJodHRwOi8vbnMuYWRvYmUuY29tL3RpZmYvMS4wLyIKICAgIHhtbG5zOnBob3Rvc2hvcD0iaHR0cDovL25zLmFkb2JlLmNvbS9waG90b3Nob3AvMS4wLyIKICAgIHhtbG5zOnhtcD0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wLyIKICAgIHhtbG5zOnhtcE1NPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvbW0vIgogICAgeG1sbnM6c3RFdnQ9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9zVHlwZS9SZXNvdXJjZUV2ZW50IyIKICAgZXhpZjpQaXhlbFhEaW1lbnNpb249IjMzIgogICBleGlmOlBpeGVsWURpbWVuc2lvbj0iMzQiCiAgIGV4aWY6Q29sb3JTcGFjZT0iMSIKICAgdGlmZjpJbWFnZVdpZHRoPSIzMyIKICAgdGlmZjpJbWFnZUxlbmd0aD0iMzQiCiAgIHRpZmY6UmVzb2x1dGlvblVuaXQ9IjIiCiAgIHRpZmY6WFJlc29sdXRpb249Ijk2LjAiCiAgIHRpZmY6WVJlc29sdXRpb249Ijk2LjAiCiAgIHBob3Rvc2hvcDpDb2xvck1vZGU9IjMiCiAgIHBob3Rvc2hvcDpJQ0NQcm9maWxlPSJzUkdCIElFQzYxOTY2LTIuMSIKICAgeG1wOk1vZGlmeURhdGU9IjIwMjItMDMtMzFUMTA6NTA6MjMrMDI6MDAiCiAgIHhtcDpNZXRhZGF0YURhdGU9IjIwMjItMDMtMzFUMTA6NTA6MjMrMDI6MDAiPgogICA8eG1wTU06SGlzdG9yeT4KICAgIDxyZGY6U2VxPgogICAgIDxyZGY6bGkKICAgICAgc3RFdnQ6YWN0aW9uPSJwcm9kdWNlZCIKICAgICAgc3RFdnQ6c29mdHdhcmVBZ2VudD0iQWZmaW5pdHkgRGVzaWduZXIgMS4xMC4xIgogICAgICBzdEV2dDp3aGVuPSIyMDIyLTAzLTMxVDEwOjUwOjIzKzAyOjAwIi8+CiAgICA8L3JkZjpTZXE+CiAgIDwveG1wTU06SGlzdG9yeT4KICA8L3JkZjpEZXNjcmlwdGlvbj4KIDwvcmRmOlJERj4KPC94OnhtcG1ldGE+Cjw/eHBhY2tldCBlbmQ9InIiPz5V57uAAAABgmlDQ1BzUkdCIElFQzYxOTY2LTIuMQAAKJF1kc8rRFEUxz9maORHo1hYKC9hISNGTWwsRn4VFmOUX5uZZ36oeTOv954kW2WrKLHxa8FfwFZZK0WkZClrYoOe87ypmWTO7dzzud97z+nec8ETzaiaWd4NWtYyIiNhZWZ2TvE946WZSjqoj6mmPjE1HKWkfdxR5sSbgFOr9Ll/rXoxYapQVik8oOqGJTwqPL5i6Q5vCzeo6dii8KlwpyEXFL519LjLLw6nXP5y2IhGBsFTJ6ykijhexGra0ITl5bRqmWU1fx/nJTWJ7PSUxBbxJkwijBBGYYwhBgnRQ7/MIQIE6ZIVJfK7f/MnyUmuKrPOKgZLpEhj0SnqslRPSEyKnpCRYdXp/9++msneoFu9JgwVT7b91ga+LfjetO3PQ9v+PgLvI1xkC/m5A+h7F32zoLXug38dzi4LWnwHzjeg8UGPGbFfySvuSSbh9QRqZ6H+Gqrm3Z7l9zm+h+iafNUV7O5Bu5z3L/wAdthn7QIme0YAAAAJcEhZcwAADsQAAA7EAZUrDhsAAAJTSURBVFiF7Zi9axRBGIefEw2IdxFBRQsLWUTBaywSK4ubdSGVIY1Y6HZql8ZKCGIqwX/AYLmCgVQKfiDn7jZeEQMWfsSAHAiKqPiB5mIgELWYOW5vzc3O7niHhT/YZvY37/swM/vOzJbIqVq9uQ04CYwCI8AhYAlYAB4Dc7HnrOSJWcoJcBS4ARzQ2F4BZ2LPmTeNuykHwEWgkQGAet9QfiMZjUSt3hwD7psGTWgs9pwH1hC1enMYeA7sKwDxBqjGnvNdZzKZjqmCAKh+U1kmEwi3IEBbIsugnY5avTkEtIAtFhBrQCX2nLVehqyRqFoCAAwBh3WGLAhbgCRIYYinwLolwLqKUwwi9pxV4KUlxKKKUwxC6ZElRCPLYAJxGfhSEOCz6m8HEXvOB2CyIMSk6m8HoXQTmMkJcA2YNTHm3congOvATo3tE3A29pxbpnFzQSiQPcB55IFmFNgFfEQeahaAGZMpsIJIAZWAHcDX2HN+2cT6r39GxmvC9aPNwH5gO1BOPFuBVWAZue0vA9+A12EgjPadnhCuH1WAE8ivYAQ4ohKaagV4gvxi5oG7YSA2vApsCOH60WngKrA3R9IsvQUuhIGY00K4flQG7gHH/mLytB4C42EgfrQb0mV7us8AAMeBS8mGNMR4nwHamtBB7B4QRNdaS0M8GxDEog7iyoAguvJ0QYSBuAOcAt71Kfl7wA8DcTvZ2KtOlJEr+ByyQtqqhTyHTIeB+ONeqi3brh+VgIN0fohUgWGggizZFTplu12yW8iy/YLOGWMpDMTPXnl+Az9vj2HERYqPAAAAAElFTkSuQmCC"
        type="image/png" />

    <link rel="stylesheet" href="lib/css/style.css" />

    <link rel="stylesheet" href="lib/css/table-datatable.css" />
    <link rel="stylesheet" href="lib/css/app.css" />
    <link rel="stylesheet" href="lib/css/app-dark.css" />
';
    }

    public function log(): string
    {
        elog(__METHOD__);

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

    public function head(): string
    {
        elog(__METHOD__);

        return '';
    }

    public function head_orig(): string
    {
        elog(__METHOD__);

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

    public function nav1(): string
    {
        elog(__METHOD__);

        return $this->sidebar();
    }

    public function nav2_orig(): string
    {
        elog(__METHOD__);

        return $this->nav_dropdown(['Theme', $this->g->nav2, 'fa fa-th fa-fw']);
    }

    public function nav3_orig(): string
    {
        elog(__METHOD__);

        if (util::is_usr()) {
            $usr[] = ['Change Profile', '?o=accounts&m=read&i=' . $_SESSION['usr']['id'], 'fas fa-user fa-fw'];
            $usr[] = ['Change Password', '?o=auth&m=update&i=' . $_SESSION['usr']['id'], 'fas fa-key fa-fw'];
            $usr[] = ['Sign out', '?o=auth&m=delete', 'fas fa-sign-out-alt fa-fw'];

            if (util::is_adm() && !util::is_acl(0)) {
                $usr[] =
                    ['Switch to sysadm', '?o=accounts&m=switch_user&i=' . $_SESSION['adm'], 'fas fa-user fa-fw'];
            }

            return $this->nav_dropdown([$_SESSION['usr']['login'], $usr, 'fas fa-user fa-fw']);
        }

        return '';
    }

    public function nav_dropdown(array $a = []): string
    {
        elog(__METHOD__);

        $o = '?o=' . $this->g->in['o'];
        $i = isset($a[2]) ? '<i class="' . $a[2] . '"></i> ' : '';

        return '
            <li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle" href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">' . $i . $a[0] . '</a>
              <div class="dropdown-menu">' . implode('', array_map(function ($n) use ($o) {
            $c = $o === $n[1] ? ' active' : '';
            $i = isset($n[2]) ? '<i class="' . $n[2] . '"></i> ' : '';

            return '
                <a class="dropdown-item" href="' . $n[1] . '">' . $i . $n[0] . '</a>';
        }, $a[1])) . '
              </div>
            </li>';
    }

    public function sidebar(): string
    {
        elog(__METHOD__);

        $a = isset($a[0]) ? $a : util::get_nav($this->g->nav1);
        $o = '?o=' . $this->g->in['o'];
        $t = '?t=' . util::ses('t');
        //elog(var_export($a, true));
        //return implode('', array_map(function ($n) use ($o, $t) {
        $buf = implode('', array_map(function ($n) use ($o, $t) {
            if (is_array($n[1])) {
                return $this->sidebar_submenu($n);
            }
            $c = $o === $n[1] || $t === $n[1] ? ' active' : '';
            $i = isset($n[2]) ? '<i class="' . $n[2] . '"></i> ' : '';

            return '
            <li class="sidebar-item ' . $c . '">
                <a href="' . $n[1] . '" class="sidebar-link">' . $i . '
                    <span>' . $n[0] . '</span>
                </a>
            </li>';
        }, $a));
        //elog($buf);
        return $buf;
    }

    public function sidebar_submenu(array $a = []): string
    {
        elog(__METHOD__);
        elog(var_export($a, true));

        $o = '?o=' . $this->g->in['o'];
        $i = isset($a[2]) ? '<i class="' . $a[2] . '"></i> ' : '';

        return '
                        <li class="sidebar-item has-sub">
                            <a href="#" class="sidebar-link">
                                <i class="bi bi-stack"></i>
                                <span>' . $a[0] . '</span>
                            </a>
                            <ul class="submenu">
                            ' . implode('', array_map(function ($n) use ($o) {
            $c = $o === $n[1] ? ' active' : '';
            $i = isset($n[2]) ? '<i class="' . $n[2] . '"></i> ' : '';

            return '
                                <li class="submenu-item' . $c . '">
                                    <a href="' . $n[1] . '" class="submenu-link">' . $i . $n[0] . '</a>
                                </li>';
        }, $a[1])) . '
                            </ul>
                        </li>';
    }


    public function main(): string
    {
        elog(__METHOD__);

        return '
    <script src="lib/js/initTheme.js"></script>
    <div id="app">
        <div id="sidebar">
            <div class="sidebar-wrapper active">
                <div class="sidebar-header position-relative">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="logo">
                            <a href="index.html"><img src="lib/img/logo.svg" alt="Logo" srcset="" /></a>
                        </div>
                        <div class="theme-toggle d-flex gap-2 align-items-center mt-2">
                            <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"
                                aria-hidden="true" role="img" class="iconify iconify--system-uicons" width="20"
                                height="20" preserveAspectRatio="xMidYMid meet" viewBox="0 0 21 21">
                                <g fill="none" fill-rule="evenodd" stroke="currentColor" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <path
                                        d="M10.5 14.5c2.219 0 4-1.763 4-3.982a4.003 4.003 0 0 0-4-4.018c-2.219 0-4 1.781-4 4c0 2.219 1.781 4 4 4zM4.136 4.136L5.55 5.55m9.9 9.9l1.414 1.414M1.5 10.5h2m14 0h2M4.135 16.863L5.55 15.45m9.899-9.9l1.414-1.415M10.5 19.5v-2m0-14v-2"
                                        opacity=".3"></path>
                                    <g transform="translate(-210 -1)">
                                        <path d="M220.5 2.5v2m6.5.5l-1.5 1.5"></path>
                                        <circle cx="220.5" cy="11.5" r="4"></circle>
                                        <path d="m214 5l1.5 1.5m5 14v-2m6.5-.5l-1.5-1.5M214 18l1.5-1.5m-4-5h2m14 0h2">
                                        </path>
                                    </g>
                                </g>
                            </svg>
                            <div class="form-check form-switch fs-6">
                                <input class="form-check-input me-0" type="checkbox" id="toggle-dark"
                                    style="cursor: pointer" />
                                <label class="form-check-label"></label>
                            </div>
                            <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"
                                aria-hidden="true" role="img" class="iconify iconify--mdi" width="20" height="20"
                                preserveAspectRatio="xMidYMid meet" viewBox="0 0 24 24">
                                <path fill="currentColor"
                                    d="m17.75 4.09l-2.53 1.94l.91 3.06l-2.63-1.81l-2.63 1.81l.91-3.06l-2.53-1.94L12.44 4l1.06-3l1.06 3l3.19.09m3.5 6.91l-1.64 1.25l.59 1.98l-1.7-1.17l-1.7 1.17l.59-1.98L15.75 11l2.06-.05L18.5 9l.69 1.95l2.06.05m-2.28 4.95c.83-.08 1.72 1.1 1.19 1.85c-.32.45-.66.87-1.08 1.27C15.17 23 8.84 23 4.94 19.07c-3.91-3.9-3.91-10.24 0-14.14c.4-.4.82-.76 1.27-1.08c.75-.53 1.93.36 1.85 1.19c-.27 2.86.69 5.83 2.89 8.02a9.96 9.96 0 0 0 8.02 2.89m-1.64 2.02a12.08 12.08 0 0 1-7.8-3.47c-2.17-2.19-3.33-5-3.49-7.82c-2.81 3.14-2.7 7.96.31 10.98c3.02 3.01 7.84 3.12 10.98.31Z">
                                </path>
                            </svg>
                        </div>
                        <div class="sidebar-toggler x">
                            <a href="#" class="sidebar-hide d-xl-none d-block"><i class="bi bi-x bi-middle"></i></a>
                        </div>
                    </div>
                </div>
                <div class="sidebar-menu">
                    <ul class="menu">' . $this->g->out['nav1'] . '
<!--
                        <li class="sidebar-item">
                            <a href="/hcp/" class="sidebar-link">
                                <i class="bi bi-grid-fill"></i>
                                <span>Home</span>
                            </a>
                        </li>

                        <li class="sidebar-item has-sub">
                            <a href="#" class="sidebar-link">
                                <i class="bi bi-stack"></i>
                                <span>Admin</span>
                            </a>

                            <ul class="submenu">
                                <li class="submenu-item">
                                    <a href="?o=accounts" class="submenu-link">Accounts</a>
                                </li>

                                <li class="submenu-item">
                                    <a href="?o=vhosts" class="submenu-link">Vhosts</a>
                                </li>

                                <li class="submenu-item">
                                    <a href="?o=vmails" class="submenu-link">Mailboxes</a>
                                </li>

                                <li class="submenu-item">
                                    <a href="?o=valias" class="submenu-link">Aliases</a>
                                </li>

                                <li class="submenu-item">
                                    <a href="?o=dkim" class="submenu-link">DKIM</a>
                                </li>

                                <li class="submenu-item">
                                    <a href="?o=domains" class="submenu-link">Domains</a>
                                </li>
                            </ul>
                        </li>
-->
                    </ul>
                </div>
            </div>
        </div>
        <div id="main">
            <header class="mb-3">
                <a href="#" class="burger-btn d-block d-xl-none">
                    <i class="bi bi-justify fs-3"></i>
                </a>
            </header>

            <div class="page-heading">
                <div class="page-title">
                    <div class="row">
                        <div class="col-12 col-md-6 order-md-1 order-last">
                            <h3>DataTable</h3>
                            <p class="text-subtitle text-muted">
                                A sortable, searchable, paginated table without dependencies
                                thanks to simple-datatables.
                            </p>
                        </div>
                        <div class="col-12 col-md-6 order-md-2 order-first">
                            <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item">
                                        <a href="index.html">Dashboard</a>
                                    </li>
                                    <li class="breadcrumb-item active" aria-current="page">
                                        DataTable
                                    </li>
                                </ol>
                            </nav>
                        </div>
                    </div>
                </div>
                <section class="section">
                    <div class="card">
                        <div class="card-header">Simple Datatable</div>
                        <div class="card-body">
                        ' . $this->g->out['log'] . $this->g->out['main'] . '
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>    
    ';
    }

    public function main_orig(): string
    {
        elog(__METHOD__);

        return '
    <main class="container">
      <div class="row">' . $this->g->out['log'] . $this->g->out['main'] . '
      </div>
    </main>';
    }

    public function foot(): string
    {
        elog(__METHOD__);

        return '
            <footer>
                <div class="footer clearfix mb-0 text-muted">
                    <div class="float-start">
                        <p>2023 &copy; Mazer</p>
                    </div>
                    <div class="float-end">
                        <p>
                            Crafted with
                            <span class="text-danger"><i class="bi bi-heart-fill icon-mid"></i></span>
                            by <a href="https://saugi.me">Saugi</a>
                        </p>
                    </div>
                </div>
            </footer>
    ';
    }

    public function js(): string
    {
        elog(__METHOD__);

        return '
    <script src="lib/js/dark.js"></script>
    <script src="lib/js/perfect-scrollbar.min.js"></script>
    <script src="lib/js/app.js"></script>
    <script src="lib/js/simple-umd-datatables.js"></script>
    <script src="lib/js/simple-datatables.js"></script>
    ';
    }

    protected function modal(array $ary): string
    {
        elog(__METHOD__);

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
                <input type="hidden" name="i" value="' . $this->g->in['i'] . '">' . $hidden . '
                <div class="modal-body">' . $body . '
                </div>' . $footer . '
              </form>
            </div>
          </div>
        </div>';
    }
}
