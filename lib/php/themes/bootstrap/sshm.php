<?php

declare(strict_types=1);
// lib/php/themes/bootstrap/sshm.php 20230703 - 20230708
// Copyright (C) 2015-2023 Mark Constable <markc@renta.net> (AGPL-3.0)

class Themes_Bootstrap_Sshm extends Themes_Bootstrap_Theme
{
    public function create(array $in): string
    {
        return $this->modal_content([
            'title'     => 'Create SSH Host',
            'action'    => 'create',
            'lhs_cmd'   => '',
            'mid_cmd'   => 'Help',
            'rhs_cmd'   => 'Create',
            'body'      => $this->modal_body($in)
        ]);
    }

    public function update(array $in): string
    {
        return $this->modal_content([
            'title'     => 'Update SSH Host',
            'action'    => 'update',
            'lhs_cmd'   => '',
            'mid_cmd'   => 'Help',
            'rhs_cmd'   => 'Update',
            'body'      => $this->modal_body($in)
        ]);
    }

    public function delete(array $in): string
    {
        return $this->modal_content([
            'title'     => 'Remove SSH Host',
            'action'    => 'delete',
            'lhs_cmd'   => '',
            'mid_cmd'   => 'Help',
            'rhs_cmd'   => 'Remove',
            'hidden'    => '
                <input type="hidden" name="name" value="' . $in['name'] . '">',
            'body'      => '
                  <p class="text-center">Are you sure you want to remove SSH Host for<br><b>' . $in['name'] . '</b></p>',
        ]);
    }

