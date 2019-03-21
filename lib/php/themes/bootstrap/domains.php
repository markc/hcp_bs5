<?php
// lib/php/themes/bootstrap/domains.php 20170225 - 20180520
// Copyright (C) 2015-2018 Mark Constable <markc@renta.net> (AGPL-3.0)

class Themes_Bootstrap_Domains extends Themes_Bootstrap_Theme
{
    public function create(array $in) : string
    {
error_log(__METHOD__);

        return $this->editor($in);
    }

    public function update(array $in) : string
    {
error_log(__METHOD__);

        return $this->editor($in);
    }

    public function list(array $in) : string
    {
error_log(__METHOD__);
var_export($in, true);

        $create = $this->modal([
            'id'      => 'createmodal',
            'title'   => 'Create DNS Zone',
            'action'  => 'create',
            'footer'  => 'Create',
            'body'    => '
            <div class="form-group">
                <label for="domain" class="form-control-label">Domain</label>
                <input type="text" class="form-control" id="domain" name="domain">
              </div>',
        ]);

        $remove = $this->modal([
            'id'      => 'removemodal',
            'title'   => 'Remove DNS Zone',
            'action'  => 'delete',
            'footer'  => 'Remove',
            'hidden'  => '
                <input type="hidden" id="removemodalid" name="i" value="">',
            'body'    => '
                <p class="text-center">Are you sure you want to remove this domain?<br><b id="removemodalname"></b></p>',
        ]);

        return '
          <div class="col-12">
            <h3>
              <i class="fas fa-globe fa-fw"></i> Domains
              <a href="#" title="Add new domain" data-toggle="modal" data-target="#createmodal">
                <small><i class="fas fa-plus-circle fa-fw"></i></small>
              </a>
            </h3>
          </div>
        </div><!-- END UPPER ROW -->
        <div class="row">
          <div class="table-responsive">
            <table id="domains" class="table table-sm" style="min-width:1100px;table-layout:fixed">
              <thead>
                <tr>
                  <th>Name</th>
                  <th>Type</th>
                  <th>Records</th>
                  <th>Serial</th>
                  <th></th>
                </tr>
              </thead>
              <tbody>
              </tbody>
            </table>
          </div>
        </div>' . $create . $remove . '
        <script>
$(document).ready(function() {
  $("#domains").DataTable({
    "processing": true,
    "serverSide": true,
    "ajax": "?x=json&o=domains&m=list",
    "order": [[ 5, "desc" ]],
    "columnDefs": [
      {"targets":0, "className":"text-truncate", "width":"40%"},
      {"targets":4, "width":"2rem", "className":"text-right", "sortable": false},
      {"targets":5, "visible":false},
    ],
  });
  $(document).on("click", ".serial", {}, (function() {
    var a = $(this)
    $.post("?x=text&increment=1&" + this.toString().split("?")[1], function(data) {
      $(a).text(data);
    });
    return false;
  }));
  $(document).on("click", ".delete", {}, function() {
    var row = $(this).closest("tr");
    $("#removemodalid").val($(this).attr("data-rowid"));
    $("#removemodalname").text($(this).attr("data-rowname"));
  });
});
        </script>';
    }

    private function editor(array $in) : string
    {
error_log(__METHOD__);

        $domain = $in['name'];
        $soa = isset($in['soa'])
            ? explode(' ', $in['soa'])
            : ['', '', '', 7200, 540, 604800, 300];

        if ($this->g->in['m'] === 'create') {
            $serial = $hidden = '';
            $header = 'Add Domain';
            $submit = '
                <a class="btn btn-secondary" href="?o=domains&m=list">&laquo; Back</a>
                <button type="submit" id="m" name="m" value="create" class="btn btn-primary">Add Domain</button>';
        } else {
            $serial = '&nbsp;&nbsp;<small>Serial: ' . $soa[2] . '</small>';
            $header = $domain;
            $submit = '
                <a class="btn btn-secondary" href="?o=domains&m=list">&laquo; Back</a>
                <button type="submit" id="m" name="m" value="update" class="btn btn-primary">Update</button>';
            $hidden = '
            <input type="hidden" name="serial" value="' . $soa[2] . '">';
        }

        return '
          <div class="col-12">
          <h3>
            <i class="fa fa-globe fa-fw"></i>  ' . $header . $serial . '
            <a href="" title="Add new domain" data-toggle="modal" data-target="#createmodal">
              <small><i class="fas fa-plus-circle fa-fw"></i></small></a>
          </h3>
          </div>
        </div><!-- END UPPER ROW -->
        <div class="row">
          <div class="col-12">
            <form method="post" action="' . $this->g->cfg['self'] . '">
              <input type="hidden" name="c" value="' . $_SESSION['c'] . '">
              <input type="hidden" name="o" value="' . $this->g->in['o'] . '">
              <input type="hidden" name="i" value="' . $this->g->in['i'] . '">' . $hidden . '
              <div class="row">
                <div class="col-3">
                  <div class="form-group">
                    <label for="primary">Primary</label>
                    <input type="text" class="form-control" id="primary" name="primary" value="' . $soa[0] . '" required>
                  </div>
                </div>
                <div class="col-3">
                  <div class="form-group">
                    <label for="email">Email</label>
                    <input type="text" class="form-control" id="email" name="email" value="' . $soa[1] . '" required>
                  </div>
                </div>
                <div class="col-1">
                  <div class="form-group">
                    <label for="refresh">Refresh</label>
                    <input type="text" class="form-control" id="refresh" name="refresh" value="' . $soa[3] . '" required>
                  </div>
                </div>
                <div class="col-1">
                  <div class="form-group">
                    <label for="retry">Retry</label>
                    <input type="text" class="form-control" id="retry" name="retry" value="' . $soa[4] . '" required>
                  </div>
                </div>
                <div class="col-2">
                  <div class="form-group">
                    <label for="expire">Expire</label>
                    <input type="text" class="form-control" id="expire" name="expire" value="' . $soa[5] . '" required>
                  </div>
                </div>
                <div class="col-2">
                  <div class="form-group">
                    <label for="ttl">TTL</label>
                    <input type="text" class="form-control" id="ttl" name="ttl" value="' . $soa[6] . '" required>
                  </div>
                </div>
              </div>
              <div class="row">
                <div class="col-12 text-right">
                  <div class="btn-group">' . $submit . '
                  </div>
                </div>
              </div>
            </form>
          </div>';
    }
}
