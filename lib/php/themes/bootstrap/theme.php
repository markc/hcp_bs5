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

    private function getHeadContent(): string
    {
        return <<<HTML
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top bg-dark">
      <div class=container>
        <a class="navbar-brand" href="{$this->g->cfg['self']}">
          <b><i class="fa fa-server fa-fw"></i> {$this->g->out['head']} </b>
        </a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarsDefault" aria-controls="navbarsDefault" aria-expanded="false" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarsDefault">
          <ul class="navbar-nav mr-auto">{$this->g->out['nav1']}
          </ul>
          <ul class="navbar-nav ml-auto">{$this->g->out['nav3']}
          </ul>
        </div>
      </div>
    </nav>
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
