<?php

declare(strict_types=1);
// lib/php/themes/bootstrap/sshm.php 20230703 - 20230703
// Copyright (C) 2015-2023 Mark Constable <markc@renta.net> (AGPL-3.0)

class Themes_Bootstrap_Sshm extends Themes_Bootstrap_Theme
{
    public function create(array $in): string
    {
        return $this->modal_content([
            'title'     => 'Create SSH Host',
            'action'    => 'create',
            'lhs_cmd'   => '',
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
            'rhs_cmd'   => 'Update',
            'body'      => $this->modal_body($in, $keys)
        ]);
    }

    public function delete(array $in): string
    {
        return $this->modal_content([
            'title'     => 'Remove SSH Host',
            'action'    => 'delete',
            'lhs_cmd'   => '',
            'rhs_cmd'   => 'Remove',
            'hidden'    => '
                <input type="hidden" name="name" value="' . $in['name'] . '">',
            'body'      => '
                  <p class="text-center">Are you sure you want to remove SSH Host for<br><b>' . $in['name'] . '</b></p>',
        ]);
    }

    /**
     * # Introducing shell exit strategies to trigger Bootstrap5 alerts
     * #
     * # exit 0        - success, no alert and continue
     * # exit 1-250    - error, with 'danger' alert and continue
     * # exit 251      - success, with 'success' alert and continue
     * # exit 252      - info, with 'info' alert and continue
     * # exit 253      - warning, with 'warning' alert and continue
     * # exit 254      - warning, with 'warning' alert and empty content
     * # exit 255      - error, with 'danger' alert and empty content
     * #
     * # 251/252/253 strip the first line to be used in alert message
     */
    public function list(array $in): string
    {
        $buf = '';
        if ($in['err'] === 254 || $in['err'] === 255) {
            $lvl = $in['err'] === 254 ? 'warning' : 'danger';
            util::log($in['ary'][0], $lvl);
        } else {
            if ($in['err'] === 253) {
                $msg = array_shift($in['ary']);
                util::log($msg, 'warning');
            }
            if ($in['err'] === 252) {
                $msg = array_shift($in['ary']);
                util::log($msg, 'info');
            }
            if ($in['err'] === 251) {
                $msg = array_shift($in['ary']);
                util::log($msg, 'success');
            }
            if ($in['err'] > 0) {
                util::log($in['ary'][0], 'danger');
            }

            foreach ($in['ary'] as $line) {
                $ary = preg_split('/\s+/', $line);
                $skey_buf = (empty($ary[4]) || $ary[4] === 'none') ? '' : '
              <a class="bslink" href="?o=sshm&m=shkey&skey=' . $ary[4] . '"><b>' . $ary[4] . '</b></a>';

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
        <div class="row">
          <h3>
            <i class="bi bi-key"></i> SSH Manager
            <a href="?o=sshm&m=create" class="bslink" title="Add new SSH host">
              <small><i class="bi bi-plus-circle"></i></small>
            </a>
          </h3>
        </div>
        <div class="table-responsive">
          <table id="sshm" class="table table-borderless table-striped w-100">
            <thead>
              <tr>
                <th>Name</th>
                <th>Host</th>
                <th>Port</th>
                <th>User</th>
                <th>SKey</th>
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
        <div class="modal fade" id="shkeymodal" tabindex="-1" role="dialog" aria-labelledby="shkeymodal" aria-hidden="true">
          <div class="modal-dialog" id="shkeydialog">
          </div>
        </div>
        <script>
$(document).ready(function() {
  $("#sshm").DataTable({
    "processing": true,
    "scrollX": true,
    "columnDefs": [
      {"targets":0, "className":"text-truncate"},
      {"targets":3, "className":"text-truncate"},
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

    public function shkey(string $name, string $body): string
    {
        return $this->modal_content([
            'title'     => 'SSH Key: <b>' . $name . '</b>',
            'action'    => '',
            'lhs_cmd'   => '',
            'rhs_cmd'   => '',
            'body'      => '
            <textarea rows="10" style="width:100%;">' . $body . '</textarea>',
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
                      <!-- <input type="text" class="form-control" id="skey" name="skey" value="' . $in['skey'] . '"> -->
                    </div>
                  </div>
                    ';
    }
}
