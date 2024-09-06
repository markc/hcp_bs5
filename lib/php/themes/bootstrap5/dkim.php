<?php

declare(strict_types=1);

// lib/php/themes/bootstrap5/dkim.php 20180511 - 20240906
// Copyright (C) 2015-2024 Mark Constable <markc@renta.net> (AGPL-3.0)

class Themes_Bootstrap5_Dkim extends Themes_Bootstrap5_Theme
{
    public function create(): string
    {
elog(__METHOD__);

        $keybuf = $this->dropdown([
            ['1024', '1024'],
            ['2048', '2048'],
            ['4096', '4096'],
        ], 'keylen', '2048', '', 'form-select');

        return $this->modal([
            'id'        => 'createmodal',
            'title'     => 'Create SSH Host',
            'action'    => 'create',
            'lhs_cmd'   => '',
            'rhs_cmd'   => 'Create',
            'body'      => <<<HTML
                <div class="mb-3">
                    <label for="domain" class="form-label">Domain</label>
                    <input type="text" class="form-control" id="domain" name="domain">
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="select" class="form-label">Selector</label>
                        <input type="text" class="form-control" id="select" name="select" value="mail">
                    </div>
                    <div class="col-md-6">
                        <label for="keylen" class="form-label">Key Length</label>
                        $keybuf
                    </div>
                </div>
            HTML,
        ]);
    }

    public function read(array $in): string
    {
elog(__METHOD__);

        return <<<HTML
            <div class="row">
                <h1>
                    <a href="?o=dkim&m=list"><i class="bi bi-chevron-double-left"></i></a> DKIM
                    <a href="" title="Remove this DKIM record" data-bs-toggle="modal" data-bs-target="#removemodal">
                        <small><i class="bi bi-trash cursor-pointer text-bs-danger"></i></small>
                    </a>
                </h1>
            </div>
            <div class="row">{$in['buf']}</div>
            {$this->delete($in)}
        HTML;
    }

    public function delete(array $in): string
    {
elog(__METHOD__);

        return $this->modal([
            'id'        => 'removemodal',
            'title'     => 'Remove DKIM Record',
            'action'    => 'delete',
            'lhs_cmd'   => '',
            'rhs_cmd'   => 'Remove',
            'hidden'    => sprintf('<input type="hidden" name="domain" value="%s">', $in['domain']),
            'body'      => sprintf('<p class="text-center">Are you sure you want to remove DKIM record for<br><b>%s</b></p>', $in['domain']),
        ]);
    }

    public function list(array $in): string
    {
elog(__METHOD__);

        return <<<HTML
            <div class="row">
                <h1>
                    <i class="bi bi-card-heading"></i> DKIM
                    <a href="#" title="Add New DKIM Key" data-bs-toggle="modal" data-bs-target="#createmodal">
                        <small><i class="bi bi-plus-circle"></i></small>
                    </a>
                </h1>
            </div>
            <div class="row">{$in['buf']}</div>
            {$this->create()}
        HTML;
    }
}
