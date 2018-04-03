<?php
// lib/php/themes/bootstrap/vmails.php 20180321
// Copyright (C) 2015-2018 Mark Constable <markc@renta.net> (AGPL-3.0)

class Themes_Bootstrap_Vmails extends Themes_Bootstrap_Theme
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
        $adm = util::is_adm();

        foreach($in as $row) {
            extract($row);
            $active = $active ? 1 : 0;
            $spamf = $spamf ? 1 : 0;
            list($lhs, $rhs) = explode('@', $user);
            $sql = "
 SELECT mailquota
   FROM vhosts
  WHERE domain = :rhs";

            $maxquota = db::qry($sql, ['rhs' => $rhs], 'col');

            $sql = "
 SELECT user_mail,num_total
   FROM logging
  WHERE name = :user ORDER BY month DESC";

            $quota          = db::qry($sql, ['user' => $user], 'one');
            $mailquota      = $quota['user_mail'] ?? 0;
            $messages       = $quota['num_total'] ?? 0;
            $percent        = round(($mailquota / $maxquota) * 100);

            $percent_buf    = $percent > 9 ? $percent.'%' : '';
            $mailquota_buf  = util::numfmt($mailquota);
            $maxquota_buf   = util::numfmt($maxquota);

            $pbar           = $percent >= 90
                ? 'bg-danger'
                : ($percent >= 75 ? 'bg-warning' : '');
            $active_buf     = $active
                ? '<i class="fas fa-check text-success"></i>'
                : '<i class="fas fa-times text-danger"></i>';
            $url = $adm ? '
              <a href="?o=vmails&m=update&i=' . $id . '">' . $user . '</a>' : $user;

            $buf .= '
                  <tr>
                    <td><strong>' . $url . '</strong></td>
                    <td>' . $rhs . '</td>
                    <td class="align-middle">
                      <div class="progress">
                        <div class="progress-bar ' . $pbar . '" role="progressbar" aria-valuenow="' . $percent . '" aria-valuemin="0" aria-valuemax="100" style="width: ' . $percent . '%;">
                          ' . $percent_buf . '
                        </div>
                      </div>
                    </td>
                    <td class="text-right">' . $mailquota_buf . ' / ' . $maxquota_buf . '</td>
                    <td class="text-right">' . $messages . '</td>
                    <td class="text-right">' . $active_buf . '
                      <a href="?o=vmails&m=delete&i=' . $id . '" title="Remove Mailbox" onClick="javascript: return confirm(\'Are you sure you want to remove: ' . $user . '?\')">
                        <i class="fas fa-trash fa-fw cursor-pointer text-danger"></i></a>
                    </td>
                  </tr>';
        }
        if (empty($buf)) $buf .= '
                <tr><td colspan="6" class="text-center">No Records</td></tr>';

        return '
          <div class="col-12">
            <h3>
              <i class="fas fa-envelope fa-fw"></i> Mailboxes
              <a href="#" title="Add New Mailbox" data-toggle="modal" data-target="#createmodal">
              <!-- <a href="?o=vmails&m=create" title="Add Mailbox"> -->
                <small><i class="fas fa-plus-circle fa-fw"></i></small>
              </a>
            </h3>
          </div>
        </div><!-- END UPPER ROW -->
        <div class="row">
          <table id=vmails class="table table-sm">
            <thead class="nowrap">
              <tr>
                <th>UserID</th>
                <th>Domain</th>
                <th class="w-25" data-sortable="false"></th>
                <th>Mailbox Quota</th>
                <th>Msg #</th>
                <th data-sortable="false" class="text-right">&nbsp;&nbsp;&nbsp;</th>
              </tr>
            </thead>
            <tbody>' . $buf . '
            </tbody>
          </table>

          <div class="modal fade" id="createmodal" tabindex="-1" role="dialog" aria-labelledby="createmodal" aria-hidden="true">
            <div class="modal-dialog" role="document">
              <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title">Mailboxes</h5>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                  </button>
                </div>
                <form method="post" action="' . $this->g->cfg['self'] . '">
                  <div class="modal-body">
                    <input type="hidden" name="o" value="' . $this->g->in['o'] . '">
                    <input type="hidden" name="i" value="' . $this->g->in['i'] . '">
                    <input type="hidden" name="m" value="create">
                    <div class="form-group">
                      <label for="user" class="form-control-label">Mailbox</label>
                      <input type="text" class="form-control" id="user" name="user">
                    </div>
                    <div class="form-group">
                      <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" name="spamf" id="spamf" checked>
                        <label class="custom-control-label" for="spamf">Spam Filter</label>
                      </div>
                    </div>
                  </div>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Add New Mailbox</button>
                  </div>
                </form>
              </div>
            </div>
          </div>

          <script>$(document).ready(function() { $("#vmails").DataTable({"order": []}); });</script>';
    }

    function editor(array $in) : string
    {
error_log(__METHOD__);

        extract($in);
error_log("active=$active,spamf=$spamf");
        $active_checked = ($active ? 1 : 0) ? ' checked' : '';
        $filter_checked = ($spamf ? 1 : 0) ? ' checked' : '';
error_log("active_checked=$active_checked,filter_checked=$filter_checked");

        $passwd1  = $passwd1 ?? '';
        $passwd2  = $passwd2 ?? '';

        $header   = $this->g->in['m'] === 'create' ? 'Add Mailbox' : 'Update Mailbox';
        $submit   = $this->g->in['m'] === 'create' ? '
                      <a class="btn btn-secondary" href="?o=vmails&m=list">&laquo; Back</a>
                      <button type="submit" name="m" value="create" class="btn btn-primary">Add Mailbox</button>' : '
                      <a class="btn btn-secondary" href="?o=vmails&m=list">&laquo; Back</a>
                      <a class="btn btn-danger" href="?o=vmails&m=delete&i=' . $this->g->in['i'] . '" title="Remove mailbox" onClick="javascript: return confirm(\'Are you sure you want to remove ' . $user . '?\')">Remove</a>
                      <button type="submit" name="m" value="update" class="btn btn-primary">Update</button>';
        $enable   = $this->g->in['m'] === 'create' ? '
                <input type="text" autocorrect="off" autocapitalize="none" class="form-control" name="user" id="user" value="' . $user . '">' : '
                <input type="text" class="form-control" value="' . $user . '" disabled>
                <input type="hidden" name="user" id="user" value="' . $user . '">';

        return '
          <div class="col-12">
            <h3><a href="?o=vmails&m=list">&laquo;</a> ' . $header . '</h3>
          </div>
        </div><!-- END UPPER ROW -->
        <div class="row">
          <div class="col-12">
            <form method="post" action="' . $this->g->cfg['self'] . '">
              <input type="hidden" name="o" value="' . $this->g->in['o'] . '">
              <input type="hidden" name="i" value="' . $this->g->in['i'] . '">
              <div class="row">
                <div class="form-group col-4">
                  <label for="domain">Email Address</label>' . $enable . '
                </div>
                <div class="form-group col-2">
                  <label for="quota">Mailbox Quota</label>
                  <input type="number" class="form-control" name="quota" id="quota" value="' . intval($quota / 1000000) . '">
                </div>
                <div class="col-3">
                  <div class="form-group">
                    <label for="passwd1">Password</label>
                    <input type="password" class="form-control" name="passwd1" id="passwd1" value="' . $passwd1 . '">
                  </div>
                </div>
                <div class="col-3">
                  <div class="form-group">
                    <label for="passwd2">Confirm Password</label>
                    <input type="password" class="form-control" name="passwd2" id="passwd2" value="' . $passwd2 . '">
                  </div>
                </div>
              </div>
              <div class="row">
                <div class="col-4">
                  <div class="form-group">
                    <div class="custom-control custom-checkbox">
                      <input type="checkbox" class="custom-control-input" name="spamf" id="spamf"' . $filter_checked . '>
                      <label class="custom-control-label" for="spamf">Spam Filter</label>
                    </div>
                  </div>
                </div>
                <div class="col-4">
                  <div class="form-group">
                    <div class="custom-control custom-checkbox">
                      <input type="checkbox" class="custom-control-input" name="active" id="active"' . $active_checked . '>
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
