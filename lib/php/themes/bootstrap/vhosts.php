<?php
// lib/php/themes/bootstrap/vhosts.php 20170225
// Copyright (C) 2015-2017 Mark Constable <markc@renta.net> (AGPL-3.0)

class Themes_Bootstrap_Vhosts extends Themes_Bootstrap_Theme
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

            $sql = "
 SELECT size_mpath, size_wpath, size_upath
   FROM `logging`
  WHERE `name`= :domain AND month = :month";

            $logging = db::qry($sql, ['domain' => $domain, 'month' => date('Ym')], 'one');
            if (is_array($logging)) extract($logging);
            else $size_mpath = $size_wpath = $size_upath = 0;

            $sql = "
 SELECT COUNT(*) FROM `valias`
  WHERE `did`= :did";

            $num_aliases = db::qry($sql, ['did' => $id], 'col');

            $sql = "
 SELECT COUNT(*) FROM `vmails`
  WHERE `did`= :did";

            $num_mailboxes = db::qry($sql, ['did' => $id], 'col');

            $active_icon = (isset($active) && $active)
                ? '<i class="fa fa-check text-success"></i>'
                : '<i class="fa fa-times text-danger"></i>';

            $url = $adm ? '
                  <a href="?o=vhosts&m=update&i=' . $id . '" title="Vhost ID: ' . $id . '">' . $domain . '</a>' : $domain;

            $mail_quota = util::numfmt($mailquota);
            $disk_quota = util::numfmt($diskquota);
            $size_mpath = util::numfmt($size_mpath);
            $size_upath = util::numfmt($size_upath);

            $buf .= '
              <tr id="data">
                <td><strong>' . $url . '</strong></td>
                <td>' . $uname . '</td>
                <td>' . $uid . ':' . $gid . '</td>
                <td>' . $num_aliases . ' / ' . $aliases . '</td>
                <td>' . $num_mailboxes . ' / ' . $mailboxes . '</td>
                <td>' . $size_mpath . ' / ' . $mail_quota . '</td>
                <td>' . $size_upath . ' / ' . $disk_quota . '</td>
                <td>' . $active_icon . '</td>
              </tr>';
        }

        if (empty($buf)) $buf .= '
                <tr><td colspan="8" class="text-center">No Records</td></tr>';

        return '
        <div class="row">
          <div class="col-md-6">
            <h3 class="min600">
              <a href="?o=vhosts&m=create" title="Add Vhost">
                <i class="fa fa-globe fa-fw"></i> Vhosts
                <small><i class="fa fa-plus-circle fa-fw"></i></small>
              </a>
            </h3>
          </div>' . $pgr_top . '
        </div>
        <div class="table-responsive">
          <table class="table table-sm min600">
            <thead class="nowrap">
              <tr>
                <th class="min200">Domain</th>
                <th class="min100">Uname</th>
                <th class="min100">UID:GID</th>
                <th class="min100">Aliases</th>
                <th class="min100">Mailboxes</th>
                <th class="min200">Mail Quota</th>
                <th class="min200">Disk Quota</th>
                <th></th>
              </tr>
            </thead>
            <tbody>' . $buf . '
            </tbody>
          </table>
        </div>' . $pgr_end;
    }

    private function editor(array $in) : string
    {
error_log(__METHOD__);

        extract($in);

        $active = $active ? 1 : 0;
        $header = $this->g->in['m'] === 'create' ? 'Add Vhost' : $domain;
        $submit = $this->g->in['m'] === 'create' ? '
                <a class="btn btn-secondary" href="?o=vhosts&m=list">&laquo; Back</a>
                <button type="submit" name="m" value="create" class="btn btn-primary">Add</button>' : '
                <a class="btn btn-secondary" href="?o=vhosts&m=list">&laquo; Back</a>
                <a class="btn btn-danger" href="?o=vhosts&m=delete&i=' . $this->g->in['i'] . '" title="Remove Vhost" onClick="javascript: return confirm(\'Are you sure you want to remove ' . $domain . '?\')">Remove</a>
                <button type="submit" name="m" value="update" class="btn btn-primary">Update</button>';
        $enable = $this->g->in['m'] === 'create' ? '
                <input type="text" autocorrect="off" autocapitalize="none" class="form-control" name="domain" id="domain" value="' . $domain . '">' : '
                <input type="text" class="form-control" value="' . $domain . '" disabled>
                <input type="hidden" name="domain" id="domain" value="' . $domain . '">';

        $checked = $active ? ' checked' : '';

        return '
          <h3 class="min600">
            <a href="?o=vhosts&m=list">
              <i class="fa fa-globe fa-fw"></i> ' . $header . '
            </a>
          </h3>
          <form method="post" action="' . $this->g->cfg->self . '">
            <input type="hidden" name="o" value="' . $this->g->in['o'] . '">
            <input type="hidden" name="i" value="' . $this->g->in['i'] . '">
            <div class="row">
              <div class="form-group col-md-4">
                <label for="domain">Domain</label>' . $enable . '
              </div>
              <div class="form-group col-md-2">
                <label for="aliases">Max Aliases</label>
                <input type="number" class="form-control" name="aliases" id="aliases" value="' . $aliases . '">
              </div>
              <div class="form-group col-md-2">
                <label for="mailboxes">Max Mailboxes</label>
                <input type="number" class="form-control" name="mailboxes" id="mailboxes" value="' . $mailboxes . '">
              </div>
              <div class="form-group col-md-2">
                <label for="mailquota">Mail Quota (MB)</label>
                <input type="number" class="form-control" name="mailquota" id="mailquota" value="' . intval($mailquota / 1048576) . '">
              </div>
              <div class="form-group col-md-2">
                <label for="diskquota">Disk Quota (MB)</label>
                <input type="number" class="form-control" name="diskquota" id="diskquota" value="' . intval($diskquota / 1048576) . '">
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
