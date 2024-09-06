<?php

declare(strict_types=1);

// lib/php/themes/bootstrap5/theme.php 20150101 - 20240906
// Copyright (C) 2015-2024 Mark Constable <markc@renta.net> (AGPL-3.0)

class Themes_Bootstrap5_Theme extends Theme
{
    public function css(): string
    {
elog(__METHOD__);

        return <<<HTML
    <link rel="icon" type="image/x-icon" href="/hcp/favicon.ico">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
    body { padding-top: 5rem; }
    .table { width: 100%; table-layout: fixed; }
    table.dataTable { border-collapse: collapse !important; }
    .bi { margin-right: 0.25rem; } 
    @media (min-width: 768px) {
        .columns { column-count: 2; }
    }
    @media (min-width: 1200px) {
        .columns { column-count: 3; }
    }
    </style>
    HTML;
    }

    public function head(): string
    {
elog(__METHOD__);

        return <<<HTML
        <nav class="navbar navbar-expand-lg navbar-dark fixed-top bg-dark">
            <div class="container">
                <a class="navbar-brand" href="{$this->g->cfg['self']}">
                <b><i class="bi bi-server"></i> {$this->g->out['head']}</b>
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent" aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarContent">
                <ul class="navbar-nav mx-auto mb-2 mb-lg-0">
                    {$this->g->out['nav1']}
                </ul>
                <ul class="navbar-nav">
                    {$this->g->out['nav3']}
                </ul>
                </div>
            </div>
        </nav>
        HTML;
    }

    public function nav1(array $a = []): string
    {
elog(__METHOD__);

        $a = isset($a[0]) ? $a : util::get_nav($this->g->nav1);
        $o = '?o=' . $this->g->in['o'];
        $t = '?t=' . util::ses('t');

        return implode('', array_map(fn($n) => $this->generateNavItem($n, $o, $t), $a));
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

    public function js(): string
    {
elog(__METHOD__);

        return <<<HTML
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
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

    private function generateNavItem(array $n, string $o, string $t): string
    {
elog(__METHOD__);

        if (is_array($n[1])) {
            return $this->nav_dropdown($n);
        }
        $c = $o === $n[1] || $t === $n[1] ? ' active' : '';
        $i = isset($n[2]) ? "<i class=\"{$n[2]}\"></i> " : '';
        return "<li class=\"nav-item\"><a class=\"nav-link{$c}\" href=\"{$n[1]}\">{$i}{$n[0]}</a></li>";
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
        <div class="modal fade" id="{$id}" tabindex="-1" role="dialog" aria-labelledby="{$id}" aria-hidden="true">
          <div class="modal-dialog">{$content}</div>
        </div>
        HTML;
    }

    private function generateModalCommand(string $cmd, string $class, string $action): string
    {
elog(__METHOD__);

        if (empty($cmd)) {
            return '';
        }
        return "<a class=\"btn btn-{$class} bslink\" href=\"?o={$this->g->in['o']}&m={$action}&i={$this->g->in['i']}\">{$cmd}</a>";
    }

    private function generateModalFooter(string $rhs_cmd, string $lhs_cmd, string $mid_cmd): string
    {
elog(__METHOD__);

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
          {$hidden}{$bodyContent}{$footer}
        </form>
        HTML;
    }

    private function generateModalContent(string $title, string $body): string
    {
elog(__METHOD__);

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
