<?php
// lib/php/themes/bootstrap/mail/domainaliases.php 20170225
// Copyright (C) 2015-2017 Mark Constable <markc@renta.net> (AGPL-3.0)

class Themes_Bootstrap_Mail_DomainAlias extends Themes_Bootstrap_Theme
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
            $active_buf = $active
                ? '<i class="fa fa-check text-success"></i>'
                : '<i class="fa fa-times text-danger"></i>';

            $buf .= '
                <tr id="data">
                  <td><a href="?o=mail_domainalias&m=update&i=' . $id . '"><strong>' . $alias_domain . '<strong></a></td>
                  <td>' . $target_domain . '</td>
                  <td>' . $active_buf . '</td>
                </tr>';
        }

        if (empty($buf)) $buf .= '
                <tr>
                  <td colspan="3" class="text-center">No Records</td>
                </tr>';

        return '
        <div class="row">
          <div class="col-md-6">
            <h3 class="min600">
              <a href="?o=mail_domainalias&m=create" title="Add DomainAlias">
                <i class="fa fa-globe fa-fw"></i> Domain Aliases
                <small><i class="fa fa-plus-circle fa-fw"></i></small>
              </a>
            </h3>
          </div>' . $pgr_top . '
        </div>
        <div class="table-responsive">
          <table class="table table-sm min600">
            <thead class="nowrap">
              <tr class="bg-primary text-white">
                <th class="min100">Alias</th>
                <th class="min150">Target Domain</th>
                <th class="min50">Active</th>
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
error_log(var_export($in, true));

        extract($in);

        $options = '';
        $checked = $active ? ' checked' : '';
        $tmp =  db::qry("SELECT `domain` FROM `domain`");
        $rows = [];
        foreach($tmp as $r) $rows[] = [$r['domain'], $r['domain']];
        $options = $this->dropdown(
            $rows,
            'target_domain',
            $target_domain,
            'Please select...',
            'custom-select'
        );

        $header = $this->g->in['m'] === 'create' ? 'Add Domain Alias' : 'Update Domain Alias';
        $submit = $this->g->in['m'] === 'create' ? '
                      <a class="btn btn-secondary" href="?o=mail_domainalias&m=list">&laquo; Back</a>
                      <button type="submit" name="m" value="create" class="btn btn-primary">Add Domain Alias</button>' : '
                      <a class="btn btn-secondary" href="?o=mail_domainalias&m=list">&laquo; Back</a>
                      <a class="btn btn-danger" href="?o=mail_domainalias&m=delete&i=' . $id . '" title="Remove domain" onClick="javascript: return confirm(\'Are you sure you want to remove ' . $alias_domain . '?\')">Remove</a>
                      <button type="submit" name="m" value="update" class="btn btn-primary">Update</button>';

        return '
          <h3 class="w30">
            <a href="?o=mail_domainalias&m=list">
              <i class="fa fa-globe fa-fw"></i> ' . $header . '
            </a>
          </h3>
          <form method="post" action="' . $this->g->cfg['self'] . '">
            <input type="hidden" name="o" value="' . $this->g->in['o'] . '">
            <input type="hidden" name="i" value="' . $this->g->in['i'] . '">
            <div class="row">
              <div class="form-group col-md-6">
                <label for="alias_domain">Alias domain</label>
                <textarea class="form-control" rows="4" name="alias_domain" id="alias_domain">' . $alias_domain . '</textarea>
                <p>Valid domain names only (comma-separated)</p>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label class="control-label" for="target_domain">Target domain</label>
                  <br>' . $options . '
                </div>
                <div class="row">
                  <div class="form-group col-md-6">
                    <label class="custom-control custom-checkbox">
                      <input type="checkbox" class="custom-control-input" name="active" id="active"' . $checked . '>
                      <span class="custom-control-indicator"></span>
                      <span class="custom-control-description">Active</span>
                    </label>
                  </div>
                  <div class="col-md-6 text-right">
                    <div class="btn-group">' . $submit . '
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </form>';
    }
}

?>
