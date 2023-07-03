<?php

declare(strict_types=1);
// lib/php/themes/bootstrap/vhosts.php 20170101 - 20230625
// Copyright (C) 2015-2023 Mark Constable <markc@renta.net> (AGPL-3.0)

class Themes_Bootstrap_Vhosts extends Themes_Bootstrap_Theme
{
    public function create(array $in): string
    {
        return $this->modal_content([
            'title'     => 'Create New Vhost',
            'action'    => 'create',
            'lhs_cmd'   => '',
            'rhs_cmd'   => 'Create',
            'body'      => $this->modal_create_body($in)
        ]);
    }

    public function update(array $in): string
    {
        $remove = $this->modal([
            'id' => 'removemodal',
            'title' => 'Remove Vhost',
            'action' => 'delete',
            'footer' => 'Remove',
            //            'hidden'  => '
            //                <input type="hidden" name="domain" value="' . $in['domain'] . '">',
            'body' => '
                  <p class="text-center">Are you sure you want to remove this Vhost?<br><b>' . $in['domain'] . '</b></p>',
        ]);

        return '
        <div class="row">
          <h3>
            <a href="?o=vhosts&m=list"><i class="fas fa-angle-double-left fa-fw"></i></a> Vhosts
            <a href="" title="Remove this VHOST" data-bs-toggle="modal" data-bs-target="#removemodal">
              <small><i class="fas fa-trash fa-fw cursor-pointer text-danger"></i></small>
            </a>
          </h3>
        </div>
        <div class="row">
          <div class="col-12">
            <form method="post" action="' . $this->g->cfg['self'] . '">
              <input type="hidden" name="c" value="' . $_SESSION['c'] . '">
              <input type="hidden" name="o" value="' . $this->g->in['o'] . '">
              <input type="hidden" name="i" value="' . $this->g->in['i'] . '">
              <div class="row">
                <div class="form-group col-12 col-md-6 col-lg-4">
                  <label for="domain">Domain</label>
                  <input type="text" class="form-control" value="' . $in['domain'] . '" disabled>
                </div>
                <div class="form-group col-6 col-md-3 col-lg-2">
                  <label for="aliases">Max Aliases</label>
                  <input type="number" class="form-control" name="aliases" id="aliases" value="' . $in['aliases'] . '">
                </div>
                <div class="form-group col-6 col-md-3 col-lg-2">
                  <label for="mailboxes">Max Mailboxes</label>
                  <input type="number" class="form-control" name="mailboxes" id="mailboxes" value="' . $in['mailboxes'] . '">
                </div>
                <div class="form-group col-6 col-md-3 col-lg-2">
                  <label for="mailquota">Mail Quota (MB)</label>
                  <input type="number" class="form-control" name="mailquota" id="mailquota"
                    value="' . intval($in['mailquota'] / 1000000) . '">
                </div>
                <div class="form-group col-6 col-md-3 col-lg-2">
                  <label for="diskquota">Disk Quota (MB)</label>
                  <input type="number" class="form-control" name="diskquota" id="diskquota"
                    value="' . intval($in['diskquota'] / 1000000) . '">
                </div>
              </div>
              <div class="row">
                <div class="col-12 col-sm-6">
                  <div class="form-group">
                    <div class="custom-control custom-checkbox">
                      <input type="checkbox" class="custom-control-input" name="active" id="active"' . ($in[' active']
            ? ' checked' : '') . '>
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
              ' . $remove;
    }

    public function list(array $in): string
    {
        $create = $this->modal([
            'id'        => 'createmodal',
            'title'     => 'Create New Vhost',
            'lhs_cmd'   => '',
            'rhs_cmd'   => 'Create',
            'body'      => '
',
        ]);

        return '
        <div class="row">
          <h3>
            <i class="bi bi-globe"></i> Vhosts
            <a href="?o=vhosts&m=create" class="bslink" title="Add New Vhost">
              <small><i class="bi bi-plus-circle"></i></small>
            </a>
          </h3>
        </div>
        <div class="table-responsive">
          <table id="vhosts" class="table table-borderless table-striped w-100">
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
            <tfoot>
            </tfoot>
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
  $("#vhosts").DataTable({
    "processing": true,
    "serverSide": true,
    "ajax": "?x=json&o=vhosts&m=list",
    "order": [[ 15, "desc" ]],
    "scrollX": true,
    "columnDefs": [
      {"targets":0,   "className":"text-truncate", "width":"25%"},
      {"targets":1,   "className":"text-right", "width":"3rem"},
      {"targets":2,   "className":"text-center", "width":"0.5rem", "sortable": false},
      {"targets":3,   "width":"3rem"},
      {"targets":4,   "className":"text-right", "width":"3rem"},
      {"targets":5,   "className":"text-center", "width":"0.5rem", "sortable": false},
      {"targets":6,   "width":"3rem"},
      {"targets":7,   "className":"text-right", "width":"4rem"},
      {"targets":8,   "className":"text-center", "width":"0.5rem", "sortable": false},
      {"targets":9,   "width":"4rem"},
      {"targets":10,  "className":"text-right", "width":"4rem"},
      {"targets":11,  "className":"text-center", "width":"0.5rem", "sortable": false},
      {"targets":12,  "width":"4rem"},
      {"targets":13,  "className":"text-right", "width":"1rem", "sortable": false},
      {"targets":14,  "visible":false, "sortable": true},
      {"targets":15,  "visible":false, "sortable": true},
    ]
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

    public function modal_create_body(array $in): string
    {
        return '
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
        ';
    }
}
