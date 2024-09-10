<?php

declare(strict_types=1);

// lib/php/themes/bootstrap5/valias.php 20170101 - 20240906
// Copyright (C) 2015-2024 Mark Constable <markc@renta.net> (AGPL-3.0)

class Themes_Bootstrap5_Valias extends Themes_Bootstrap5_Theme
{
    public function create(array $in): string
    {
elog(__METHOD__);

        return $this->modalContent(
            'Create New Alias',
            'create',
            '',
            'Create',
            $this->modalBody($in)
        );
    }

    public function update(array $in): string
    {
elog(__METHOD__);

        return $this->modalContent(
            'Update Alias',
            'update',
            'Delete',
            'Update',
            $this->modalBody($in)
        );
    }

    public function delete(): string
    {
elog(__METHOD__);

        $source = db::read('source', 'id', $this->g->in['i'], '', 'one');

        return $this->modalContent(
            'Remove Alias',
            'delete',
            '',
            'Remove',
            $this->deleteModalBody($source['source'] ?? '')
        );
    }

    public function list(array $in): string
    {
elog(__METHOD__);

        return $this->generateListHTML();
    }

    private function modalContent(string $title, string $action, string $lhsCmd, string $rhsCmd, string $body): string
    {
elog(__METHOD__);

        return <<<HTML
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">$title</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post" action="?o=valias&m=$action">
                <div class="modal-body">
                    $body
                </div>
                <div class="modal-footer">
                    {$this->modalFooter($lhsCmd, $rhsCmd)}
                </div>
            </form>
        </div>
        HTML;
    }

    private function modalBody(array $in): string
    {
elog(__METHOD__);

        $activeChecked = ($in['active'] ?? false) ? ' checked' : '';
        return <<<HTML
        <div class="mb-3">
            <label class="form-label" for="source">Alias Address(es)</label>
            <textarea autocorrect="off" autocapitalize="none" class="form-control" rows="4" name="source" id="source">{$in['source']}</textarea>
            <div>Full email address/es or @example.com, to catch all messages for a domain (comma-separated). <b>Locally hosted domains only</b>.</div>
        </div>
        <div class="mb-3">
            <label class="form-label" for="target">Target Address(es)</label>
            <textarea autocorrect="off" autocapitalize="none" class="form-control" rows="4" id="target" name="target">{$in['target']}</textarea>
            <div>Full email address/es (comma-separated).</div>
        </div>
        <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" name="active" id="active"$activeChecked>
            <label class="form-check-label" for="active">Active</label>
        </div>
        HTML;
    }

    private function deleteModalBody(string $source): string
    {
elog(__METHOD__);

        $id = htmlspecialchars($this->g->in['i'], ENT_QUOTES, 'UTF-8');
        return <<<HTML
        <p class="text-center">Are you sure you want to remove this alias?<br><b>$source</b></p>
        <input type="hidden" name="i" value="$id">
        HTML;
    }

    private function modalFooter(string $lhsCmd, string $rhsCmd): string
    {
elog(__METHOD__);

        $lhsButton = $lhsCmd ? "<button type=\"submit\" class=\"btn btn-danger\" name=\"sb\" value=\"$lhsCmd\">$lhsCmd</button>" : '';
        return <<<HTML
        $lhsButton
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary" name="sb" value="$rhsCmd">$rhsCmd</button>
        HTML;
    }

    private function generateListHTML(): string
    {
elog(__METHOD__);

        return <<<HTML
        <div class="row">
            <h1>
                <i class="bi bi-envelope"></i> Aliases
                <a href="?o=valias&m=create" class="bslink" title="Add New Alias">
                    <small><i class="bi bi-plus-circle"></i></small>
                </a>
            </h1>
        </div>
        <div class="table-responsive">
            <table id="valias" class="table table-borderless table-striped datatable">
                <thead>
                    <tr>
                        <th>Alias</th>
                        <th>Target Address</th>
                        <th>Domain</th>
                        <th></th>
                    </tr>
                </thead>
            </table>
        </div>
        <div class="modal fade" id="createmodal" tabindex="-1" aria-labelledby="createmodal" aria-hidden="true">
            <div class="modal-dialog" id="createdialog"></div>
        </div>
        <div class="modal fade" id="updatemodal" tabindex="-1" aria-labelledby="updatemodal" aria-hidden="true">
            <div class="modal-dialog" id="updatedialog"></div>
        </div>
        <div class="modal fade" id="deletemodal" tabindex="-1" aria-labelledby="deletemodal" aria-hidden="true">
            <div class="modal-dialog" id="deletedialog"></div>
        </div>
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            const table = new DataTable('#valias', {
                processing: true,
                serverSide: true,
                ajax: '?x=json&o=valias&m=list',
                order: [[5, 'desc']],
                scrollX: true,
                columnDefs: [
                    {targets: 0, className: 'text-truncate', width: '30%'},
                    {targets: 3, className: 'text-end', width: '1rem', sortable: false},
                    {targets: 4, visible: false},
                    {targets: 5, visible: false},
                ],
            });

            document.addEventListener('click', function(event) {
                if (event.target.matches('.bslink')) {
                    event.preventDefault();
                    const url = new URL(event.target.href);
                    url.searchParams.set('x', 'html');
                    const m = url.searchParams.get('m');
                    fetch(url)
                        .then(response => response.text())
                        .then(html => {
                            document.getElementById(`${m}dialog`).innerHTML = html;
                            new bootstrap.Modal(document.getElementById(`${m}modal`)).show();
                        });
                }
            });
        });
        </script>
        HTML;
    }
}
