<?php

declare(strict_types=1);

// lib/php/themes/bootstrap/vmails.php 20170101 - 20240904
// Copyright (C) 2015-2024 Mark Constable <markc@renta.net> (AGPL-3.0)

class Themes_Bootstrap_Vmails extends Themes_Bootstrap_Theme
{
    public function list(array $in): string
    {
        $create = $this->generateModal('createmodal', 'Create New Mailbox', 'create', 'Create', $this->createModalBody());
        $remove = $this->generateModal('removemodal', 'Remove Mailbox', 'delete', 'Remove', $this->removeModalBody());
        $update = $this->generateModal('updatemodal', 'Change Password', 'update', 'Update', $this->updateModalBody());

        return $this->generateListHTML($create, $remove, $update);
    }

    private function generateModal(string $id, string $title, string $action, string $footer, string $body): string
    {
        return <<<HTML
        <div class="modal fade" id="$id" tabindex="-1" aria-labelledby="{$id}Label" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="{$id}Label">$title</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form method="post" action="?o=vmails&m=$action">
                        <div class="modal-body">
                            $body
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">$footer</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        HTML;
    }

    private function createModalBody(): string
    {
        return <<<HTML
        <div class="mb-3">
            <label for="user" class="form-label">Email Address</label>
            <input type="text" class="form-control" id="user" name="user">
        </div>
        HTML;
    }

    private function removeModalBody(): string
    {
        return <<<HTML
        <input type="hidden" id="removeuser" name="user" value="">
        <p class="text-center">Are you sure you want to remove this mailbox?<br><b id="targetuser"></b></p>
        HTML;
    }

    private function updateModalBody(): string
    {
        return <<<HTML
        <input type="hidden" id="updateuser" name="user" value="">
        <div class="input-group mb-3">
            <button class="btn btn-outline-primary" type="button" id="shpw">Show</button>
            <input type="text" class="form-control" id="password" name="password" placeholder="Email Password">
            <button class="btn btn-outline-primary" type="button" id="newpw">NewPW</button>
        </div>
        HTML;
    }

    private function generateListHTML(string $create, string $remove, string $update): string
    {
        return <<<HTML
        <div class="col-12">
            <h3>
                <i class="bi bi-envelope"></i> Mailboxes
                <a href="#" title="Add New Mailbox" data-bs-toggle="modal" data-bs-target="#createmodal">
                    <small><i class="bi bi-plus-circle"></i></small>
                </a>
            </h3>
        </div>
        <div class="row">
            <div class="col-12">
                <div class="table-responsive">
                    <table id="vmails" class="table table-sm" style="min-width:1100px;table-layout:fixed">
                        <thead class="nowrap">
                            <tr>
                                <th>Email</th>
                                <th>Usage&nbsp;</th>
                                <th>Messages&nbsp;</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        $create
        $remove
        $update
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            const table = new DataTable('#vmails', {
                processing: true,
                serverSide: true,
                ajax: '?x=json&o=vmails&m=list',
                order: [[4, 'desc']],
                scrollX: true,
                columnDefs: [
                    {targets: 0, width: '30%'},
                    {targets: 1, className: 'text-end'},
                    {targets: 2, className: 'text-end'},
                    {targets: 3, className: 'text-end', width: '2rem', sortable: false},
                    {targets: 4, visible: false, sortable: true}
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

            const removeModal = document.getElementById('removemodal');
            removeModal.addEventListener('show.bs.modal', function(event) {
                const button = event.relatedTarget;
                const user = button.getAttribute('data-removeuser');
                this.querySelector('#removeuser').value = user;
                this.querySelector('#targetuser').textContent = user;
            });

            const updateModal = document.getElementById('updatemodal');
            updateModal.addEventListener('show.bs.modal', function(event) {
                const button = event.relatedTarget;
                const user = button.getAttribute('data-user');
                const id = button.getAttribute('data-id');
                this.querySelector('#i').value = id;
                this.querySelector('#updateuser').value = user;

                const passwordInput = this.querySelector('#password');

                this.querySelector('#shpw').addEventListener('click', function() {
                    fetch(`?x=text&o=vmails&m=update&shpw=1&user=${user}`, {method: 'POST'})
                        .then(response => response.text())
                        .then(data => passwordInput.value = data);
                });

                this.querySelector('#newpw').addEventListener('click', function() {
                    fetch('?x=text&o=vmails&m=update&newpw=1', {method: 'POST'})
                        .then(response => response.text())
                        .then(data => passwordInput.value = data);
                });
            });
        });
        </script>
        HTML;
    }
}
