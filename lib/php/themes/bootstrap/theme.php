<?php

declare(strict_types=1);
// lib/php/themes/bootstrap/theme.php 20150101 - 20230625
// Copyright (C) 2015-2023 Mark Constable <markc@renta.net> (AGPL-3.0)

class Themes_Bootstrap_Theme extends Theme
{
    public function css(): string
    {
        return '
    <meta name="theme-color" content="#712cf9">
<link href="lib/css/bootstrap.min.css" rel="stylesheet">
<!--
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">
-->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <style>
        body {
            min-height: 75rem;
            /* padding-top: 5rem; */
        }

        .bi {
            margin-left: .25rem;
            margin-right: .25rem;
        }

        .navbar-brand {
            padding-top: 0;
        }

        .navbar-toggler {
            font-size: 1rem;
        }

        .bd-placeholder-img {
            font-size: 1.125rem;
            text-anchor: middle;
            -webkit-user-select: none;
            -moz-user-select: none;
            user-select: none;
        }

        @media (min-width: 768px) {
            .bd-placeholder-img-lg {
                font-size: 3.5rem;
            }
        }

        .b-example-divider {
            width: 100%;
            height: 3rem;
            background-color: rgba(0, 0, 0, .1);
            border: solid rgba(0, 0, 0, .15);
            border-width: 1px 0;
            box-shadow: inset 0 .5em 1.5em rgba(0, 0, 0, .1), inset 0 .125em .5em rgba(0, 0, 0, .15);
        }

        .b-example-vr {
            flex-shrink: 0;
            width: 1.5rem;
            height: 100vh;
        }

        .bi {
            /* vertical-align: 0.01em; */
            fill: currentColor;
        }

        .nav-scroller {
            position: relative;
            z-index: 2;
            height: 2.75rem;
            overflow-y: hidden;
        }

        .nav-scroller .nav {
            display: flex;
            flex-wrap: nowrap;
            padding-bottom: 1rem;
            margin-top: -1px;
            overflow-x: auto;
            text-align: center;
            white-space: nowrap;
            -webkit-overflow-scrolling: touch;
        }

        .btn-bd-primary {
            --bd-violet-bg: #712cf9;
            --bd-violet-rgb: 112.520718, 44.062154, 249.437846;

            --bs-btn-font-weight: 600;
            --bs-btn-color: var(--bs-white);
            --bs-btn-bg: var(--bd-violet-bg);
            --bs-btn-border-color: var(--bd-violet-bg);
            --bs-btn-hover-color: var(--bs-white);
            --bs-btn-hover-bg: #6528e0;
            --bs-btn-hover-border-color: #6528e0;
            --bs-btn-focus-shadow-rgb: var(--bd-violet-rgb);
            --bs-btn-active-color: var(--bs-btn-hover-color);
            --bs-btn-active-bg: #5a23c8;
            --bs-btn-active-border-color: #5a23c8;
        }

        .bd-mode-toggle {
            z-index: 1500;
        }

        .navbar-scrolled {
            padding-top: 0.25rem !important;
            padding-bottom: 0.25rem !important;
            /* transition: padding 0.25s; */
        }
    </style>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"
		integrity="sha256-2Pmvv0kuTBOenSvLm6bvfBSSHrUJ+3A7x6P5Ebd07/g="
		crossorigin="anonymous">
    </script>
    ';
    }

