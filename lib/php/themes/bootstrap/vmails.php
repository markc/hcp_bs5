<?php
// lib/php/themes/bootstrap/vmails.php 20170101 - 20180826
// Copyright (C) 2015-2018 Mark Constable <markc@renta.net> (AGPL-3.0)

class Themes_Bootstrap_Vmails extends Themes_Bootstrap_Theme
{
    function update(array $in) : string
    {
error_log(__METHOD__);

        $remove = $this->modal([
            'id'      => 'removemodal',
            'title'   => 'Remove Mailbox',
            'action'  => 'delete',
            'footer'  => 'Remove',
            'body'    => '
                  <p class="text-center">Are you sure you want to remove this mailbox?<br><b>' . $in['user'] . '</b></p>',
        ]);

        return '
              <div class="col-12">
                <h3>
                  <a href="?o=vmails&m=list"><i class="fas fa-angle-double-left fa-fw"></i></a> Mailboxes
                  <a href="" title="Remove this Mailbox" data-toggle="modal" data-target="#removemodal">
                    <small><i class="fas fa-trash fa-fw cursor-pointer text-danger"></i></small>
                  </a>
                </h3>
              </div>
            </div><!-- END UPPER ROW -->
            <div class="row">
              <div class="col-12">
                <form method="post" action="' . $this->g->cfg['self'] . '">
                  <input type="hidden" name="c" value="' . $_SESSION['c'] . '">
                  <input type="hidden" name="o" value="' . $this->g->in['o'] . '">
                  <input type="hidden" name="i" value="' . $this->g->in['i'] . '">
                  <input type="hidden" name="user" value="' . $in['user'] . '">
                  <div class="row">
                    <div class="form-group col-4">
                      <label for="user">Email Address</label>
                      <input type="text" class="form-control" id="user" value="' . $in['user'] . '" disabled>
                    </div>
                    <div class="form-group col-2">
                      <label for="quota">Mailbox Quota</label>
                      <input type="number" class="form-control" name="quota" id="quota" value="' . intval($in['quota'] / 1000000) . '">
                    </div>
                    <div class="col-3">
                      <div class="form-group">
                        <label for="passwd1">Password</label>
                        <input type="password" class="form-control" name="passwd1" id="passwd1" value="">
                      </div>
                    </div>
                    <div class="col-3">
                      <div class="form-group">
                        <label for="passwd2">Confirm Password</label>
                        <input type="password" class="form-control" name="passwd2" id="passwd2" value="">
                      </div>
                    </div>
                  </div>
                  <div class="row">
                    <div class="col-4">
                      <div class="form-group">
                        <div class="custom-control custom-checkbox">
                          <input type="checkbox" class="custom-control-input" name="spamf" id="spamf"' . ($in['spamf'] ? ' checked' : '') . '>
                          <label class="custom-control-label" for="spamf">Spam Filter</label>
                        </div>
                      </div>
                    </div>
                    <div class="col-4">
                      <div class="form-group">
                        <div class="custom-control custom-checkbox">
                          <input type="checkbox" class="custom-control-input" name="active" id="active"' . ($in['active'] ? ' checked' : '') . '>
                          <label class="custom-control-label" for="active">Active</label>
                        </div>
                      </div>
                    </div>
                    <div class="col-4 text-right">
                    <div class="btn-group">
                      <a class="btn btn-secondary" href="?o=vmails&m=list">&laquo; Back</a>
                      <button type="submit" name="m" value="update" class="btn btn-primary">Save</button>
                    </div>
                  </div>
                </div>
              </form>
            </div>' . $remove;
    }

    public function list(array $in) : string
    {
error_log(__METHOD__);

        $create = $this->modal([
            'id'      => 'createmodal',
            'title'   => 'Create New Mailbox',
            'action'  => 'create',
            'footer'  => 'Create',
            'body'    => '
                  <div class="form-group">
                    <label for="user" class="form-control-label">Mailbox</label>
                    <input type="text" class="form-control" id="user" name="user">
                  </div>
                  <div class="form-group">
                    <div class="custom-control custom-checkbox">
                      <input type="checkbox" class="custom-control-input" name="spamf" id="spamf" checked>
                      <label class="custom-control-label" for="spamf">Spam Filter</label>
                    </div>
                  </div>',
        ]);

        return '
        <div class="col-12">
          <h3>
            <i class="fas fa-envelope fa-fw"></i> Mailboxes
            <a href="#" title="Add New Mailbox" data-toggle="modal" data-target="#createmodal">
              <small><i class="fas fa-plus-circle fa-fw"></i></small>
            </a>
          </h3>
        </div>
      </div><!-- END UPPER ROW -->
      <div class="row">
        <div class="table-responsive">
          <table id=vmails class="table table-sm" style="min-width:1100px;table-layout:fixed">
            <thead class="nowrap">
              <tr>
                <th>Email</th>
                <th>Domain</th>
                <th></th>
                <th>Usage&nbsp;</th>
                <th></th>
                <th>Quota</th>
                <th>Msg&nbsp;#&nbsp;</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
            </tbody>
          </table>
        </div>' . $create . '
        <script>
$(document).ready(function() {
  $("#vmails").DataTable({
    "processing": true,
    "serverSide": true,
    "ajax": "?x=json&o=vmails&m=list",
    "order": [[ 8, "desc" ]],
    "columnDefs": [
      {"targets":0, "className":"text-truncate", "width":"25%"},
      {"targets":1, "className":"text-truncate", "width":"20%"},
      {"targets":2, "className":"align-middle", "sortable": false},
      {"targets":3, "className":"text-right", "width":"4rem"},
      {"targets":4, "className":"text-center", "width":"0.5rem", "sortable": false},
      {"targets":5, "width":"4rem"},
      {"targets":6, "className":"text-right", "width":"3rem"},
      {"targets":7, "className":"text-right", "width":"4rem", "sortable": false},
      {"targets":8, "visible":false, "sortable": true}
    ]
  });
});
        </script>';
    }
}

?>
