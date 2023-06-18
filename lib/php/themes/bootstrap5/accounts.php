<?php

declare(strict_types=1);
// lib/php/themes/bootstrap/accounts.php 20170225 - 20230617
// Copyright (C) 2015-2023 Mark Constable <markc@renta.net> (AGPL-3.0)

/**
 * Summary of Themes_Bootstrap5_Accounts
 * @author Mark Constable
 * @copyright (c) 2023
 */
class Themes_Bootstrap5_Accounts extends Themes_Bootstrap5_Theme
{
    /**
     * Summary of create
     * @param array $in
     * @return string
     */
    public function create(array $in): string
    {
        elog(__METHOD__);

        return $this->editor($in);
    }

    /**
     * Summary of read
     * @param array $in
     * @return string
     */
    public function read(array $in): string
    {
        elog(__METHOD__);

        return $this->editor($in);
    }

    /**
     * Summary of update
     * @param array $in
     * @return string
     */
    public function update(array $in): string
    {
        elog(__METHOD__);

        return $this->editor($in);
    }

    /**
     * Summary of delete
     * @param array $in
     * @return string
     */
    public function delete(array $in): string
    {
        elog(__METHOD__);

        //        return $this->editor($in);
        if (util::is_post()) {
            return "Themes_Bootstrap5_Accounts goto plugin::delete()";
        } else {
            $usr = db::read('login', 'id', $this->g->in['i'], '', 'one');
            elog(var_export($usr, true));
            return $this->modal_content([
                'title' => 'Remove User',
                'action' => 'delete',
                'lhs_cmd' => '',
                'rhs_cmd' => 'Remove',
                'hidden' => '
                <input type="hidden" name="i" value="' . $this->g->in['i'] . '">',
                'body' => '
                <p class="text-center">Are you sure you want to remove this user?<br><b>' . $usr['login'] . '</b></p>',
            ]);
        }
    }

    /**
     * Summary of boo
     * @param array $in
     * @return string
     */
    private function boo(array $in): string
    {
        elog(__METHOD__);

        $login = db::read('login', 'id', $this->g->in['i'], '', 'one');

        return $this->modal_content([
            'title' => 'Remove User',
            'action' => 'delete',
            'lhs_cmd' => '',
            'rhs_cmd' => 'Remove',
            'hidden' => '
                <input type="hidden" name="i" value="' . $this->g->in['i'] . '">',
            'body' => '
                <p class="text-center">Are you sure you want to remove this user?<br><b>' . $login . '</b></p>',
        ]);
    }

    /**
     * Summary of list
     * @param array $in
     * @return string
     */
    public function list(array $in): string
    {
        elog(__METHOD__);

        return '
        <div class="row">
          <h3>
            <i class="bi bi-people-fill"></i> Accounts
            <a href="?o=accounts&m=create" class="bslink" title="Add new account">
              <small><i class="bi bi-plus-circle"></i></small>
            </a>
          </h3>
        </div>
        <div class="table-responsive">
          <table id="accounts" class="table table-borderless table-striped w-100">
            <thead>
              <tr>
                <th>User ID</th>
                <th>First Name</th>
                <th>Last Name</th>
                <th>Alt Email</th>
                <th>ACL</th>
                <th>Grp</th>
              </tr>
            </thead>
            <tbody>
            </tbody>
          </table>
        </div>
        <div class="modal fade" id="createmodal" tabindex="-1" role="dialog" aria-labelledby="createmodal" aria-hidden="true">
          <div class="modal-dialog" id="createdialog">
          </div>
        </div>
        <div class="modal fade" id="readmodal" tabindex="-1" role="dialog" aria-labelledby="readmodal" aria-hidden="true">
          <div class="modal-dialog" id="readdialog">
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
        <div class="modal fade" id="listmodal" tabindex="-1" role="dialog" aria-labelledby="listmodal" aria-hidden="true">
          <div class="modal-dialog" id="listdialog">
          </div>
        </div>
        <script>
$(document).ready(function() {
  $("#accounts").DataTable({
    "processing": true,
    "serverSide": true,
    "ajax": "?x=json&o=accounts&m=list",
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

    /**
     * Summary of editor
     * @param array $in
     * @return string
     */
    private function editor(array $in): string
    {
        elog(__METHOD__);

        extract($in);

        $aclgrp_buf = '';

        //       if (util::is_adm()) {
        $acl = $_SESSION['usr']['acl'];
        $grp = $_SESSION['usr']['grp'];
        $acl_ary = $grp_ary = [];
        foreach ($this->g->acl as $k => $v) {
            $acl_ary[] = [$v, $k];
        }
        $acl_buf = $this->dropdown($acl_ary, 'acl', "{$acl}", '', 'form-select');
        $res = db::qry('
 SELECT login,id
   FROM `accounts`
  WHERE acl = :0 OR acl = :1', ['0' => 0, '1' => 1]);

        foreach ($res as $k => $v) {
            $grp_ary[] = [$v['login'], $v['id']];
        }
        $grp_buf = $this->dropdown($grp_ary, 'grp', "{$grp}", '', 'form-select');

        $aclgrp_buf = '
                <div class="row">
                    <div class="col-6 mb-3">
                        <label for="acl" class="form-label">ACL</label>' . $acl_buf . '
                    </div>
                    <div class="col-6 mb-3">
                        <label for="grp" class="form-label">Group</label>' . $grp_buf . '
                    </div>
                </div>';

        if ($this->g->in['m'] === 'list') {
            $login = '';
            $altemail = '';
            $fname = '';
            $lname = '';
        }

        $body_buf = '
            <div class="row">
                <div class="col-6 mb-3">
                    <label for="login" class="form-label">Email ID</label>
                    <input type="email" class="form-control" id="login" name="login" value="' . $login . '" required>
                </div>
                <div class="col-6 mb-3">
                    <label for="altemail" class="form-label">Alt Email</label>
                    <input type="text" class="form-control" id="altemail" name="altemail" value="' . $altemail . '">
                </div>
            </div>
            <div class="row">
                <div class="col-6 mb-3">
                    <label for="fname" class="form-label">First Name</label>
                    <input type="text" class="form-control" id="fname" name="fname" value="' . $fname . '" required>
                </div>
                <div class="col-6 mb-3">
                    <label for="lname" class="form-label">Last Name</label>
                    <input type="text" class="form-control" id="lname" name="lname" value="' . $lname . '" required>
                </div>
            </div>' . $aclgrp_buf;

        if ($this->g->in['m'] === 'list' || $this->g->in['m'] === 'create') {
            return $this->modal_content([
                //                'id'=>'createmodal',
                'title' => 'Create new user',
                'action' => 'create',
                'lhs_cmd' => '',
                'rhs_cmd' => 'Create',
                'body' => $body_buf
            ]);
        } elseif ($this->g->in['m'] === 'read') {
            return $this->modal_content([
                'title' => 'Update user',
                'action' => 'update',
                'lhs_cmd' => 'Delete',
                'rhs_cmd' => 'Update',
                'body' => $body_buf
            ]);
        }
    }
}