    public function head(): string
    {
        return '
    <svg xmlns="http://www.w3.org/2000/svg" style="display: none;">
      <symbol id="check2" viewBox="0 0 16 16">
        <path d="M13.854 3.646a.5.5 0 0 1 0 .708l-7 7a.5.5 0 0 1-.708 0l-3.5-3.5a.5.5 0 1 1 .708-.708L6.5 10.293l6.646-6.647a.5.5 0 0 1 .708 0z"/>
      </symbol>
      <symbol id="circle-half" viewBox="0 0 16 16">
        <path d="M8 15A7 7 0 1 0 8 1v14zm0 1A8 8 0 1 1 8 0a8 8 0 0 1 0 16z"/>
      </symbol>
      <symbol id="moon-stars-fill" viewBox="0 0 16 16">
        <path d="M6 .278a.768.768 0 0 1 .08.858 7.208 7.208 0 0 0-.878 3.46c0 4.021 3.278 7.277 7.318 7.277.527 0 1.04-.055 1.533-.16a.787.787 0 0 1 .81.316.733.733 0 0 1-.031.893A8.349 8.349 0 0 1 8.344 16C3.734 16 0 12.286 0 7.71 0 4.266 2.114 1.312 5.124.06A.752.752 0 0 1 6 .278z"/>
        <path d="M10.794 3.148a.217.217 0 0 1 .412 0l.387 1.162c.173.518.579.924 1.097 1.097l1.162.387a.217.217 0 0 1 0 .412l-1.162.387a1.734 1.734 0 0 0-1.097 1.097l-.387 1.162a.217.217 0 0 1-.412 0l-.387-1.162A1.734 1.734 0 0 0 9.31 6.593l-1.162-.387a.217.217 0 0 1 0-.412l1.162-.387a1.734 1.734 0 0 0 1.097-1.097l.387-1.162zM13.863.099a.145.145 0 0 1 .274 0l.258.774c.115.346.386.617.732.732l.774.258a.145.145 0 0 1 0 .274l-.774.258a1.156 1.156 0 0 0-.732.732l-.258.774a.145.145 0 0 1-.274 0l-.258-.774a1.156 1.156 0 0 0-.732-.732l-.774-.258a.145.145 0 0 1 0-.274l.774-.258c.346-.115.617-.386.732-.732L13.863.1z"/>
      </symbol>
      <symbol id="sun-fill" viewBox="0 0 16 16">
        <path d="M8 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8zM8 0a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-1 0v-2A.5.5 0 0 1 8 0zm0 13a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-1 0v-2A.5.5 0 0 1 8 13zm8-5a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1 0-1h2a.5.5 0 0 1 .5.5zM3 8a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1 0-1h2A.5.5 0 0 1 3 8zm10.657-5.657a.5.5 0 0 1 0 .707l-1.414 1.415a.5.5 0 1 1-.707-.708l1.414-1.414a.5.5 0 0 1 .707 0zm-9.193 9.193a.5.5 0 0 1 0 .707L3.05 13.657a.5.5 0 0 1-.707-.707l1.414-1.414a.5.5 0 0 1 .707 0zm9.193 2.121a.5.5 0 0 1-.707 0l-1.414-1.414a.5.5 0 0 1 .707-.707l1.414 1.414a.5.5 0 0 1 0 .707zM4.464 4.465a.5.5 0 0 1-.707 0L2.343 3.05a.5.5 0 1 1 .707-.707l1.414 1.414a.5.5 0 0 1 0 .708z"/>
      </symbol>
    </svg>

    <div class="dropdown position-fixed bottom-0 end-0 mb-3 me-3 bd-mode-toggle">
      <button class="btn btn-bd-primary py-2 dropdown-toggle d-flex align-items-center"
              id="bd-theme"
              type="button"
              aria-expanded="false"
              data-bs-toggle="dropdown"
              aria-label="Toggle theme (auto)">
        <svg class="bi my-1 theme-icon-active" width="1em" height="1em"><use href="#circle-half"></use></svg>
        <span class="visually-hidden" id="bd-theme-text">Toggle theme</span>
      </button>
      <ul class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="bd-theme-text">
        <li>
          <button type="button" class="dropdown-item d-flex align-items-center" data-bs-theme-value="light" aria-pressed="false">
            <svg class="bi me-2 opacity-50 theme-icon" width="1em" height="1em"><use href="#sun-fill"></use></svg>
            Light
            <svg class="bi ms-auto d-none" width="1em" height="1em"><use href="#check2"></use></svg>
          </button>
        </li>
        <li>
          <button type="button" class="dropdown-item d-flex align-items-center" data-bs-theme-value="dark" aria-pressed="false">
            <svg class="bi me-2 opacity-50 theme-icon" width="1em" height="1em"><use href="#moon-stars-fill"></use></svg>
            Dark
            <svg class="bi ms-auto d-none" width="1em" height="1em"><use href="#check2"></use></svg>
          </button>
        </li>
        <li>
          <button type="button" class="dropdown-item d-flex align-items-center active" data-bs-theme-value="auto" aria-pressed="true">
            <svg class="bi me-2 opacity-50 theme-icon" width="1em" height="1em"><use href="#circle-half"></use></svg>
            Auto
            <svg class="bi ms-auto d-none" width="1em" height="1em"><use href="#check2"></use></svg>
          </button>
        </li>
      </ul>
    </div>

    <nav id="navbar" class="navbar mb-4 nav-underline navbar-expand-lg sticky-top">
        <div class="container">
            <a class="navbar-brand" href="' . $this->g->cfg['self'] . '">
                <b><i class="bi bi-box"></i> ' . $this->g->out['head'] . '</b>
                (' . $_SESSION['r'] . ')
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarCollapse"
                aria-controls="navbarCollapse" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarCollapse">
                <ul class="navbar-nav ms-auto">' . $this->g->out['nav1'] . '
                </ul>
                <ul class="navbar-nav ms-auto">' . $this->g->out['nav3'] . '
                </ul>
            </div>
        </div>
    </nav>';
    }

