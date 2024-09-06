<?php

declare(strict_types=1);

// lib/php/themes/bootstrap5/sshm.php 20230703 - 20240906
// Copyright (C) 2015-2024 Mark Constable <markc@renta.net> (AGPL-3.0)

class Themes_Bootstrap5_Sshm extends Themes_Bootstrap5_Theme
{
    public function create(array $in): string
    {
elog(__METHOD__);

        return $this->modalContent('Create SSH Host', 'create', '', 'Help', 'Create', $this->modalBody($in));
    }

    public function update(array $in): string
    {
elog(__METHOD__);

        return $this->modalContent('Update SSH Host', 'update', '', 'Help', 'Update', $this->modalBody($in));
    }

    public function delete(array $in): string
    {
elog(__METHOD__);

        $hidden = "<input type='hidden' name='name' value='{$in['name']}'>";
        $body = "<p class='text-center'>Are you sure you want to remove SSH Host for<br><b>{$in['name']}</b></p>";
        return $this->modalContent('Remove SSH Host', 'delete', '', 'Help', 'Remove', $body, $hidden);
    }

    public function list(array $in): string
    {
elog(__METHOD__);

        $rows = $this->generateTableRows($in['ary'] ?? []);
        return $this->generateListHTML($rows);
    }

    public function help(string $name, string $body): string
    {
elog(__METHOD__);

        return $this->modalContent("Help for <b>sshm $name</b>", body: "<pre>$body</pre>");
    }

    private function modalBody(array $in): string
    {
elog(__METHOD__);

        $keys = array_pop($in);
        array_unshift($keys, 'none');
        $skeyOptions = array_map(fn($k) => [$k, $k], $keys);
        $skeyBuf = $this->dropdown($skeyOptions, 'skey', $in['skey'], '', 'form-select');

        return <<<HTML
        <div class="row mb-3">
            <div class="col-md-6">
                <label for="name" class="form-label">Name</label>
                <input type="text" class="form-control" id="name" name="name" value="{$in['name']}">
            </div>
            <div class="col-md-6">
                <label for="host" class="form-label">Host</label>
                <input type="text" class="form-control" id="host" name="host" value="{$in['host']}">
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-md-3">
                <label for="port" class="form-label">Port</label>
                <input type="text" class="form-control" id="port" name="port" value="{$in['port']}">
            </div>
            <div class="col-md-4">
                <label for="user" class="form-label">User</label>
                <input type="text" class="form-control" id="user" name="user" value="{$in['user']}">
            </div>
            <div class="col-md-5">
                <label for="skey" class="form-label">SSH key</label>
                $skeyBuf
            </div>
        </div>
        HTML;
    }

    public function key_create(array $in): string
    {
elog(__METHOD__);

        return $this->modalContent('Create SSH Key', 'key_create', '', 'Help', 'Create', $this->modalKeyBody($in));
    }

    public function key_read(string $name, string $body): string
    {
elog(__METHOD__);

        return $this->modalContent("SSH Key: <b>$name</b>", body: "<textarea rows='12' style='width:100%;'>$body</textarea>");
    }

    public function key_delete(array $in): string
    {
elog(__METHOD__);

        $hidden = "<input type='hidden' id='key_name' name='key_name' value='{$in['key_name']}'>";
        $body = "<p class='text-center'>Are you sure you want to remove SSH Key<br><b>{$in['key_name']}</b></p>";
        return $this->modalContent('Remove SSH Key', 'key_delete', '', 'Help', 'Remove', $body, $hidden);
    }

    public function key_list(array $in): string
    {
elog(__METHOD__);

        $rows = $this->generateKeyTableRows($in);
        return $this->generateKeyListHTML($rows);
    }

