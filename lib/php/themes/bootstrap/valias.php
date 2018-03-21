<?php
// lib/php/themes/bootstrap/valias.php 20180219
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

        $buf = '';
        $pgr = $in['pager']; unset($in['pager']);
/*
        $buf = $pgr_top = $pgr_end = '';
        if ($pgr['last'] > 1) {
            $pgr_top ='
          <div class="col-6">' . $this->pager($pgr) . '
          </div>';
            $pgr_end = '
          <div class="row">
            <div class="col-12">' . $this->pager($pgr) . '
            </div>
          </div>';
        }
*/
        foreach($in as $row) {
            extract($row);
            $active = $active ? 1 : 0;
            list($lhs, $rhs) = explode('@', $source);
            $target_buf = '';
            $source_buf = (filter_var($source, FILTER_VALIDATE_EMAIL))
                ? $source
                : 'Catch-all ' . $source;

            foreach (explode(',', $target) as $t) {
                $target_buf .= nl2br(htmlspecialchars($t . PHP_EOL));
            }

            $active_buf = $active
                ? '<i class="fa fa-check text-success"></i>'
                : '<i class="fa fa-times text-danger"></i>';

            $buf .= '
            <tr>
              <td><a href="?o=valias&m=update&i=' . $id . '"><strong>' . $source_buf . '<strong></a></td>
              <td>' . $target_buf . ' </td>
              <td>' . $rhs . '</td>
              <td class="text-right">' . $active_buf . '</td>
            </tr>';
        }

        return '
          <div class="col-6">
          <h3>
            <i class="fa fa-globe fa-fw"></i> Aliases
            <a href="?o=valias&m=create" title="Add Alias">
              <small><i class="fa fa-plus-circle fa-fw"></i></small>
            </a>
          </h3>
          </div>
        </div><!-- END UPPER ROW -->
          <div class="row">
            <table id=valias class="table table-sm">
              <thead class="nowrap">
                <tr>
                  <th>Alias</th>
                  <th>Target Address</th>
                  <th>Domain</th>
                  <th></th>
                </tr>
              </thead>
              <tbody>' . $buf . '
              </tbody>
            </table>
          </div>
          <script>$(document).ready(function() { $("#valias").DataTable(); });</script>';

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
          <h3 class="min600">
            <a href="?o=valias&m=list">
              <i class="fa fa-globe fa-fw"></i> ' . $header . '
            </a>
          </h3>
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
                  <label class="custom-control custom-checkbox">
                    <input type="checkbox" class="custom-control-input" name="active" id="active"' . $checked . '>
                    <span class="custom-control-indicator"></span>
                    <span class="custom-control-description">Active</span>
                  </label>
                </div>
              </div>
              <div class="col-4 text-right">
                <div class="btn-group">' . $submit . '
                </div>
              </div>
            </div>
          </form>';
    }
}

?>
