<?php

declare(strict_types=1);

// lib/php/themes/bootstrap5/theme.php 20150101 - 20240908
// Copyright (C) 2015-2024 Mark Constable <markc@renta.net> (AGPL-3.0)

class Themes_Bootstrap5_Theme extends Theme
{
    public function css(): string
    {
elog(__METHOD__);

        $self = json_encode($this->g->cfg['self']);
elog("self = $self");
        return <<<HTML
    <link href="/hcp/favicon.ico" rel="icon" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
    body {
        padding-left: 250px;
    }
    .sidebar {
        position: fixed;
        top: 0;
        bottom: 0;
        left: 0;
        z-index: 100;
        padding: 0;
        box-shadow: inset -1px 0 0 rgba(0, 0, 0, .1);
        width: 250px;
        transition: margin .25s ease-out;
    }
    .sidebar-brand {
        padding: 1rem;
        font-size: 1.25rem;
        font-weight: bold;
        color: #fff;
        background-color: rgba(0, 0, 0, .25);
    }
    .sidebar .nav-link {
        font-weight: 500;
        color: #adb5bd;
        padding: .5rem 1rem;
    }
    .sidebar .nav-link:hover {
        color: #fff;
        background-color: rgba(255,255,255,.1);
    }
    .sidebar .nav-link.active {
        color: #fff;
    }
    .sidebar-heading {
        font-size: .75rem;
        text-transform: uppercase;
    }
    .sidebar .collapse .nav-link {
        padding-left: 2rem;
    }
    main {
        padding-top: 1rem;
    }
    @media (max-width: 767.98px) {
        body {
            padding-left: 0;
        }
        .sidebar {
            margin-left: -250px;
        }
        body.sb-sidenav-toggled .sidebar {
            margin-left: 0;
        }
        body.sb-sidenav-toggled {
            padding-left: 250px;
        }
    }
    .table-responsive { 
        width: 100%;
    }
    .table {
        width: 100% !important;
        min-width: 1100px;
    }
    ul.pagination {
        padding-top: 1rem;
    }
    div.dataTables_wrapper div.dataTables_info {
        padding-top: 1.6rem;
    }
    </style>
    <script>
    window.hcpConfig = {
        selfUrl: {$self}
    };
    </script>
    HTML;
    }

    public function nav1(array $a = []): string
    {
elog(__METHOD__);

        $a = isset($a[0]) ? $a : util::get_nav($this->g->nav1);
        $o = '?o=' . $this->g->in['o'];
        $t = '?t=' . util::ses('t');

        $navItems = '';
        foreach ($a as $n) {
            if (is_array($n[1])) {
                $navItems .= $this->generateNavDropdown($n, $o, $t);
            } else {
                $navItems .= $this->generateNavItem($n, $o, $t);
            }
        }

        return $navItems;
    }

    private function generateNavDropdown(array $n, string $o, string $t): string
    {
elog(__METHOD__);

        $id = strtolower(str_replace(' ', '', $n[0]));
        $icon = isset($n[2]) ? "<i class=\"{$n[2]}\"></i> " : '';
        $items = '';
        foreach ($n[1] as $subItem) {
            $items .= $this->generateNavItem($subItem, $o, $t, 'nav-link');
        }

        return <<<HTML
        <li class="nav-item">
            <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="collapse" data-bs-target="#{$id}Submenu" aria-expanded="false">
                {$icon}{$n[0]}
            </a>
            <div class="collapse" id="{$id}Submenu">
                <ul class="nav flex-column">
                    {$items}
                </ul>
            </div>
        </li>
        HTML;
    }

    private function generateNavItem(array $n, string $o, string $t, string $class = 'nav-link'): string
    {
elog(__METHOD__);

        $c = $o === $n[1] || $t === $n[1] ? ' active' : '';
        $i = isset($n[2]) ? "<i class=\"{$n[2]}\"></i> " : '';
        return "<li class=\"nav-item\"><a class=\"{$class}{$c}\" href=\"{$n[1]}\">{$i}{$n[0]}</a></li>";
    }

