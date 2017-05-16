<?php
// lib/php/themes/bootstrap/domains.php 20170225 - 20170423
// Copyright (C) 2015-2017 Mark Constable <markc@renta.net> (AGPL-3.0)

class Themes_Bootstrap_Domains extends Themes_Bootstrap_Theme
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

        foreach ($in as $row) {
            extract($row);
            $buf .= '
                <tr>
                  <td class="nowrap">
                    <a href="?o=records&m=update&i=' . $id . '" title="Show item ' . $id . '">
                      <strong>' . $name . '</strong>
                    </a>
                  </td>
                  <td>' . $type . '
                  </td>
                  <td>' . $records . '
                  </td>
                  <td class="text-right">
                    <a href="?o=domains&m=update&i=' . $id . '" title="Edit SOA: ' . $id . '">
                      <i class="fa fa-pencil fa-fw cursor-pointer"></i>
                    </a>
                    <a href="?o=domains&m=delete&i=' . $id . '" title="Remove DNS record" onClick="javascript: return confirm(\'Are you sure you want to remove: ' . $name . '?\')">
                      <i class="fa fa-trash fa-fw cursor-pointer"></i>
                    </a>
                  </td>
                </tr>';
        }
        if (empty($buf)) $buf .= '
                <tr><td colspan="4" class="text-center">No Domains</td></tr>';

        return '
        <div class="row">
          <div class="col-md-6">
            <h3 class="min600">
              <a href="#" title="Add new domain" data-toggle="modal" data-target="#createmodal">
                <i class="fa fa-globe fa-fw"></i> Domains
                <small><i class="fa fa-plus-circle fa-fw"></i></small>
              </a>
            </h3>
          </div>' . $pgr_top . '
        </div>
        <div class="table-responsive">
          <table class="table table-sm min600">
            <thead>
              <tr>
                <th>Name</th>
                <th>Type</th>
                <th>Records</th>
                <th class="text-right">SOA</th>
              </tr>
            </thead>
            <tbody>' . $buf . '
            </tbody>
          </table>
        </div>' . $pgr_end . '
        <div class="modal fade" id="createmodal" tabindex="-1" role="dialog" aria-labelledby="createmodal" aria-hidden="true">
          <div class="modal-dialog" role="document">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title"">Domain</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>
                <form method="post" action="' . $this->g->self . '">
              <div class="modal-body">
                  <input type="hidden" name="o" value="' . $this->g->in['o'] . '">
                  <input type="hidden" name="i" value="' . $this->g->in['i'] . '">
                  <input type="hidden" name="m" value="create">
                  <div class="form-group">
                    <label for="domain class="form-control-label">Name</label>
                    <input type="text" class="form-control" id="domain" name="domain">
                  </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary">Add New Domain</button>
              </div>
                </form>
            </div>
          </div>
        </div>';
    }

    private function editor(array $in) : string
    {
error_log(__METHOD__);

        $domain = $in['name'];
        $soa = isset($in['soa'])
            ? explode(' ', $in['soa'])
            : ['', '', '', 7200, 540, 604800, 300];

        if ($this->g->in['m'] === 'create') {
            $serial = $hidden = '';
            $header = 'Add Domain';
            $submit = '
                <a class="btn btn-secondary" href="?o=domains&m=list">&laquo; Back</a>
                <button type="submit" id="m" name="m" value="create" class="btn btn-primary">Add Domain</button>';
        } else {
            $serial = '&nbsp;&nbsp;<small>Serial: ' . $soa[2] . '</small>';
            $header = $domain;
            $submit = '
                <a class="btn btn-secondary" href="?o=domains&m=list">&laquo; Back</a>
                <button type="submit" id="m" name="m" value="update" class="btn btn-primary">Update</button>';
            $hidden = '
            <input type="hidden" name="serial" value="' . $soa[2] . '">';
        }

        return '
          <h3 class="min600">
            <a href="?o=domains&m=list">
              <i class="fa fa-globe fa-fw"></i> ' . $header . '
            </a>' . $serial . '
          </h3>
          <form method="post" action="' . $this->g->self . '">
            <input type="hidden" name="o" value="' . $this->g->in['o'] . '">
            <input type="hidden" name="i" value="' . $this->g->in['i'] . '">' . $hidden . '
            <div class="row">
              <div class="col-md-3">
                <div class="form-group">
                  <label for="primary">Primary</label>
                  <input type="text" class="form-control" id="primary" name="primary" value="' . $soa[0] . '" required>
                </div>
              </div>
              <div class="col-md-3">
                <div class="form-group">
                  <label for="email">Email</label>
                  <input type="text" class="form-control" id="email" name="email" value="' . $soa[1] . '" required>
                </div>
              </div>
              <div class="col-md-1">
                <div class="form-group">
                  <label for="refresh">Refresh</label>
                  <input type="text" class="form-control" id="refresh" name="refresh" value="' . $soa[3] . '" required>
                </div>
              </div>
              <div class="col-md-1">
                <div class="form-group">
                  <label for="retry">Retry</label>
                  <input type="text" class="form-control" id="retry" name="retry" value="' . $soa[4] . '" required>
                </div>
              </div>
              <div class="col-md-2">
                <div class="form-group">
                  <label for="expire">Expire</label>
                  <input type="text" class="form-control" id="expire" name="expire" value="' . $soa[5] . '" required>
                </div>
              </div>
              <div class="col-md-2">
                <div class="form-group">
                  <label for="ttl">TTL</label>
                  <input type="text" class="form-control" id="ttl" name="ttl" value="' . $soa[6] . '" required>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-12 text-right">
                <div class="btn-group">' . $submit . '
                </div>
              </div>
            </div>
          </form>';
    }
}

?>
