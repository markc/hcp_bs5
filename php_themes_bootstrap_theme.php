<?php

declare(strict_types=1);

// lib/php/themes/bootstrap/theme.php 20150101 - 20240904
// Copyright (C) 2015-2024 Mark Constable <markc@renta.net> (AGPL-3.0)

class Themes_Bootstrap_Theme extends Theme
{
    public function css(): string
    {
        return $this->getCssContent();
    }

    public function head(): string
    {
        return $this->getHeadContent();
    }

    public function nav1(array $a = []): string
    {
        $a = isset($a[0]) ? $a : util::get_nav($this->g->nav1);
        $o = '?o=' . $this->g->in['o'];
        $t = '?t=' . util::ses('t');

        return implode('', array_map(fn($n) => $this->generateNavItem($n, $o, $t), $a));
    }

    public function nav2(): string
    {
        return $this->nav_dropdown(['Sites', $this->g->nav2, 'bi bi-globe'], 'r');
    }

    public function nav3(): string
    {
        if (!util::is_usr()) {
            return '';
        }

        $usr = $this->getUserNavItems();
        return $this->nav_dropdown([$_SESSION['usr']['login'], $usr, 'bi bi-person-fill']);
    }

    public function nav_dropdown(array $a = []): string
    {
        $o = "?o=" . $this->g->in['o'];
        $i = isset($a[2]) ? "<i class=\"{$a[2]}\"></i> " : '';

        $dropdownItems = implode('', array_map(fn($n) => $this->generateDropdownItem($n, $o), $a[1]));

        return $this->generateDropdownHtml($a[0], $i, $dropdownItems);
    }

    public function main(): string
    {
        return "<main class=\"container\">{$this->g->out['log']}{$this->g->out['main']}</main>";
    }

    public function js(): string
    {
        return $this->getJsContent();
    }

    public function log(): string
    {
        $logs = '';
        foreach (util::log() as $lvl => $msg) {
            if (is_array($msg) && !empty($msg)) {
                foreach ($msg as $text) {
                    $logs .= $this->generateLogAlert($lvl, $text);
                }
            }
        }
        return $logs;
    }

    protected function modal(array $ary): string
    {
        return $this->generateModalHtml($ary);
    }

    protected function modal_content(array $ary): string
    {
        extract($ary);

        $action     = $action ?? '';
        $hidden     = $hidden ?? '';
        $lhs_cmd    = $this->generateModalCommand($lhs_cmd ?? '', 'danger', 'delete');
        $mid_cmd    = $this->generateModalCommand($mid_cmd ?? '', 'info', 'help');
        $footer     = $this->generateModalFooter($rhs_cmd ?? '', $lhs_cmd, $mid_cmd);
        $body       = $this->generateModalBody($body, $footer, $action, $hidden);

        return $this->generateModalContent($title, $body);
    }

    private function getCssContent(): string
    {
        return "";
    }

    private function getHeadContent(): string
    {
        return <<<HTML
<!-- Bootstrap 5 CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<!-- DataTables Bootstrap 5 CSS -->
<link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">

<!-- DataTables Buttons CSS -->
<link href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.bootstrap5.min.css" rel="stylesheet">

<!-- Bootstrap 5 JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- jQuery (still required for DataTables) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- DataTables JavaScript -->
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>

<!-- DataTables Buttons JavaScript -->
<script src="https://cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.bootstrap5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.print.min.js"></script>
HTML;
    }

    private function getJsContent(): string
    {
        return "";
    }

    private function generateNavItem(array $n, string $o, string $t): string
    {
        if (is_array($n[1])) {
            return $this->nav_dropdown($n);
        }
        $c = $o === $n[1] || $t === $n[1] ? ' active' : '';
        $i = isset($n[2]) ? "<i class=\"{$n[2]}\"></i> " : '';
        return "<li class=\"nav-item\"><a class=\"nav-link{$c}\" href=\"{$n[1]}\">{$i}{$n[0]}</a></li>";
    }

    private function getUserNavItems(): array
    {
        $usr = [
            ['Change Profile', "?o=accounts&m=read&i={$_SESSION['usr']['id']}", 'bi bi-person'],
            ['Change Password', "?o=auth&m=update&i={$_SESSION['usr']['id']}", 'bi bi-key'],
            ['Sign out', '?o=auth&m=delete', 'bi bi-box-arrow-right']
        ];

        if (util::is_adm() && !util::is_acl(0)) {
            $usr[] = ['Switch to sysadm', "?o=accounts&m=switch_user&i={$_SESSION['adm']}", 'bi bi-person-fill'];
        }

        return $usr;
    }

    private function generateDropdownItem(array $n, string $o): string
    {
        $tmp = isset($n[3]) ? "?r={$this->g->in[$n[3]]}" : $o;
        $c = ($tmp === $n[1]) ? ' active' : '';
        $i = isset($n[2]) ? "<i class=\"{$n[2]}\"></i> " : '';
        return "<a class=\"dropdown-item{$c}\" href=\"{$n[1]}\">{$i}{$n[0]}</a>";
    }

    private function generateDropdownHtml(string $label, string $icon, string $items): string
    {
        return <<<HTML
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown" aria-expanded="false">{$icon}{$label}</a>
          <div class="dropdown-menu">{$items}</div>
        </li>
        HTML;
    }

    private function generateLogAlert(string $level, string $message): string
    {
        $escapedMessage = htmlspecialchars($message);
        return <<<HTML
        <div class="row">
          <div class="col">
            <div class="alert alert-{$level} alert-dismissible fade show" role="alert">
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
              {$escapedMessage}
            </div>
          </div>
        </div>
        HTML;
    }

    private function generateModalHtml(array $ary): string
    {
        $id = $ary['id'];
        $content = $this->modal_content($ary);
        return <<<HTML
        <div class="modal fade" id="{$id}" tabindex="-1" role="dialog" aria-labelledby="{$id}" aria-hidden="true">
          <div class="modal-dialog">{$content}</div>
        </div>
        HTML;
    }

    private function generateModalCommand(string $cmd, string $class, string $action): string
    {
        if (empty($cmd)) {
            return '';
        }
        return "<a class=\"btn btn-{$class} bslink\" href=\"?o={$this->g->in['o']}&m={$action}&i={$this->g->in['i']}\">{$cmd}</a>";
    }

    private function generateModalFooter(string $rhs_cmd, string $lhs_cmd, string $mid_cmd): string
    {
        if (empty($rhs_cmd)) {
            return '';
        }
        return <<<HTML
        <div class="modal-footer d-flex justify-content-between">{$lhs_cmd}
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>{$mid_cmd}
          <button type="submit" class="btn btn-primary">{$rhs_cmd}</button>
        </div>
        HTML;
    }

    private function generateModalBody(string $body, string $footer, string $action, string $hidden): string
    {
        $bodyContent = "<div class=\"modal-body\">{$body}</div>";
        if (empty($footer)) {
            return $bodyContent;
        }
        return <<<HTML
        <form method="post" action="{$this->g->cfg['self']}">
          <input type="hidden" name="c" value="{$_SESSION['c']}">
          <input type="hidden" name="o" value="{$this->g->in['o']}">
          <input type="hidden" name="m" value="{$action}">
          <input type="hidden" name="i" value="{$this->g->in['i']}">
          {$hidden}{$bodyContent}{$footer}
        </form>
        HTML;
    }

    private function generateModalContent(string $title, string $body): string
    {
        return <<<HTML
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">{$title}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>{$body}
        </div>
        HTML;
    }
}

/*
    // Original CSS/Bootstrap functions FTR

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
*/
