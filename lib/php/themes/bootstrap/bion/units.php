<?php
// lib/php/themes/bootstrap/bion/units.php 20190225 - 20190225
// Copyright (C) 2015-2019 Mark Constable <markc@renta.net> (AGPL-3.0)

class Themes_Bootstrap_Bion_Units extends Themes_Bootstrap_Theme
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
            'title'   => 'Create New Unit',
            'action'  => 'create',
            'footer'  => 'Create',
            'body'    => '
                  <div class="form-group">
                    <label for="name" class="form-control-label">Unit Name</label>
                    <input type="text" class="form-control" id="name" name="name" value="' . $name . '" required>
                  </div>
                  <div class="form-group">
                    <label for="port" class="form-control-label">Unit Port</label>
                    <input type="text" class="form-control" id="port" name="port" value="' . $port . '" required>
                  </div>
                  <div class="form-group">
                    <label for="link_user" class="form-control-label">Link User</label>
                    <input type="text" class="form-control" id="link_user" name="link_user" value="' . $link_user . '" required>
                  </div>
                  <div class="form-group">
                    <label for="link_admin" class="form-control-label">Link Admin</label>
                    <input type="text" class="form-control" id="link_admin" name="link_admin" value="' . $link_admin . '" required>
                  </div>
                  <div class="form-group">
                    <label for="link_files" class="form-control-label">Link Files</label>
                    <input type="text" class="form-control" id="link_files" name="link_files" value="' . $link_files . '" required>
                  </div>
                  <div class="form-group">
                    <label for="link_charts" class="form-control-label">Link Charts</label>
                    <input type="text" class="form-control" id="link_charts" name="link_charts" value="' . $link_charts . '" required>
                  </div>

                  '
        ]);

        return '
            <div class="col-12">
              <h3>
                <i class="fas fa-users fa-fw"></i> Units
                <a href="" title="Add new unit" data-toggle="modal" data-target="#createmodal">
                  <small><i class="fas fa-plus-circle fa-fw"></i></small>
                </a>
              </h3>
            </div>
          </div><!-- END UPPER ROW -->
          <div class="row">
            <div class="table-responsive">
              <table id="bion_units" class="table table-sm" style="min-width:1100px;table-layout:fixed">
                <thead class="nowrap">
                  <tr>
                    <th>Unit Name</th>
                    <th>Unit Port</th>
                    <th>Link User</th>
                    <th>Link Admin</th>
                    <th>Link Files</th>
                    <th>Link Charts</th>
                  </tr>
                </thead>
                <tbody>
                </tbody>
              </table>
            </div>' . $createmodal . '
            <script>
$(document).ready(function() {
  $("#bion_units").DataTable({
    "processing": true,
    "serverSide": true,
    "ajax": "?x=json&o=bion_units&m=list",
    "columnDefs": [
      {"targets":0,   "width":"12%"},
      {"targets":1,   "width":"8%"},
      {"targets":2,   "className":"text-truncate", "width":"20%"},
      {"targets":3,   "className":"text-truncate", "width":"20%"},
      {"targets":4,   "className":"text-truncate", "width":"20%"},
      {"targets":5,   "className":"text-truncate", "width":"20%"},
    ]
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
            'title'   => 'Remove Unit',
            'action'  => 'delete',
            'footer'  => 'Remove',
            'hidden'  => '
                <input type="hidden" name="i" value="' . $id . '">',
            'body'    => '
                <p class="text-center">Are you sure you want to remove this unit?<br><b>' . $name . '</b></p>',
        ]);

        $header = 'Update Site';
        $submit = '
                <a class="btn btn-secondary" href="?o=bion_units&m=list">&laquo; Back</a>
                <button type="submit" name="m" value="update" class="btn btn-primary">Update</button>';

        return '
          <div class="col-12">
            <h3>
              <a href="?o=bion_clients&m=list"><i class="fas fa-angle-double-left fa-fw"></i></a> Units
              <a href="" title="Remove this unit" data-toggle="modal" data-target="#removemodal">
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
                    <label for="name">Unit Name</label>
                    <input type="text" class="form-control" id="name" name="name" value="' . $name . '" required>
                  </div>
                </div>
                <div class="col-12 col-sm-6 col-lg-4">
                  <div class="form-group">
                    <label for="city">Unit City</label>
                    <input type="text" class="form-control" id="city" name="city" value="' . $city . '" required>
                  </div>
                </div>
                <div class="col-12 col-sm-6 col-lg-4">
                  <div class="form-group">
                    <label for="postcode">Unit Postcode</label>
                    <input type="text" class="form-control" id="postcode" name="postcode" value="' . $postcode . '" required>
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