    private function modalKeyBody(array $in): string
    {
elog(__METHOD__);

        return <<<HTML
        <div class="row mb-3">
            <div class="col-md-4">
                <label for="key_name" class="form-label">Key Name</label>
                <input type="text" class="form-control" id="key_name" name="key_name" value="{$in['key_name']}">
            </div>
            <div class="col-md-4">
                <label for="key_cmnt" class="form-label">Key Comment</label>
                <input type="text" class="form-control" id="key_cmnt" name="key_cmnt" value="{$in['key_cmnt']}" placeholder="Optional">
            </div>
            <div class="col-md-4">
                <label for="key_pass" class="form-label">Key Password</label>
                <input type="text" class="form-control" id="key_pass" name="key_pass" value="{$in['key_pass']}" placeholder="Optional">
            </div>
        </div>
        HTML;
    }

    private function modalContent(string $title, string $action = '', string $lhsCmd = '', string $midCmd = '', string $rhsCmd = '', string $body = '', string $hidden = ''): string
    {
elog(__METHOD__);

        $footer = $this->generateModalFooter($lhsCmd, $midCmd, $rhsCmd);
        return <<<HTML
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">$title</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post" action="?o=sshm&m=$action">
                <div class="modal-body">
                    $body
                    $hidden
                </div>
                <div class="modal-footer">
                    $footer
                </div>
            </form>
        </div>
        HTML;
    }

    private function generateModalFooter(string $lhsCmd, string $midCmd, string $rhsCmd): string
    {
elog(__METHOD__);

        $buttons = [];
        if ($lhsCmd) $buttons[] = "<button type='submit' class='btn btn-secondary' name='sb' value='$lhsCmd'>$lhsCmd</button>";
        if ($midCmd) $buttons[] = "<button type='button' class='btn btn-info' data-bs-dismiss='modal'>$midCmd</button>";
        if ($rhsCmd) $buttons[] = "<button type='submit' class='btn btn-primary' name='sb' value='$rhsCmd'>$rhsCmd</button>";
        return implode('', $buttons);
    }

    private function generateTableRows(array $rows): string
    {
elog(__METHOD__);

        return implode('', array_map(fn($row) => $this->generateTableRow(...preg_split('/\s+/', $row)), $rows));
    }

    private function generateTableRow(string $name, string $host, string $port, string $user, ?string $skey = null): string
    {
elog(__METHOD__);

        $skeyLink = (!$skey || $skey === 'none') ? '' : "<a class='bslink' href='?o=sshm&m=key_read&skey=$skey'><b>$skey</b></a>";
        return <<<HTML
        <tr>
            <td><a class="bslink" href="?o=sshm&m=update&name=$name"><b>$name</b></a></td>
            <td>$host</td>
            <td>$port</td>
            <td>$user</td>
            <td>$skeyLink</td>
            <td>
                <a class="bslink" href="?o=sshm&m=delete&name=$name" title="Delete SSH Host: $name">
                    <i class="bi bi-trash cursor-pointer text-danger"></i>
                </a>
            </td>
        </tr>
        HTML;
    }

    private function generateListHTML(string $rows): string
    {
elog(__METHOD__);

        return <<<HTML
        <div class="row mb-1">
            <div class="d-flex justify-content-between">
                <h3>
                    <i class="bi bi-key"></i> SSH Manager
                    <a href="?o=sshm&m=create" class="bslink" title="Add new SSH Host config">
                        <small><i class="bi bi-plus-circle"></i></small>
                    </a>
                </h3>
                <div>
                    <a href="?o=sshm&m=key_list" class="btn btn-primary btn-sm" title="Show SSH Keys">SSH Keys</a>
                </div>
            </div>
        </div>
        <div class="table-responsive">
            <table id="sshm" class="table table-borderless table-striped w-100">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Host</th>
                        <th>Port</th>
                        <th>User</th>
                        <th>SSH Key</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>$rows</tbody>
            </table>
        </div>
        {$this->generateModals()}
        {$this->generateScript()}
        HTML;
    }

