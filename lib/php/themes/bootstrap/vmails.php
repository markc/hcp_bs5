<?php
// lib/php/themes/bootstrap/vmails.php 20170225
// Copyright (C) 2015-2017 Mark Constable <markc@renta.net> (AGPL-3.0)

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

        $buf = $pgr_top = $pgr_end = '';
        $pgr = $in['pager']; unset($in['pager']);
        $adm = util::is_adm();

        if ($pgr['last'] > 1) {
            $pgr_top ='
          <div class="col-md-6">' . $this->pager($pgr) . '
          </div>';
            $pgr_end = '
          <div class="row">
            <div class="col-12">' . $this->pager($pgr) . '
            </div>
          </div>';
        }

        foreach($in as $row) {
            extract($row);
            $active = $active ? 1 : 0;
            list($lhs, $rhs) = explode('@', $user);
            $sql = "
 SELECT mailquota
   FROM vhosts
  WHERE domain = :rhs";

            $maxquota = db::qry($sql, ['rhs' => $rhs], 'col');

            $sql = "
 SELECT user_mail,num_total
   FROM logging
  WHERE name = :user";

            $quota          = db::qry($sql, ['user' => $user], 'one');
            $mailquota      = $quota['user_mail'];
            $messages       = $quota['num_total'] ? $quota['num_total'] : 0;
            $percent        = round((intval($mailquota) / intval($maxquota)) * 100);
            $percent_buf    = $percent > 9 ? $percent.'%' : '';
            $mailquota_buf  = util::numfmt(intval($mailquota), 2);
            $maxquota_buf   = util::numfmt(intval($maxquota), 2);
            $pbar           = $percent >= 90
                ? 'bg-danger'
                : ($percent >= 75 ? 'bg-warning' : '');
            $active_buf     = $active
                ? '<i class="fa fa-check text-success"></i>'
                : '<i class="fa fa-times text-danger"></i>';

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
                    <td>' . $mailquota_buf . ' / ' . $maxquota_buf . '</td>
                    <td class="text-right">' . $messages . '</td>
                    <td class="text-right">' . $active_buf . '</td>
                  </tr>';
        }

        if (empty($buf)) $buf .= '
                <tr><td colspan="6" class="text-center">No Records</td></tr>';

//?                <tr class="bg-primary text-white">
        return '
        <div class="row">
          <div class="col-md-6">
            <h3 class="min600">
              <a href="?o=vmails&m=create" title="Add Mailbox">
                <i class="fa fa-envelope fa-fw"></i> Vmails
                <small><i class="fa fa-plus-circle fa-fw"></i></small>
              </a>
            </h3>
          </div>' . $pgr_top . '
        </div>
        <div class="table-responsive">
          <table class="table table-sm min600">
            <thead class="nowrap">
              <tr>
                <th class="min100">UserID</th>
                <th class="min100">Domain</th>
                <th class="min200">Mailbox Quota</th>
                <th class="min150"></th>
                <th class="min50">Msg #</th>
                <th class="min50"></th>
              </tr>
            </thead>
            <tbody>' . $buf . '
            </tbody>
          </table>
        </div>' . $pgr_end;
    }

    function editor(array $in) : string
    {
error_log(__METHOD__);

        extract($in);

        $active = $active ? 1 : 0;
        $checked = $active ? ' checked' : '';
        $passwd1 = $passwd1 ?? '';
        $passwd2 = $passwd2 ?? '';

        $header = $this->g->in['m'] === 'create' ? 'Add Vmail' : 'Update Vmail';
        $submit = $this->g->in['m'] === 'create' ? '
                      <a class="btn btn-secondary" href="?o=vmails&m=list">&laquo; Back</a>
                      <button type="submit" name="m" value="create" class="btn btn-primary">Add Mailbox</button>' : '
                      <a class="btn btn-secondary" href="?o=vmails&m=list">&laquo; Back</a>
                      <a class="btn btn-danger" href="?o=vmails&m=delete&i=' . $this->g->in['i'] . '" title="Remove mailbox" onClick="javascript: return confirm(\'Are you sure you want to remove ' . $user . '?\')">Remove</a>
                      <button type="submit" name="m" value="update" class="btn btn-primary">Update</button>';
        $enable = $this->g->in['m'] === 'create' ? '
                <input type="text" autocorrect="off" autocapitalize="none" class="form-control" name="user" id="user" value="' . $user . '">' : '
                <input type="text" class="form-control" value="' . $user . '" disabled>
                <input type="hidden" name="user" id="user" value="' . $user . '">';

        return '
          <h3 class="min600">
            <a href="?o=vmails&m=list">
              <i class="fa fa-envelope fa-fw"></i> ' . $header . '
            </a>
          </h3>
          <form method="post" action="' . $this->g->cfg->self . '">
            <input type="hidden" name="o" value="' . $this->g->in['o'] . '">
            <input type="hidden" name="i" value="' . $this->g->in['i'] . '">
            <div class="row">
              <div class="form-group col-md-4">
                <label for="domain">Email Address</label>' . $enable . '
              </div>
              <div class="form-group col-md-2">
                <label for="quota">Mailbox Quota</label>
                <input type="number" class="form-control" name="quota" id="quota" value="' . intval($quota / 1048576) . '">
              </div>
              <div class="col-md-3">
                <div class="form-group">
                  <label for="passwd1">Password</label>
                  <input type="password" class="form-control" name="passwd1" id="passwd1" value="' . $passwd1 . '">
                </div>
              </div>
              <div class="col-md-3">
                <div class="form-group">
                  <label for="passwd2">Confirm Password</label>
                  <input type="password" class="form-control" name="passwd2" id="passwd2" value="' . $passwd2 . '">
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-2 offset-md-6">
                <div class="form-group">
                  <label class="custom-control custom-checkbox">
                    <input type="checkbox" class="custom-control-input" name="active" id="active"' . $checked . '>
                    <span class="custom-control-indicator"></span>
                    <span class="custom-control-description">Active</span>
                  </label>
                </div>
              </div>
              <div class="col-md-4 text-right">
                <div class="btn-group">' . $submit . '
                </div>
              </div>
            </div>
          </form>';
    }
}

?>
