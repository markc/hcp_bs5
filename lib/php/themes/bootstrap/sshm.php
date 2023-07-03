<?php

declare(strict_types=1);
// lib/php/themes/bootstrap/sshm.php 20230703 - 20230703
// Copyright (C) 2015-2023 Mark Constable <markc@renta.net> (AGPL-3.0)

class Themes_Bootstrap_Sshm extends Themes_Bootstrap_Theme
{
    public function create(): string
    {
        $keybuf = $this->dropdown([
            ['1024', '1024'],
            ['2048', '2048'],
            ['4096', '4096'],
        ], 'keylen', '2048', '', 'form-select');

        return $this->modal([
            'id' => 'createmodal',
            'title' => 'Create DKIM Record',
            'action' => 'create',
            'footer' => 'Create',
            'body' => '
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
                      <label for="keylen" class="form-label">Key Length</label>' . $keybuf . '
                    </div>
                  </div>',
        ]);
    }

    public function read(array $in): string
    {
        return '
        <div class="row">
          <h3>
            <a href="?o=dkim&m=list"><i class="bi bi-chevron-double-left"></i></a> DKIM
            <a href="" title="Remove this DKIM record" data-bs-toggle="modal" data-bs-target="#removemodal">
              <small><i class="bi bi-trash cursor-pointer text-bs-danger"></i></small>
            </a>
          </h3>
        </div>
        <div class="row">' . $in['buf'] . '
        </div>' . $this->delete($in);
    }

    /**
     * Summary of delete
     * @param array $in
     * @return string
     */
    public function delete(array $in): string
    {
        return $this->modal([
            'id' => 'removemodal',
            'title' => 'Remove DKIM Record',
            'action' => 'delete',
            'footer' => 'Remove',
            'hidden' => '
                <input type="hidden" name="domain" value="' . $in['domain'] . '">',
            'body' => '
                  <p class="text-center">Are you sure you want to remove DKIM record for<br><b>' . $in['domain'] . '</b></p>',
        ]);
    }

    public function list(array $in): string
    {
        return '
        <div class="row">
          <h3>
            <i class="bi bi-key"></i> SSH Manager
            <a href="#" title="Add New SSH Host" data-bs-toggle="modal" data-bs-target="#createmodal">
            <small><i class="bi bi-plus-circle"></i></small></a>
          </h3>
        </div>
        <div class="row">' . $in['buf'] . '
        </div>' . $this->create();
    }
}