    public function list(array $in): string
    {
        $buf = '';
        // TODO: move this mess to a dedicated util::alert method
        if (!empty($in)) {
            foreach ($in['ary'] as $line) {
                $ary = preg_split('/\s+/', $line);
                $skey_buf = (empty($ary[4]) || $ary[4] === 'none') ? '' : '
              <a class="bslink" href="?o=sshm&m=key_read&skey=' . $ary[4] . '"><b>' . $ary[4] . '</b></a>';

                $buf .=
                    '
          <tr>
            <td>
              <a class="bslink" href="?o=sshm&m=update&name=' . $ary[0] . '"><b>' . $ary[0] . '</b></a>
            </td>
            <td>' . $ary[1] . '</td>
            <td>' . $ary[2] . '</td>
            <td>' . $ary[3] . '</td>
            <td>' . $skey_buf . '</td>
            <td>
              <a class="bslink" href="?o=sshm&m=delete&name=' . $ary[0] . '" title="Delete SSH Host: ' . $ary[0]  . '">
                <i class="bi bi-trash cursor-pointer text-danger"></i>
              </a>
            </td>
          </tr>';
            }
        }

        return '
        <div class="row mb-1">
          <div class="d-flex justify-content-between">
            <h3>
              <i class="bi bi-key"></i> SSH Manager
              <a href="?o=sshm&m=create" class="bslink" title="Add new SSH Host config">
                <small><i class="bi bi-plus-circle"></i></small>
              </a>
            </h3>
            <div>
              <a href="?o=sshm&m=key_list" class="btn btn-primary btn-sm" title="Show SSH Keys">
               SSH Keys
              </a>
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
            <tbody>' . $buf . '
            </tbody>
          </table>
        </div>
        <div class="modal fade" id="createmodal" tabindex="-1" role="dialog" aria-labelledby="createmodal" aria-hidden="true">
          <div class="modal-dialog" id="createdialog">
          </div>
        </div>
        <div class="modal fade" id="updatemodal" tabindex="-1" role="dialog" aria-labelledby="readmodal" aria-hidden="true">
          <div class="modal-dialog" id="updatedialog">
          </div>
        </div>
        <div class="modal fade" id="deletemodal" tabindex="-1" role="dialog" aria-labelledby="deletemodal" aria-hidden="true">
          <div class="modal-dialog" id="deletedialog">
          </div>
        </div>
        <div class="modal fade" id="key_readmodal" tabindex="-1" role="dialog" aria-labelledby="shkeymodal" aria-hidden="true">
          <div class="modal-dialog" id="key_readdialog">
          </div>
        </div>
        <div class="modal fade" id="helpmodal" tabindex="-1" role="dialog" aria-labelledby="shkeymodal" aria-hidden="true">
          <div class="modal-dialog" id="helpdialog">
          </div>
        </div>
        <script>
$(document).ready(function() {
  $("#sshm").DataTable({
    "processing": true,
    "scrollX": true,
    "columnDefs": [
      {"targets":2, "className":"text-truncate"},
      {"targets":5, "className":"text-right", "width":"3rem", "sortable": false},
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

    public function help(string $name, string $body): string
    {
        return $this->modal_content([
            'title'     => 'Help for <b>sshm ' . $name . '</b>',
            'body'      => '
            <pre>' . $body . '</pre>',
        ]);
    }

    private function modal_body(array $in): string
    {
        elog('sshm-modal_body.in=' . var_export($in, true));

        $keys = array_pop($in);
        elog('keys=' . var_export($keys, true));

        array_unshift($keys, 'none');

        foreach ($keys as $k) {
            $skey_ary[] = [$k, $k];
        }
        elog('skey_ary=' . var_export($skey_ary, true));

        $skey_buf = $this->dropdown($skey_ary, 'skey', "{$in['skey']}", '', 'form-select');
        elog('skey_buf=' . $skey_buf);

        return '
                  <div class="row mb-3">
                    <div class="col-md-6">
                      <label for="name" class="form-label">Name</label>
                      <input type="text" class="form-control" id="name" name="name" value="' . $in['name'] . '">
                    </div>
                    <div class="col-md-6">
                      <label for="host" class="form-label">Host</label>
                      <input type="text" class="form-control" id="host" name="host" value="' . $in['host'] . '">
                    </div>
                  </div>
                  <div class="row mb-3">
                    <div class="col-md-3">
                      <label for="port" class="form-label">Port</label>
                      <input type="text" class="form-control" id="port" name="port" value="' . $in['port'] . '">
                    </div>
                    <div class="col-md-4">
                      <label for="user" class="form-label">User</label>
                      <input type="text" class="form-control" id="user" name="user" value="' . $in['user'] . '">
                    </div>
                    <div class="col-md-5">
                      <label for="skey" class="form-label">SSH key</label>' . $skey_buf . '
                    </div>
                  </div>
                    ';
    }

    public function key_create(array $in): string
    {
        return $this->modal_content([
            'title'     => 'Create SSH Key',
            'action'    => 'key_create',
            'lhs_cmd'   => '',
            'mid_cmd'   => 'Help',
            'rhs_cmd'   => 'Create',
            'body'      => $this->modal_key_body($in)
        ]);
    }

    public function key_read(string $name, string $body): string
    {
        return $this->modal_content([
            'title'     => 'SSH Key: <b>' . $name . '</b>',
            'body'      => '
            <textarea rows="12" style="width:100%;">' . $body . '</textarea>',
        ]);
    }

    public function key_delete(array $in): string
    {
        return $this->modal_content([
            'title'     => 'Remove SSH Key',
            'action'    => 'key_delete',
            'lhs_cmd'   => '',
            'mid_cmd'   => 'Help',
            'rhs_cmd'   => 'Remove',
            'hidden'    => '
                <input type="hidden" id="key_name" name="key_name" value="' . $in['key_name'] . '">',
            'body'      => '
                  <p class="text-center">Are you sure you want to remove SSH Key<br><b>' . $in['key_name'] . '</b></p>',
        ]);
    }

    public function key_list(array $in): string
    {
        $buf = '';
        // TODO: move this mess to a dedicated util::alert method
        if ($in['err'] === 254 || $in['err'] === 255) {
            $lvl = $in['err'] === 254 ? 'warning' : 'danger';
            util::log($in['ary'][0], $lvl);
        } else {
            foreach ($in['ary'] as $line) {
                $ary = preg_split('/\s+/', $line);
                $buf .=
                    '
          <tr>
            <td>
              <a class="bslink" href="?o=sshm&m=key_read&skey=' . $ary[0] . '"><b>' . $ary[0] . '</b></a>
            </td>
            <td>' . $ary[1] . '</td>
            <td>' . $ary[2] . '</td>
            <td>' . $ary[3] . '</td>
            <td>' . $ary[4] . '</td>
            <td>
              <a class="bslink" href="?o=sshm&m=key_delete&key_name=' . $ary[0] . '" title="Delete SSH Key: ' . $ary[0]  . '">
                <i class="bi bi-trash cursor-pointer text-danger"></i>
              </a>
            </td>
          </tr>';
            }
        }

        return '
        <div class="row mb-1">
          <div class="d-flex justify-content-between">
            <h3>
              <i class="bi bi-key"></i> SSH Keys
              <a href="?o=sshm&m=key_create" class="bslink" title="Add new SSH Key">
                <small><i class="bi bi-plus-circle"></i></small>
              </a>
            </h3>
            <div>
              <a href="?o=sshm" class="btn btn-primary btn-sm" title="Show SSH Hosts">
               SSH Hosts
              </a>
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
            <tbody>' . $buf . '
            </tbody>
          </table>
        </div>
        <div class="modal fade" id="key_createmodal" tabindex="-1" role="dialog" aria-labelledby="createmodal" aria-hidden="true">
          <div class="modal-dialog" id="key_createdialog">
          </div>
        </div>
        <div class="modal fade" id="key_readmodal" tabindex="-1" role="dialog" aria-labelledby="shkeymodal" aria-hidden="true">
          <div class="modal-dialog" id="key_readdialog">
          </div>
        </div>
        <div class="modal fade" id="key_deletemodal" tabindex="-1" role="dialog" aria-labelledby="deletemodal" aria-hidden="true">
          <div class="modal-dialog" id="key_deletedialog">
          </div>
        </div>
        <div class="modal fade" id="helpmodal" tabindex="-1" role="dialog" aria-labelledby="shkeymodal" aria-hidden="true">
          <div class="modal-dialog" id="helpdialog">
          </div>
        </div>
        <script>
$(document).ready(function() {
  $("#sshm_keys").DataTable({
    "processing": true,
    "scrollX": true,
    "columnDefs": [
      {"targets":2, "className":"text-truncate"},
      {"targets":5, "className":"text-right", "width":"3rem", "sortable": false},
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

    private function modal_key_body(array $in): string
    {
        elog('sshm-modal_key_body.in=' . var_export($in, true));
        return '
                  <div class="row mb-3">
                    <div class="col-md-4">
                      <label for="key_name" class="form-label">Key Name</label>
                      <input type="text" class="form-control" id="key_name" name="key_name" value="' . $in['key_name'] . '">
                    </div>
                    <div class="col-md-4">
                      <label for="key_cmnt" class="form-label">Key Comment</label>
                      <input type="text" class="form-control" id="key_cmnt" name="key_cmnt" value="' . $in['key_cmnt'] . '" placeholder="Optional">
                    </div>
                    <div class="col-md-4">
                      <label for="key_pass" class="form-label">Key Password</label>
                      <input type="text" class="form-control" id="key_pass" name="key_pass" value="' . $in['key_pass'] . '" placeholder="Optional">
                    </div>
                  </div>';
    }
}
