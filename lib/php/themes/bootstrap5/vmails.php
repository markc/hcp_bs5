<?php

declare(strict_types=1);

// lib/php/themes/bootstrap5/vmails.php 20170101 - 20240906
// Copyright (C) 2015-2024 Mark Constable <markc@renta.net> (AGPL-3.0)

class Themes_Bootstrap5_Vmails extends Themes_Bootstrap5_Theme
{
    public function list(array $in): string
    {
elog(__METHOD__);

        $create = $this->generateModal('createmodal', 'Create New Mailbox', 'create', 'Create', $this->createModalBody());
        $remove = $this->generateModal('removemodal', 'Remove Mailbox', 'delete', 'Remove', $this->removeModalBody());
        $update = $this->generateModal('updatemodal', 'Change Password', 'update', 'Update', $this->updateModalBody());

        return $this->generateListHTML($create, $remove, $update);
    }

    private function generateModal(string $id, string $title, string $action, string $footer, string $body): string
    {
elog(__METHOD__);

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
elog(__METHOD__);

        return <<<HTML
        <div class="mb-3">
            <label for="user" class="form-label">Email Address</label>
            <input type="text" class="form-control" id="user" name="user">
        </div>
        HTML;
    }

    private function removeModalBody(): string
    {
elog(__METHOD__);

        return <<<HTML
        <input type="hidden" id="removeuser" name="user" value="">
        <p class="text-center">Are you sure you want to remove this mailbox?<br><b id="targetuser"></b></p>
        HTML;
    }

    private function updateModalBody(): string
    {
elog(__METHOD__);

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
elog(__METHOD__);

        return <<<HTML
        <h1>
            <i class="bi bi-envelope"></i> Mailboxes
            <a href="#" title="Add New Mailbox" data-bs-toggle="modal" data-bs-target="#createmodal">
                <i class="bi bi-plus-circle-fill fs-2"></i>
            </a>
        </h1>
        <div class="table-responsive">
            <table id="vmails" class="table table-borderless table-striped datatable">
                <thead>
                    <tr>
                        <th>Email</th>
                        <th>Usage</th>
                        <th>Messages</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
        $create
        $remove
        $update
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize DataTable
    const table = new DataTable('#vmails', {
        processing: true,
        serverSide: true,
        ajax: '?x=json&o=vmails&m=list',
        order: [[4, 'desc']],
        scrollX: true,
        autoWidth: false,
        columnDefs: [
            {targets: 0, width: '30%'},
            {targets: 1, width: '20%', className: 'text-end'},
            {targets: 2, width: '20%', className: 'text-end'},
            {targets: 3, width: '20%', className: 'text-end', width: '2rem', sortable: false},
            {targets: 4, visible: false, sortable: true}
        ]
    });

    // Remove modal event listener
    const removeModal = document.getElementById('removemodal');
    removeModal.addEventListener('show.bs.modal', function(event) {
        const button = event.relatedTarget;
        const user = button.getAttribute('data-removeuser');
        this.querySelector('#removeuser').value = user;
        this.querySelector('#targetuser').textContent = user;
    });

    // Update modal event listener
    const updateModal = document.getElementById('updatemodal');
    updateModal.addEventListener('show.bs.modal', function(event) {
        const button = event.relatedTarget;
        const user = button.getAttribute('data-user');
        const id = button.getAttribute('data-id');
        this.querySelector('#updateuser').value = user;

        const passwordInput = this.querySelector('#password');

        // Show password button click handler
        this.querySelector('#shpw').addEventListener('click', function() {
            fetch('?x=text&o=vmails&m=update&shpw=1&user=\${user}', {method: 'POST'})
                .then(response => response.text())
                .then(data => passwordInput.value = data);
        });

        // New password button click handler
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

/*

        document.addEventListener('DOMContentLoaded', function() {
            // Initialize DataTable
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

        // Event listener for .bslink elements
        document.addEventListener('click', function(event) {
            if (event.target.matches('.bslink')) {
                event.preventDefault();
                const action = event.target.getAttribute('data-bs-action');
                const url = new URL(window.hcpConfig.selfUrl, window.location.origin);
                url.searchParams.set('x', 'html');
                url.searchParams.set('o', 'vmails');
                url.searchParams.set('m', action);
                url.searchParams.set('i', event.target.closest('form').querySelector('[name="i"]').value);
                
                fetch(url)
                    .then(response => response.text())
                    .then(html => {
                        const tempDiv = document.createElement('div');
                        tempDiv.innerHTML = html;
                        const modalId = tempDiv.querySelector('.modal').id;
                        document.body.insertAdjacentHTML('beforeend', html);
                        const modal = new bootstrap.Modal(document.getElementById(modalId));
                        modal.show();

                        // Clean up modal after it's hidden
                        document.getElementById(modalId).addEventListener('hidden.bs.modal', function() {
                            this.remove();
                        });
                    });
            }
        });

        // Global modal event listeners
        document.body.addEventListener('show.bs.modal', function(event) {
            const modal = event.target;
            const button = event.relatedTarget;

            if (modal.id === 'removemodal') {
                    const user = button.getAttribute('data-removeuser');
                    modal.querySelector('#removeuser').value = user;
                    modal.querySelector('#targetuser').textContent = user;
                } else if (modal.id === 'updatemodal') {
                    const user = button.getAttribute('data-user');
                    const id = button.getAttribute('data-id');
                    modal.querySelector('#i').value = id;
                    modal.querySelector('#updateuser').value = user;

                    const passwordInput = modal.querySelector('#password');

                    // Show password button click handler
                    modal.querySelector('#shpw').addEventListener('click', function() {
                        fetch(`?x=text&o=vmails&m=update&shpw=1&user=${user}`, {method: 'POST'})
                            .then(response => response.text())
                            .then(data => passwordInput.value = data);
                    });

                    // New password button click handler
                    modal.querySelector('#newpw').addEventListener('click', function() {
                        fetch('?x=text&o=vmails&m=update&newpw=1', {method: 'POST'})
                            .then(response => response.text())
                            .then(data => passwordInput.value = data);
                    });
                }
            });
        });


*/