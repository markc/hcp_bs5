<?php
// lib/php/themes/bootstrap/vhosts.php 20180323
// Copyright (C) 2015-2018 Mark Constable <markc@renta.net> (AGPL-3.0)

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
/*
        $buf = '';
        $adm = util::is_adm();

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
 SELECT COUNT(id)
   FROM `valias`
  WHERE `did`= :did";

            $num_aliases = db::qry($sql, ['did' => $id], 'col');

            $sql = "
 SELECT COUNT(id)
   FROM `vmails`
  WHERE `did`= :did";

            $num_mailboxes = db::qry($sql, ['did' => $id], 'col');

            $active_icon = (isset($active) && $active)
                ? '<i class="fas fa-check text-success" title="Enabled"></i>'
                : '<i class="fas fa-times text-danger" title="Disabled"></i>';

            $url = $adm ? '
                  <a href="?o=vhosts&m=update&i=' . $id . '" title="Vhost ID: ' . $id . '">' . $domain . '</a>' : $domain;

            $mail_quota = util::numfmt($mailquota);
            $disk_quota = util::numfmt($diskquota);
            $size_mpath = util::numfmt($size_mpath);
            $size_upath = util::numfmt($size_upath);

//                <td>' . $uname . '</td>
//                <td>' . $uid . ':' . $gid . '</td>

            $buf .= '
              <tr id="data">
                <td class="text-truncate"><strong>' . $url . '</strong></td>
                <td class="text-right">' . $num_mailboxes . '&nbsp;&nbsp;/</td>
                <td>' . $mailboxes . '</td>
                <td class="text-right">' . $num_aliases . '&nbsp;&nbsp;/</td>
                <td>' . $aliases . '</td>
                <td class="text-right">' . $size_mpath . '&nbsp;&nbsp;/</td>
                <td>' . $mail_quota . '</td>
                <td class="text-right">' . $size_upath . '&nbsp;&nbsp;/</td>
                <td>' . $disk_quota . '</td>
                <td class="text-right">' . $active_icon . '
                  <a href="?o=vhosts&m=delete&i=' . $id . '" title="Remove Vhost" onClick="javascript: return confirm(\'Are you sure you want to remove: ' . $domain . '?\')">
                    <i class="fas fa-trash fa-fw cursor-pointer text-danger"></i></a>
                </td>
              </tr>';
        }

        if (empty($buf)) $buf .= '
                <tr><td colspan="8" class="text-center">No Records</td></tr>';
*/

            $plans = [
                ['Select Plan', ''],
                ['Personal - 1 GB Storage, 1 Domain, 1 Website, 1 Mailbox', 'personal'],
                ['SOHO - 5 GB Storage, 2 Domains, 2 Websites, 5 Mailboxes', 'soho'],
                ['Business - 10 GB Storage, 5 Domains, 5 Websites, 10 Mailboxes', 'business'],
                ['Enterprise - 20 GB Storage, 10 Domains, 10 Websites, 20 Mailboxes', 'enterprise'],
            ];

            $plans_buf = $this->dropdown($plans, 'plan', '', '', 'custom-select');

        return '
          <div class="col-12">
            <h3>
              <i class="fa fa-globe fa-fw"></i> Vhosts
              <a href="#" title="Add new vhost" data-toggle="modal" data-target="#createmodal">
                <small><i class="fas fa-plus-circle fa-fw"></i></small>
              </a>
            </h3>
          </div>
        </div><!-- END UPPER ROW -->
        <div class="row">
          <div class="table-responsive">
            <table id=vhosts class="table">
              <thead>
                <tr>
                  <th>Domain</th>
                  <th>Aliases</th>
                  <th>Mailboxes</th>
                  <th>MailQuota</th>
                  <th>DiskQuota</th>
                  <th>Updated</th>
                </tr>
              </thead>
              <tfoot>
              </tfoot>
            </table>
          </div>

          <div class="modal fade" id="createmodal" tabindex="-1" role="dialog" aria-labelledby="createmodal" aria-hidden="true">
            <div class="modal-dialog" role="document">
              <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title">Vhosts</h5>
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
                        <label for="domain" class="form-control-label">Vhost</label>
                        <input type="text" class="form-control" id="domain" name="domain">
                      </div>
                      <div class="form-group">
                        <label for="plan class="form-control-label">Plan</label>' . $plans_buf . '
                      </div>
                  </div>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Add New Vhost</button>
                  </div>
                </form>
              </div>
            </div>
          </div>

          <script>
$(document).ready(function() {
//  $("#vhosts").DataTable({"order": []});

  $("#vhosts").DataTable( {
    "processing": true,
    "serverSide": true,
    "ajax": "?x=json&o=vhosts&m=list"
  } );

//  $(".serial").click(function(id, serial){
//    var a = $(this)
//    $.post("?x=text&increment=1&" + this.toString().split("?")[1], function(data) {
//      $(a).text(data);
//    });
//    return false;
//  });
});
        </script>';
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
          <div class="col-12">
            <h3><a href="?o=vhosts&m=list">&laquo;</a> ' . $header . '</h3>
          </div>
        </div><!-- END UPPER ROW -->
        <div class="row">
          <div class="col-12">
            <form method="post" action="' . $this->g->cfg['self'] . '">
              <input type="hidden" name="o" value="' . $this->g->in['o'] . '">
              <input type="hidden" name="i" value="' . $this->g->in['i'] . '">
              <div class="row">
                <div class="form-group col-12 col-md-6 col-lg-4">
                  <label for="domain">Domain</label>' . $enable . '
                </div>
                <div class="form-group col-6 col-md-3 col-lg-2">
                  <label for="aliases">Max Aliases</label>
                  <input type="number" class="form-control" name="aliases" id="aliases" value="' . $aliases . '">
                </div>
                <div class="form-group col-6 col-md-3 col-lg-2">
                  <label for="mailboxes">Max Mailboxes</label>
                  <input type="number" class="form-control" name="mailboxes" id="mailboxes" value="' . $mailboxes . '">
                </div>
                <div class="form-group col-6 col-md-3 col-lg-2">
                  <label for="mailquota">Mail Quota (MB)</label>
                  <input type="number" class="form-control" name="mailquota" id="mailquota" value="' . intval($mailquota / 1000000) . '">
                </div>
                <div class="form-group col-6 col-md-3 col-lg-2">
                  <label for="diskquota">Disk Quota (MB)</label>
                  <input type="number" class="form-control" name="diskquota" id="diskquota" value="' . intval($diskquota / 1000000) . '">
                </div>
              </div>
              <div class="row">
                <div class="col-12 col-sm-6">
                  <div class="form-group">
                    <div class="custom-control custom-checkbox">
                      <input type="checkbox" class="custom-control-input" name="active" id="active"' . $checked . '>
                      <label class="custom-control-label" for="active">Active</label>
                    </div>
                  </div>
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
