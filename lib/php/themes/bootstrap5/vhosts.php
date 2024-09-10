<?php

declare(strict_types=1);

// lib/php/themes/bootstrap5/vhosts.php 20170101 - 20240906
// Copyright (C) 2015-2024 Mark Constable <markc@renta.net> (AGPL-3.0)

class Themes_Bootstrap5_Vhosts extends Themes_Bootstrap5_Theme
{
    public function create(array $in): string
    {
elog(__METHOD__);

        return $this->modalContent(
            'Create New Vhost',
            'create',
            '',
            'Create',
            $this->modalCreateBody($in)
        );
    }

    public function update(array $in): string
    {
elog(__METHOD__);

        $remove = $this->modal([
            'id'        => 'removemodal',
            'title'     => 'Remove Vhost',
            'action'    => 'delete',
            'footer'    => 'Remove',
            'body'      => $this->generateRemoveBody($in['domain'])
        ]);

        return $this->generateUpdateForm($in) . $remove;
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
            <form method="post" action="?o=vhosts&m=$action">
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

    private function modalCreateBody(array $in): string
    {
elog(__METHOD__);

        return <<<HTML
        <div class="mb-3">
            <label for="domain" class="form-label">Vhost</label>
            <input type="text" class="form-control" id="domain" name="domain">
        </div>
        <div class="row mb-3">
            <div class="col-12 col-sm-6">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="cms" id="cms" checked>
                    <label class="form-check-label" for="cms">WordPress</label>
                </div>
            </div>
            <div class="col-12 col-sm-6">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="ssl" id="ssl">
                    <label class="form-check-label" for="ssl">Self Signed SSL</label>
                </div>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-12 col-sm-6">
                <label for="ip" class="form-label">IP (optional)</label>
                <input type="text" class="form-control" id="ip" name="ip">
            </div>
            <div class="col-12 col-sm-6">
                <label for="uuser" class="form-label">Custom User</label>
                <input type="text" class="form-control" id="uuser" name="uuser">
            </div>
        </div>
        HTML;
    }

    private function generateRemoveBody(string $domain): string
    {
elog(__METHOD__);

        $escapedDomain = htmlspecialchars($domain, ENT_QUOTES, 'UTF-8');
        return <<<HTML
        <p class="text-center">Are you sure you want to remove this Vhost?<br><b>$escapedDomain</b></p>
        HTML;
    }

    private function generateUpdateForm(array $in): string
    {
elog(__METHOD__);

        $active         = $in['active'] ? ' checked' : '';
        $escapedDomain  = htmlspecialchars($in['domain'], ENT_QUOTES, 'UTF-8');
        $aliases        = intval($in['aliases']);
        $mailboxes      = intval($in['mailboxes']);
        $mailquota      = intval($in['mailquota'] / 1000000);
        $diskquota      = intval($in['diskquota'] / 1000000);

        return <<<HTML
        <div class="row">
            <h1>
                <a href="?o=vhosts&m=list"><i class="fas fa-angle-double-left fa-fw"></i></a> Vhosts
                <a href="" title="Remove this VHOST" data-bs-toggle="modal" data-bs-target="#removemodal">
                    <small><i class="fas fa-trash fa-fw cursor-pointer text-danger"></i></small>
                </a>
            </h1>
        </div>
        <div class="row">
            <div class="col-12">
                <form method="post" action="{$this->g->cfg['self']}">
                    <input type="hidden" name="c" value="{$_SESSION['c']}">
                    <input type="hidden" name="o" value="{$this->g->in['o']}">
                    <input type="hidden" name="i" value="{$this->g->in['i']}">
                    <div class="row">
                        <div class="form-group col-12 col-md-6 col-lg-4">
                            <label for="domain">Domain</label>
                            <input type="text" class="form-control" value="$escapedDomain" disabled>
                        </div>
                        <div class="form-group col-6 col-md-3 col-lg-2">
                            <label for="aliases">Max Aliases</label>
                            <input type="number" class="form-control" name="aliases" id="aliases" value="$aliases">
                        </div>
                        <div class="form-group col-6 col-md-3 col-lg-2">
                            <label for="mailboxes">Max Mailboxes</label>
                            <input type="number" class="form-control" name="mailboxes" id="mailboxes" value="$mailboxes">
                        </div>
                        <div class="form-group col-6 col-md-3 col-lg-2">
                            <label for="mailquota">Mail Quota (MB)</label>
                            <input type="number" class="form-control" name="mailquota" id="mailquota" value="$mailquota">
                        </div>
                        <div class="form-group col-6 col-md-3 col-lg-2">
                            <label for="diskquota">Disk Quota (MB)</label>
                            <input type="number" class="form-control" name="diskquota" id="diskquota" value="$diskquota">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12 col-sm-6">
                            <div class="form-group">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" name="active" id="active"$active>
                                    <label class="custom-control-label" for="active">Active</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-sm-6 text-right">
                            <div class="btn-group">
                                <a class="btn btn-secondary" href="?o=vhosts&m=list">&laquo; Back</a>
                                <button type="submit" name="m" value="update" class="btn btn-primary">Save</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        HTML;
    }

    private function generateListHTML(): string
    {
elog(__METHOD__);

        return <<<HTML
        <div class="row">
            <h1>
                <i class="bi bi-globe"></i> Vhosts
                <a href="?o=vhosts&m=create" class="bslink" title="Add New Vhost">
                    <small><i class="bi bi-plus-circle"></i></small>
                </a>
            </h1>
        </div>
        <div class="table-responsive">
            <table id="vhosts" class="table table-borderless table-striped datatable">
                <thead>
                    <tr>
                        <th>Domain</th>
                        <th>Alias&nbsp;</th>
                        <th></th>
                        <th></th>
                        <th>Mbox&nbsp;</th>
                        <th></th>
                        <th></th>
                        <th>Mail&nbsp;</th>
                        <th></th>
                        <th></th>
                        <th>Disk&nbsp;</th>
                        <th></th>
                        <th></th>
                        <th></th>
                    </tr>
                </thead>
                <tfoot></tfoot>
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
            const table = new DataTable('#vhosts', {
                processing: true,
                serverSide: true,
                ajax: '?x=json&o=vhosts&m=list',
                order: [[15, 'desc']],
                scrollX: true,
                columnDefs: [
                    {targets: 0, className: 'text-truncate', width: '25%'},
                    {targets: 1, className: 'text-end', width: '3rem'},
                    {targets: 2, className: 'text-center', width: '0.5rem', sortable: false},
                    {targets: 3, width: '3rem'},
                    {targets: 4, className: 'text-end', width: '3rem'},
                    {targets: 5, className: 'text-center', width: '0.5rem', sortable: false},
                    {targets: 6, width: '3rem'},
                    {targets: 7, className: 'text-end', width: '4rem'},
                    {targets: 8, className: 'text-center', width: '0.5rem', sortable: false},
                    {targets: 9, width: '4rem'},
                    {targets: 10, className: 'text-end', width: '4rem'},
                    {targets: 11, className: 'text-center', width: '0.5rem', sortable: false},
                    {targets: 12, width: '4rem'},
                    {targets: 13, className: 'text-end', width: '1rem', sortable: false},
                    {targets: 14, visible: false, sortable: true},
                    {targets: 15, visible: false, sortable: true},
                ]
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
}