    public function nav1(array $a = []): string
    {
        $a = isset($a[0]) ? $a : util::get_nav($this->g->nav1);
        $o = '?o=' . $this->g->in['o'];
        $t = '?t=' . util::ses('t');

        return implode('', array_map(function ($n) use ($o, $t) {
            if (is_array($n[1])) {
                return $this->nav_dropdown($n);
            }
            $c = $o === $n[1] || $t === $n[1] ? ' active' : '';
            $i = isset($n[2]) ? '<i class="' . $n[2] . '"></i> ' : '';

            return '
            <li class="nav-item"><a class="nav-link' . $c . '" href="' . $n[1] . '">' . $i . $n[0] . '</a></li>';
        }, $a));
    }

    public function nav2(): string
    {
        return $this->nav_dropdown(['Sites', $this->g->nav2, 'bi bi-globe'], 'r');
    }

    public function nav3(): string
    {
        if (util::is_usr()) {
            $usr[] = ['Change Profile', '?o=accounts&m=read&i=' . $_SESSION['usr']['id'], 'bi bi-person'];
            $usr[] = ['Change Password', '?o=auth&m=update&i=' . $_SESSION['usr']['id'], 'bi bi-key'];
            $usr[] = ['Sign out', '?o=auth&m=delete', 'bi bi-box-arrow-right'];

            if (util::is_adm() && !util::is_acl(0)) {
                $usr[] =
                    ['Switch to sysadm', '?o=accounts&m=switch_user&i=' . $_SESSION['adm'], 'bi bi-person-fill'];
            }

            return $this->nav_dropdown([$_SESSION['usr']['login'], $usr, 'bi bi-person-fill']);
        }

        return '';
    }

