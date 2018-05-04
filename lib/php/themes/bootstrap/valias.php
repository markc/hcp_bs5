<?php
// lib/php/themes/bootstrap/valias.php 20170101 - 20180503
// Copyright (C) 2015-2018 Mark Constable <markc@renta.net> (AGPL-3.0)

class Themes_Bootstrap_Valias extends Themes_Bootstrap_Theme
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

        return '
        <div class="col-12">
          <h3>
            <i class="fa fa-globe fa-fw"></i> Aliases
            <a href="?o=valias&m=create" title="Add Alias">
              <small><i class="fas fa-plus-circle fa-fw"></i></small>
            </a>
          </h3>
        </div>
      </div><!-- END UPPER ROW -->
      <div class="row">
        <div class="table-responsive">
          <table id=valias class="table table-sm" style="min-width:1100px;table-layout:fixed">
            <thead class="nowrap">
              <tr>
                <th>Alias</th>
                <th>Target Address</th>
                <th>Domain</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
            </tbody>
          </table>
        </div>
        <script>
$(document).ready(function() {
  $("#valias").DataTable({
    "processing": true,
    "serverSide": true,
    "ajax": "?x=json&o=valias&m=list",
    "order": [[ 5, "desc" ]],
    "columnDefs": [
      {"targets":0,   "className":"text-truncate", "width":"25%"},
      {"targets":3,   "className":"text-right", "width":"4rem", "sortable": false},
      {"targets":4,   "visible":false},
      {"targets":5,   "visible":false},
    ],
  });
});
        </script>';

    }

    private function editor(array $in) : string
    {
error_log(__METHOD__);

        extract($in);

        $active = $active ? 1 : 0;
        $header = $this->g->in['m'] === 'create' ? 'Add Alias' : 'Update Alias';
        $submit = $this->g->in['m'] === 'create' ? '
                <a class="btn btn-secondary" href="?o=valias&m=list">&laquo; Back</a>
                <button type="submit" name="m" value="create" class="btn btn-primary">Add Alias</button>' : '
                <a class="btn btn-secondary" href="?o=valias&m=list">&laquo; Back</a>
                <a class="btn btn-danger" href="?o=valias&m=delete&i=' . $id . '" title="Remove alias" onClick="javascript: return confirm(\'Are you sure you want to remove ' . $source . '?\')">Remove</a>
                <button type="submit" name="m" value="update" class="btn btn-primary">Update</button>';

        $checked = $active ? ' checked' : '';

        return '
          <div class="col-12">
            <h3><a href="?o=valias&m=list">&laquo;</a> ' . $header . '</h3>
          </div>
        </div><!-- END UPPER ROW -->
        <div class="row">
          <div class="col-12">
            <p><b>Note:</b> If your chosen destination address is an external mailbox, the <b>receiving mailserver</b> may reject your message due to an SPF failure.</p>
            <form method="post" action="' . $this->g->cfg['self'] . '">
              <input type="hidden" name="o" value="' . $this->g->in['o'] . '">
              <input type="hidden" name="i" value="' . $this->g->in['i'] . '">
              <div class="row">
                <div class="form-group col-6">
                  <label class="control-label" for="source">Alias Address(es)</label>
                  <textarea autocorrect="off" autocapitalize="none" class="form-control" rows="4" name="source" id="source">' . $source . '</textarea>
                  <p>Full email address/es or @example.com, to catch all messages for a domain (comma-separated). <b>Locally hosted domains only</b>.</p>
                </div>
                <div class="form-group col-6">
                  <label class="control-label" for="target">Target Address(es)</label>
                  <textarea autocorrect="off" autocapitalize="none" class="form-control" rows="4" id="target" name="target">' . $target . '</textarea>
                  <p>Full email address/es (comma-separated).</p>
                </div>
              </div>
              <div class="row">
                <div class="col-2 offset-md-6">
                  <div class="form-group">
                    <div class="custom-control custom-checkbox">
                      <input type="checkbox" class="custom-control-input" name="active" id="active"' . $checked . '>
                      <label class="custom-control-label" for="active">Active</label>
                    </div>
                  </div>
                </div>
                <div class="col-4 text-right">
                  <div class="btn-group">' . $submit . '
                  </div>
                </div>
              </div>
            </form>
          </div>';
    }
}

?>
