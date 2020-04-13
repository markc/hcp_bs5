<?php
// lib/php/themes/bootstrap/vmails.php 20170101 - 20200413
// Copyright (C) 2015-2020 Mark Constable <markc@renta.net> (AGPL-3.0)

class Themes_Bootstrap_Vmails extends Themes_Bootstrap_Theme
{
    public function list(array $in) : string
    {
elog(__METHOD__);

        $create = $this->modal([
            'id'      => 'createmodal',
            'title'   => 'Create New Mailbox',
            'action'  => 'create',
            'footer'  => 'Create',
            'body'    => '
                  <div class="form-group">
                    <label for="user" class="form-control-label">Email Address</label>
                    <input type="text" class="form-control" id="user" name="user">
                  </div>',
        ]);

        $remove = $this->modal([
            'id'      => 'removemodal',
            'title'   => 'Remove Mailbox',
            'action'  => 'delete',
            'footer'  => 'Remove',
            'body'    => '
                <input type="hidden" id="removeuser" name="user" value="">
                <p class="text-center">Are you sure you want to remove this mailbox?<br><b id=targetuser></b></p>',
        ]);

        $update = $this->modal([
            'id'      => 'updatemodal',
            'title'   => 'Change Password',
            'action'  => 'update',
            'footer'  => 'Update',
            'body'    => '
                <input type="hidden" id="updateuser" name="user" value="">
                <div class="input-group mb-3">
                  <div class="input-group-prepend">
                    <a href="#" class="btn btn-outline-primary" id=shpw>Show</a>
                  </div>
                  <input type="text" class="form-control" id=password name=password placeholder="Email Password">
                  <div class="input-group-append">
                    <a href="#" class="btn btn-outline-primary" id=newpw>NewPW</a>
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
        <div class=col-12>
          <table id=vmails class="table table-sm" style="min-width:1100px;table-layout:fixed">
            <thead class="nowrap">
              <tr>
                <th>Email</th>
                <th>Usage&nbsp;</th>
                <th>Messages&nbsp;</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
            </tbody>
          </table>
        </div>' . $create . $remove . $update .'
        <script>
$(document).ready(function() {
  $("#vmails").DataTable({
    "processing": true,
    "serverSide": true,
    "ajax": "?x=json&o=vmails&m=list",
    "order": [[ 4, "desc" ]],
    "scrollX": true,
    "columnDefs": [
      {"targets":0, "width":"30%"},
      {"targets":1, "className":"text-right"},
      {"targets":2, "className":"text-right"},
      {"targets":3, "className":"text-right", "width":"2rem", "sortable": false},
      {"targets":4, "visible":false, "sortable": true}
    ]
  });

  $("#removemodal").on("show.bs.modal", function (event) {
    var link = $(event.relatedTarget)
    var user = link.data("removeuser")
    var modal = $(this)
    modal.find("#removeuser").val(user)
    modal.find("#targetuser").text(user)
   });

  $("#updatemodal").on("show.bs.modal", function (event) {
    var link = $(event.relatedTarget)
    var user = link.data("user")
    var id = link.data("id")
    var modal = $(this)
    modal.find("#i").val(id)
    modal.find("#updateuser").val(user)

    $("#updatemodal").on("click", "#shpw", {}, (function() {
        $.post("?x=text&o=vmails&m=update&shpw=1&user=" + user, function(data) {
          modal.find("#password").val(data)
        });
        return false;
    }));

    $("#updatemodal").on("click", "#newpw", {}, (function() {
        $.post("?x=text&o=vmails&m=update&newpw=1", function(data) {
          modal.find("#password").val(data)
        });
        return false;
    }));
  });
});
        </script>';
    }
}

?>
