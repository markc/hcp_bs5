<?php
// lib/php/themes/bootstrap/bion/clients.php 20190225 - 20190427
// Copyright (C) 2015-2019 Mark Constable <markc@renta.net> (AGPL-3.0)

class Themes_Bootstrap_Bion_Clients extends Themes_Bootstrap_Theme
{
    public function create(array $in) : string
    {
error_log(__METHOD__);

        return $this->editor($in);
    }

    public function read(array $in) : string
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

        extract($in);

        $createmodal = $this->modal([
            'id'      => 'createmodal',
            'title'   => 'Create New Client',
            'action'  => 'create',
            'footer'  => 'Create',
            'body'    => '
                  <div class="form-group">
                    <label for="name" class="form-control-label">Client Name</label>
                    <input type="text" class="form-control" id="name" name="name" value="' . $name . '" required>
                  </div>
                  <div class="form-group">
                    <label for="login" class="form-control-label">Client Login</label>
                    <input type="text" class="form-control" id="login" name="login" value="' . $login . '" required>
                  </div>
                  <div class="form-group">
                    <label for="admin" class="form-control-label">Client Admin</label>
                    <input type="text" class="form-control" id="admin" name="admin" value="' . $admin . '" required>
                  </div>'
        ]);

        return '
            <div class="col-12">
              <h3>
                <i class="fas fa-users fa-fw"></i> Clients
                <a href="" title="Add new client" data-toggle="modal" data-target="#createmodal">
                  <small><i class="fas fa-plus-circle fa-fw"></i></small>
                </a>
              </h3>
            </div>
          </div><!-- END UPPER ROW -->
          <div class="row">
            <div class="table-responsive">
              <table id="bion_clients" class="table table-sm" style="min-width:1100px;table-layout:fixed">
                <thead class="nowrap">
                  <tr>
                    <th class="w-25">Client Name</th>
                    <th>Client Login</th>
                    <th>Admin Login</th>
                  </tr>
                </thead>
                <tbody>
                </tbody>
              </table>
            </div>' . $createmodal . '
            <script>
$(document).ready(function() {
  $("#bion_clients").DataTable({
    "processing": true,
    "serverSide": true,
    "ajax": "?x=json&o=bion_clients&m=list"
  });
});
          </script>';
    }

    private function editor(array $in) : string
    {
error_log(__METHOD__);

        extract($in);

        $removemodal = $this->modal([
            'id'      => 'removemodal',
            'title'   => 'Remove Client',
            'action'  => 'delete',
            'footer'  => 'Remove',
            'hidden'  => '
                <input type="hidden" name="i" value="' . $id . '">',
            'body'    => '
                <p class="text-center">Are you sure you want to remove this client?<br><b>' . $name . '</b></p>',
        ]);

        $header = 'Update Client';
        $submit = '
                <a class="btn btn-secondary" href="?o=bion_clients&m=list">&laquo; Back</a>
                <button type="submit" name="m" value="update" class="btn btn-primary">Update</button>';

        return '
          <div class="col-12">
            <h3>
              <a href="?o=bion_clients&m=list"><i class="fas fa-angle-double-left fa-fw"></i></a> Clients
              <a href="" title="Remove this user" data-toggle="modal" data-target="#removemodal">
                <small><i class="fas fa-trash fa-fw cursor-pointer text-danger"></i></small></a>
            </h3>
          </div>
        </div><!-- END UPPER ROW -->
        <div class="row">
          <div class="col-12">
            <form method="post" action="' . $this->g->cfg['self'] . '">
              <input type="hidden" name="c" value="' . $_SESSION['c'] . '">
              <input type="hidden" name="o" value="' . $this->g->in['o'] . '">
              <input type="hidden" name="i" value="' . $id . '">
              <div class="row">
                <div class="col-12 col-sm-6 col-lg-4">
                  <div class="form-group">
                    <label for="name">Client Name</label>
                    <input type="text" class="form-control" id="name" name="name" value="' . $name . '" required>
                  </div>
                </div>
                <div class="col-12 col-sm-6 col-lg-4">
                  <div class="form-group">
                    <label for="login">Client Login</label>
                    <input type="text" class="form-control" id="login" name="login" value="' . $login . '" required>
                  </div>
                </div>
                <div class="col-12 col-sm-6 col-lg-4">
                  <div class="form-group">
                    <label for="admin">Client Admin</label>
                    <input type="text" class="form-control" id="admin" name="admin" value="' . $admin . '" required>
                  </div>
                </div>
              </div>
              <div class="row">
                <div class="col-12 col-sm-6">
                </div>
                <div class="col-12 col-sm-6 text-right">
                  <div class="btn-group">' . $submit . '
                  </div>
                </div>
              </div>
            </form>
          </div>' . $removemodal;
    }
}

?>
