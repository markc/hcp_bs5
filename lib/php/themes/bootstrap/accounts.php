<?php
// lib/php/themes/bootstrap/accounts.php 20170225 - 20180430
// Copyright (C) 2015-2018 Mark Constable <markc@renta.net> (AGPL-3.0)

class Themes_Bootstrap_Accounts extends Themes_Bootstrap_Theme
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

        $buf = '';
        $num = count($in);

        foreach ($in as $a) {
            extract($a);
            $buf .= '
        <tr>
          <td class="text-truncate">
            <a href="?o=accounts&m=read&i=' . $id . '" title="Show account: ' . $id . '">
              <strong>' . $login . '</strong>
            </a>
          </td>
          <td>' . $fname . '</td>
          <td>' . $lname . '</td>
          <td class="text-truncate">' . $altemail . '</td>
          <td>' . $this->g->acl[$acl] . '</td>
          <td>' . $grp . '</td>
        </tr>';
        }

        return '
          <div class="col-12">
            <h3>
              <i class="fas fa-users fa-fw"></i> Accounts
              <a href="?o=accounts&m=create" title="Add new account">
                <small><i class="fas fa-plus-circle fa-fw"></i></small>
              </a>
            </h3>
          </div>
        </div><!-- END UPPER ROW -->
        <div class="row">
          <div class="table-responsive">
            <table id=accounts class="table table-sm" style="min-width:1000px;table-layout:fixed">
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
              <tbody>' . $buf . '
              </tbody>
            </table>
          </div>
          <script>$(document).ready(function() { $("#accounts").DataTable({"order": []}); });</script>';
    }

    private function editor(array $in) : string
    {
error_log(__METHOD__);

        extract($in);

        if ($this->g->in['m'] === 'create') {
            $header = 'Add Account';
            $switch = '';
            $submit = '
                <a class="btn btn-secondary" href="?o=accounts&m=list">&laquo; Back</a>
                <button type="submit" name="m" value="create" class="btn btn-primary">Add This Account</button>';
        } else {
            $header = 'Update Account';
//            $switch = util::is_adm() && (!util::is_usr(0) && !util::is_usr(1)) ? '
            $switch = !util::is_usr($id) && (util::is_acl(0) || util::is_acl(1)) ? '
                  <a class="btn btn-outline-primary" href="?o=accounts&m=switch_user&i=' . $id . '">Switch to ' . $login . '</a>' : '';
            $submit = '
                <a class="btn btn-secondary" href="?o=accounts&m=list">&laquo; Back</a>
                <a class="btn btn-danger" href="?o=accounts&m=delete&i=' . $id . '" title="Remove this account" onClick="javascript: return confirm(\'Are you sure you want to remove ' . $login . '?\')">Remove</a>
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
            <h3><a href="?o=accounts&m=list">&laquo;</a> ' . $header . '</h3>
          </div>
        </div><!-- END UPPER ROW -->
        <div class="row">
          <div class="col-12">
            <form method="post" action="' . $this->g->cfg['self'] . '">
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
          </div>';
    }
}

?>