    public function js(): string
    {
elog(__METHOD__);

        return <<<HTML
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const sidebarToggle = document.getElementById('sidebarToggle');
                if (sidebarToggle) {
                    sidebarToggle.addEventListener('click', event => {
                        event.preventDefault();
                        document.body.classList.toggle('sb-sidenav-toggled');
                    });
                }

                // Handle submenu toggling
                const submenuToggles = document.querySelectorAll('.sidebar .dropdown-toggle');
                submenuToggles.forEach(toggle => {
                    toggle.addEventListener('click', function(e) {
                        e.preventDefault();
                        const submenuId = this.getAttribute('data-bs-target');
                        const submenu = document.querySelector(submenuId);
                        submenu.classList.toggle('show');
                        this.setAttribute('aria-expanded', submenu.classList.contains('show'));
                    });
                });
            });
        </script>
        HTML;
    }

    public function head(): string
    {
elog(__METHOD__);

        return <<<HTML
        HTML;
    }

    public function nav2(): string
    {
elog(__METHOD__);

        return $this->nav_dropdown(['Sites', $this->g->nav2, 'bi bi-globe'], 'r');
    }

    public function nav3(): string
    {
elog(__METHOD__);

        if (!util::is_usr()) {
            return '';
        }

        $usr = $this->getUserNavItems();
        return $this->nav_dropdown([$_SESSION['usr']['login'], $usr, 'bi bi-person-fill']);
    }

    public function nav_dropdown(array $a = []): string
    {
elog(__METHOD__);

        $o = "?o=" . $this->g->in['o'];
        $i = isset($a[2]) ? "<i class=\"{$a[2]}\"></i> " : '';

        $dropdownItems = implode('', array_map(fn($n) => $this->generateDropdownItem($n, $o), $a[1]));

        return $this->generateDropdownHtml($a[0], $i, $dropdownItems);
    }

    public function main(): string
    {
elog(__METHOD__);

        return "<main class=\"container\">{$this->g->out['log']}{$this->g->out['main']}</main>";
    }

    public function html() : string
    {
elog(__METHOD__);

        extract($this->g->out, EXTR_SKIP);
        return <<<HTML
<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <title>{$doc}</title>
        {$css}
        {$js}
    </head>
    <body>
        <nav class="navbar navbar-expand-md navbar-dark bg-dark fixed-top">
            <div class="container-fluid">
                <button id="sidebarToggle" class="navbar-toggler" type="button">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <a class="navbar-brand" href="#">NetServa HCP</a>
                <!-- Other navbar items if needed -->
            </div>
        </nav>

        <div class="container-fluid">
            <div class="row">
                <!-- Sidebar -->
                <nav id="sidebar" class="col-md-3 col-lg-2 d-md-block bg-dark sidebar">
                    <div class="position-sticky">
                        <ul class="nav flex-column">
                            {$nav1}
                        </ul>
                    </div>
                </nav>

                <!-- Main content -->
                <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                    {$log}
                    {$main}
                    {$foot}
                    {$end}
                </main>
            </div>
        </div>
    </body>
</html>
HTML;
    }

    public function log(): string
    {
elog(__METHOD__);

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
elog(__METHOD__);

        return $this->generateModalHtml($ary);
    }

    protected function modal_content(array $ary): string
    {
elog(__METHOD__);

        extract($ary);

        $action     = $action ?? '';
        $hidden     = $hidden ?? '';
        $lhs_cmd    = $this->generateModalCommand($lhs_cmd ?? '', 'danger', 'delete');
        $mid_cmd    = $this->generateModalCommand($mid_cmd ?? '', 'info', 'help');
        $footer     = $this->generateModalFooter($rhs_cmd ?? '', $lhs_cmd, $mid_cmd);
        $body       = $this->generateModalBody($body, $footer, $action, $hidden);

        return $this->generateModalContent($title, $body);
    }

    private function getUserNavItems(): array
    {
elog(__METHOD__);

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
elog(__METHOD__);

        $tmp = isset($n[3]) ? "?r={$this->g->in[$n[3]]}" : $o;
        $c = ($tmp === $n[1]) ? ' active' : '';
        $i = isset($n[2]) ? "<i class=\"{$n[2]}\"></i> " : '';
        return "<a class=\"dropdown-item{$c}\" href=\"{$n[1]}\">{$i}{$n[0]}</a>";
    }

    private function generateDropdownHtml(string $label, string $icon, string $items): string
    {
elog(__METHOD__);

        return <<<HTML
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown" aria-expanded="false">{$icon}{$label}</a>
          <div class="dropdown-menu">{$items}</div>
        </li>
        HTML;
    }

    private function generateLogAlert(string $level, string $message): string
    {
elog(__METHOD__);

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
elog(__METHOD__);
        $id = $ary['id'];
        $content = $this->modal_content($ary);
        return <<<HTML
        <div class="modal fade" id="{$id}" tabindex="-1" aria-labelledby="{$id}Label" aria-hidden="true">
            <div class="modal-dialog">
                {$content}
            </div>
        </div>
        HTML;
    }
    
    private function generateModalCommand(string $cmd, string $class, string $action): string
    {
elog(__METHOD__);
        if (empty($cmd)) {
            return '';
        }
        return "<button type=\"button\" class=\"btn btn-{$class} bslink\" data-bs-action=\"{$action}\">{$cmd}</button>";
    }
    
    private function generateModalFooter(string $rhs_cmd, string $lhs_cmd, string $mid_cmd): string
    {
elog(__METHOD__);
        if (empty($rhs_cmd)) {
            return '';
        }
        return <<<HTML
        <div class="modal-footer d-flex justify-content-between">
            {$lhs_cmd}
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            {$mid_cmd}
            <button type="submit" class="btn btn-primary">{$rhs_cmd}</button>
        </div>
        HTML;
    }
    
    private function generateModalBody(string $body, string $footer, string $action, string $hidden): string
    {
elog(__METHOD__);
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
            {$hidden}
            {$bodyContent}
            {$footer}
        </form>
        HTML;
    }
    
    private function generateModalContent(string $title, string $body): string
    {
elog(__METHOD__);
        return <<<HTML
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="{$this->g->in['o']}ModalLabel">{$title}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            {$body}
        </div>
        HTML;
    }
}

