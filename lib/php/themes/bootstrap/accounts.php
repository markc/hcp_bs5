<?php
// lib/php/themes/bootstrap/accounts.php 20170225 - 20200414
// Copyright (C) 2015-2020 Mark Constable <markc@renta.net> (AGPL-3.0)

class Themes_Bootstrap_Accounts extends Themes_Bootstrap_Theme
{
    public function create(array $in) : string
    {
elog(__METHOD__);

        return $this->editor($in);
    }

    public function read(array $in) : string
    {
elog(__METHOD__);

        return $this->editor($in);
    }

    public function update(array $in) : string
    {
elog(__METHOD__);

        return $this->editor($in);
    }

    public function list(array $in) : string
    {
elog(__METHOD__.' '.var_export($in, true));

        extract($in);
        $aclgrp_buf = '';

        if (util::is_adm()) {
            $acl = $_SESSION['usr']['acl'];
            $grp = $_SESSION['usr']['grp'];
            $acl_ary = $grp_ary = [];
            foreach($this->g->acl as $k => $v) $acl_ary[] = [$v, $k];
            $acl_buf = $this->dropdown($acl_ary, 'acl', "$acl", '', 'custom-select');
            $res = db::qry("
 SELECT login,id
   FROM `accounts`
  WHERE acl = :0 OR acl = :1", ['0' => 0, '1' => 1]);

            foreach($res as $k => $v) $grp_ary[] = [$v['login'], $v['id']];
            $grp_buf = $this->dropdown($grp_ary, 'grp', "$grp", '', 'custom-select');

            $aclgrp_buf = '
                  <div class="row">
                    <div class="col-6">
                      <div class="form-group">
                        <label for="acl" class="form-control-label">ACL</label>' . $acl_buf . '
                      </div>
                    </div>
                    <div class="col-6">
                      <div class="form-group">
                        <label for="grp" class="form-control-label">Group</label>' . $grp_buf . '
                      </div>
                    </div>
                  </div>';
        }

        $createmodal = $this->modal([
            'id'      => 'createmodal',
            'title'   => 'Create New Account',
            'action'  => 'create',
            'footer'  => 'Create',
            'body'    => '
                  <div class="form-group">
                    <label for="login" class="form-control-label">Email ID</label>
                    <input type="email" class="form-control" id="login" name="login" value="" required>
                  </div>
                  <div class="form-group">
                    <label for="fname" class="form-control-label">First Name</label>
                    <input type="text" class="form-control" id="fname" name="fname" value="" required>
                  </div>
                  <div class="form-group">
                    <label for="lname" class="form-control-label">Last Name</label>
                    <input type="text" class="form-control" id="lname" name="lname" value="" required>
                  </div>
                  <div class="form-group">
                    <label for="altemail" class="form-control-label">Alt Email</label>
                    <input type="text" class="form-control" id="altemail" name="altemail" value="">
                  </div>' . $aclgrp_buf,
        ]);

        return '
        <div class="col-12">
          <h3>
            <i class="fas fa-users fa-fw"></i> Accounts
            <a href="" title="Add new account" data-toggle="modal" data-target="#createmodal">
              <small><i class="fas fa-plus-circle fa-fw"></i></small>
            </a>
          </h3>
        </div>
      </div><!-- END UPPER ROW -->
      <div class="row">
        <div class=col-12>
          <table id=accounts class="table table-sm" style="min-width:1100px;table-layout:fixed">
            <thead class="nowrap">
              <tr>
                <th class="w-25">User ID</th>
                <th>First Name</th>
                <th>Last Name</th>
                <th class="w-25">Alt Email</th>
                <th>ACL</th>
                <th>Grp</th>
              </tr>
            </thead>
            <tbody>
            </tbody>
          </table>
        </div>' . $createmodal . '
        <script>
$(document).ready(function() {
  $("#accounts").DataTable({
    "processing": true,
    "serverSide": true,
    "ajax": "?x=json&o=accounts&m=list",
//    "order": [[ 8, "desc" ]],
    "scrollX": true,
    "columnDefs": [
      {"targets":0, "className":"text-truncate"},
      {"targets":3, "className":"text-truncate"},
    ]
  });
});
        </script>';
    }

    private function editor(array $in) : string
    {
elog(__METHOD__);

        extract($in);

        $removemodal = $this->modal([
            'id'      => 'removemodal',
            'title'   => 'Remove User',
            'action'  => 'delete',
            'footer'  => 'Remove',
            'hidden'  => '
                <input type="hidden" name="i" value="' . $in['id'] . '">',
            'body'    => '
                <p class="text-center">Are you sure you want to remove this user?<br><b>' . $in['login'] . '</b></p>',
        ]);

        if ($this->g->in['m'] === 'create') {
            $header = 'Add Account';
            $switch = '';
            $submit = '
                <a class="btn btn-secondary" href="?o=accounts&m=list">&laquo; Back</a>
                <button type="submit" name="m" value="create" class="btn btn-primary">Add This Account</button>';
        } else {
            $header = 'Update Account';
            $switch = !util::is_usr($id) && (util::is_acl(0) || util::is_acl(1)) ? '
                <a class="btn btn-outline-primary" href="?o=accounts&m=switch_user&i=' . $id . '">Switch to ' . $login . '</a>' : '';
            $submit = '
                <a class="btn btn-secondary" href="?o=accounts&m=list">&laquo; Back</a>
                <button type="submit" name="m" value="update" class="btn btn-primary">Update</button>';
        }

        if (util::is_adm()) {
            $acl_ary = $grp_ary = [];
            foreach($this->g->acl as $k => $v) $acl_ary[] = [$v, $k];
            $acl_buf = $this->dropdown($acl_ary, 'acl', "$acl", '', 'custom-select');
            $res = db::qry("
 SELECT login,id
   FROM `accounts`
  WHERE acl = :0 OR acl = :1", ['0' => 0, '1' => 1]);

            foreach($res as $k => $v) $grp_ary[] = [$v['login'], $v['id']];
            $grp_buf = $this->dropdown($grp_ary, 'grp', "$grp", '', 'custom-select');
            $aclgrp_buf = '
                <div class="form-group">
                  <label for="acl">ACL</label><br>' . $acl_buf . '
                </div>
                <div class="form-group">
                  <label for="grp">Group</label><br>' . $grp_buf . '
                </div>';
        } else {
            $aclgrp_buf = '';
            $anotes_buf = '';
        }

        return '
          <div class="col-12">
            <h3>
              <a href="?o=accounts&m=list"><i class="fas fa-angle-double-left fa-fw"></i></a> Accounts
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
                    <label for="login">UserID</label>
                    <input type="email" class="form-control" id="login" name="login" value="' . $login . '" required>
                  </div>
                  <div class="form-group">
                    <label for="altemail">Alt Email</label>
                    <input type="text" class="form-control" id="altemail" name="altemail" value="' . $altemail . '">
                  </div>
                </div>
                <div class="col-12 col-sm-6 col-lg-4">
                  <div class="form-group">
                    <label for="fname">First Name</label>
                    <input type="text" class="form-control" id="fname" name="fname" value="' . $fname . '" required>
                  </div>
                  <div class="form-group">
                    <label for="lname">Last Name</label>
                    <input type="text" class="form-control" id="lname" name="lname" value="' . $lname . '" required>
                  </div>
                </div>
                <div class="col-12 col-sm-6 col-lg-4">' . $aclgrp_buf . '
                </div>
              </div>
              <div class="row">
                <div class="col-12 col-sm-6">' . $switch . '
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