    private function generateKeyTableRows(array $in): string
    {
elog(__METHOD__);

        if ($in['err'] === 254 || $in['err'] === 255) {
            $lvl = $in['err'] === 254 ? 'warning' : 'danger';
            util::log($in['ary'][0], $lvl);
            return '';
        }
        return implode('', array_map(fn($row) => $this->generateKeyTableRow(...preg_split('/\s+/', $row)), $in['ary']));
    }

    private function generateKeyTableRow(string $name, string $size, string $fingerprint, string $comment, string $type): string
    {
elog(__METHOD__);

        return <<<HTML
        <tr>
            <td><a class="bslink" href="?o=sshm&m=key_read&skey=$name"><b>$name</b></a></td>
            <td>$size</td>
            <td>$fingerprint</td>
            <td>$comment</td>
            <td>$type</td>
            <td>
                <a class="bslink" href="?o=sshm&m=key_delete&key_name=$name" title="Delete SSH Key: $name">
                    <i class="bi bi-trash cursor-pointer text-danger"></i>
                </a>
            </td>
        </tr>
        HTML;
    }

    private function generateKeyListHTML(string $rows): string
    {
elog(__METHOD__);

        return <<<HTML
        <div class="row mb-1">
            <div class="d-flex justify-content-between">
                <h3>
                    <i class="bi bi-key"></i> SSH Keys
                    <a href="?o=sshm&m=key_create" class="bslink" title="Add new SSH Key">
                        <small><i class="bi bi-plus-circle"></i></small>
                    </a>
                </h3>
                <div>
                    <a href="?o=sshm" class="btn btn-primary btn-sm" title="Show SSH Hosts">SSH Hosts</a>
                </div>
            </div>
        </div>
        <div class="table-responsive">
            <table id="sshm_keys" class="table table-borderless table-striped w-100">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Size</th>
                        <th>Fingerprint</th>
                        <th>Comment</th>
                        <th>Type</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>$rows</tbody>
            </table>
        </div>
        {$this->generateKeyModals()}
        {$this->generateKeyScript()}
        HTML;
    }

    private function generateModals(): string
    {
elog(__METHOD__);

        $modals = ['create', 'update', 'delete', 'key_read', 'help'];
        return implode('', array_map(fn($modal) => $this->generateModal($modal), $modals));
    }

    private function generateKeyModals(): string
    {
elog(__METHOD__);

        $modals = ['key_create', 'key_read', 'key_delete', 'help'];
        return implode('', array_map(fn($modal) => $this->generateModal($modal), $modals));
    }

    private function generateModal(string $name): string
    {
elog(__METHOD__);

        $label = $name === 'key_read' ? 'shkeymodal' : "{$name}modal";
        return <<<HTML
        <div class="modal fade" id="{$name}modal" tabindex="-1" aria-labelledby="$label" aria-hidden="true">
            <div class="modal-dialog" id="{$name}dialog"></div>
        </div>
        HTML;
    }

    private function generateScript(): string
    {
elog(__METHOD__);

        return $this->generateTableScript('sshm');
    }

    private function generateKeyScript(): string
    {
elog(__METHOD__);

        return $this->generateTableScript('sshm_keys');
    }

    private function generateTableScript(string $tableId): string
    {
elog(__METHOD__);

        return <<<HTML
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            new DataTable('#$tableId', {
                processing: true,
                scrollX: true,
                columnDefs: [
                    {targets: 2, className: 'text-truncate'},
                    {targets: 5, className: 'text-end', width: '3rem', orderable: false},
                ]
            });

            document.querySelectorAll('.bslink').forEach(link => {
                link.addEventListener('click', function(event) {
                    event.preventDefault();
                    const url = new URL(this.href);
                    url.searchParams.set('x', 'html');
                    const m = url.searchParams.get('m');
                    fetch(url)
                        .then(response => response.text())
                        .then(html => {
                            document.getElementById(`${m}dialog`).innerHTML = html;
                            new bootstrap.Modal(document.getElementById(`${m}modal`)).show();
                        });
                });
            });
        });
        </script>
        HTML;
    }
}
