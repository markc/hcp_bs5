<?php

declare(strict_types=1);
// lib/php/themes/bootstrap/valias.php 20170101 - 20230625
// Copyright (C) 2015-2023 Mark Constable <markc@renta.net> (AGPL-3.0)

class Themes_Bootstrap_Valias extends Themes_Bootstrap_Theme
{
    public function create(array $in): string
    {
        return $this->modal_content([
            'title'     => 'Create New Alias',
            'action'    => 'create',
            'lhs_cmd'   => '',
            'rhs_cmd'   => 'Create',
            'body'      => $this->modal_body($in)
        ]);
    }

    public function update(array $in): string
    {
        return $this->modal_content([
            'title'     => 'Update Alias',
            'action'    => 'update',
            'lhs_cmd'   => 'Delete',
            'rhs_cmd'   => 'Update',
            'body'      => $this->modal_body($in)
        ]);
    }

    public function delete(): ?string
    {
        $tmp = db::read('source', 'id', $this->g->in['i'], '', 'one');

        return $this->modal_content([
            'title'     => 'Remove Alias',
            'action'    => 'delete',
            'lhs_cmd'   => '',
            'rhs_cmd'   => 'Remove',
            'hidden'    => '
            <input type="hidden" name="i" value="' . $this->g->in['i'] . '">',
            'body'      => '
            <p class="text-center">Are you sure you want to remove this alias?<br><b>' . $tmp['source'] . '</b></p>',
        ]);
    }

    public function list(array $in): string
    {
        return '
        <div class="row">
          <h3>
            <i class="bi bi-envelope"></i> Aliases
            <a href="?o=valias&m=create" class="bslink" title="Add New Alias">
              <small><i class="bi bi-plus-circle"></i></small>
            </a>
          </h3>
        </div>
        <div class="table-responsive">
          <table id="valias" class="table table-borderless table-striped w-100">
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
        <div class="modal fade" id="createmodal" tabindex="-1" role="dialog" aria-labelledby="createmodal" aria-hidden="true">
          <div class="modal-dialog" id="createdialog">
          </div>
        </div>
        <div class="modal fade" id="updatemodal" tabindex="-1" role="dialog" aria-labelledby="updatemodal" aria-hidden="true">
          <div class="modal-dialog" id="updatedialog">
          </div>
        </div>
        <div class="modal fade" id="deletemodal" tabindex="-1" role="dialog" aria-labelledby="deletemodal" aria-hidden="true">
          <div class="modal-dialog" id="deletedialog">
          </div>
        </div>
        <script>
$(document).ready(function() {
  $("#valias").DataTable({
    "processing": true,
    "serverSide": true,
    "ajax": "?x=json&o=valias&m=list",
    "order": [[ 5, "desc" ]],
    "scrollX": true,
    "columnDefs": [
      {"targets":0,   "className":"text-truncate", "width":"30%"},
      {"targets":3,   "className":"text-right", "width":"1rem", "sortable": false},
      {"targets":4,   "visible":false},
      {"targets":5,   "visible":false},
    ],
  });

  $(document).on("click", ".bslink", function(){
    event.preventDefault();
    var url = $(this).attr("href") + "&x=html";
    var m = new URLSearchParams(url).get("m");
    $("#" + m + "dialog").load(url, function() {
      $("#" + m + "modal", document).modal("show");
    });
  });

});
        </script>';
    }

    private function modal_body(array $in): string
    {
        $active_buf = ($in['active'] ? 1 : 0) ? ' checked' : '';
        return '
        <div class="mb-3">
          <label class="form-label" for="source">Alias Address(es)</label>
          <textarea autocorrect="off" autocapitalize="none" class="form-control" rows="4" name="source"
            id="source">' . $in['source'] . '</textarea>
          <div>Full email address/es or @example.com, to catch all messages for a domain (comma-separated). <b>Locally hosted
              domains only</b>.</div>
        </div>
        <div class="mb-3">
          <label class="form-label" for="target">Target Address(es)</label>
          <textarea autocorrect="off" autocapitalize="none" class="form-control" rows="4" id="target"
            name="target">' . $in['target'] . '</textarea>
          <div>Full email address/es (comma-separated).</div>
        </div>
        <div class="form-check mb-3">
          <input class="form-check-input" type="checkbox" name="active" id="active"' . $active_buf . '>
          <label class="form-check-label" for="active">Active</label>
        </div>';
    }
}