    public function nav_dropdown(array $a = []): string
    {
        $o = "?o=" . $this->g->in['o'];
        $i = isset($a[2]) ? '<i class="' . $a[2] . '"></i> ' : '';

        return '
            <li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown" aria-expanded="false">' . $i . $a[0] . '</a>
              <div class="dropdown-menu">' . implode('', array_map(function ($n) use ($o) {
            //elog('n=' . var_export($n, true));
            $tmp = isset($n[3]) ? '?r=' . $this->g->in[$n[3]] : $o;
            $c = ($tmp === $n[1]) ? ' active' : '';
            $i = isset($n[2]) ? '<i class="' . $n[2] . '"></i> ' : '';

            return '
                <a class="dropdown-item' . $c . '" href="' . $n[1] . '">' . $i . $n[0] . '</a>';
        }, $a[1])) . '
              </div>
            </li>';
    }

    public function main(): string
    {
        return '
        <main class="container">' . $this->g->out['log'] . $this->g->out['main'] . '
        </main>';
    }

    public function js(): string
    {
        return '
    <script src="lib/js/bootstrap.bundle.min.js"></script>
    <script>
        var nav = document.getElementById("navbar");
        window.addEventListener("scroll", function () {
            let theme = document.documentElement.getAttribute("data-bs-theme");
            //console.log(theme);
            if (window.pageYOffset > 24) {
                if (theme == "dark") {
                    nav.classList.add("bg-dark", "shadow", "navbar-scrolled");
                    nav.style.transition = "padding 0.25s";
                } else {
                    nav.classList.add("bg-light", "shadow", "navbar-scrolled");
                    nav.style.transition = "padding 0.25s";
                }
            } else {
                if (theme == "dark") {
                    nav.classList.remove("bg-dark", "shadow", "navbar-scrolled");
                    nav.style.transition = "padding 0.25s";
                } else {
                    nav.classList.remove("bg-light", "shadow", "navbar-scrolled");
                    nav.style.transition = "padding 0.25s";
                }
            }
        });
    </script>

    <!--
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>
    -->
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    ';
    }

    public function log(): string
    {
        $logs = '';
        foreach (util::log() as $lvl => $msg) {
            $logs .= $msg ? '
            <div class="row">
              <div class="col">
                <div class="alert alert-' . $lvl . ' alert-dismissible fade show" role="alert">
                  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">
                  </button>' . rtrim($msg) . '
                </div>
              </div>
            </div>' : '';
        }

        return $logs;
    }

    protected function modal(array $ary): string
    {
        return '
        <div class="modal fade" id="' . $ary['id'] . '" tabindex="-1" role="dialog" aria-labelledby="' . $ary['id'] . '" aria-hidden="true">
          <div class="modal-dialog">' . $this->modal_content($ary) . '
          </div>
        </div>';
    }

    protected function modal_content(array $ary): string
    {
        extract($ary);

        $action = empty($action) ? '' : $action;
        $hidden = empty($hidden) ? '' : $hidden;
        $lhs_cmd = empty($lhs_cmd) ? '' : '
                <a class="btn btn-danger bslink" href="?o=' . $this->g->in['o'] . '&m=delete&i=' . $this->g->in['i'] . '">' . $lhs_cmd . '</a>';
        $mid_cmd = empty($mid_cmd) ? '' : '
                <a class="btn btn-info bslink" href="?o=' . $this->g->in['o'] . '&m=help&name=' . $this->g->in['m'] . '">' . $mid_cmd . '</a>';
        $footer = empty($rhs_cmd) ? '' : '
              <div class="modal-footer d-flex justify-content-between">' . $lhs_cmd . '
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>' . $mid_cmd . '
                <button type="submit" class="btn btn-primary">' . $rhs_cmd . '</button>
              </div>';
        $body = '
                <div class="modal-body">' . $body . '
                </div>';
        $body_buf = empty($footer) ? $body : '
              <form method="post" action="' . $this->g->cfg['self'] . '">
                <input type="hidden" name="c" value="' . $_SESSION['c'] . '">
                <input type="hidden" name="o" value="' . $this->g->in['o'] . '">
                <input type="hidden" name="m" value="' . $action . '">
                <input type="hidden" name="i" value="' . $this->g->in['i'] . '">'
            . $hidden . $body . $footer . '
              </form>';

        return '
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title">' . $title . '</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                </button>
              </div>' . $body_buf . '
            </div>';
    }
}
