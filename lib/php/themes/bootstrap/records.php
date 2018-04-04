<?php
// lib/php/themes/bootstrap/records.php 20180323
// Copyright (C) 2015-2018 Mark Constable <markc@renta.net> (AGPL-3.0)

class Themes_Bootstrap_Records extends Themes_Bootstrap_Theme
{
    public function create(array $in) : string
    {
error_log(__METHOD__);

        return $this->editor($in);
    }

    public function list(array $in) : string
    {
error_log(__METHOD__);

        $buf = '';
        $domain = $in['domain']; unset($in['domain']);
        $active_buf = (isset($disabled) && $disabled == 0)
            ? '<i class="fa fa-check text-success"></i>'
            : '<i class="fa fa-times text-danger"></i>';

        foreach ($in as $row) {
            extract($row);
            $buf .= '
                <tr>
                  <td class="nowrap">
                    <a href="?o=records&m=read&i=' . $id . '" title="Show record ' . $id . '">
                      <strong>' . $name . '</strong>
                    </a>
                  </td>
                  <td>' . $type . '
                  </td>
                  <td class="nowrap ellide">' . $content . '
                  </td>
                  <td>' . $priority . '
                  </td>
                  <td>' . $ttl . '
                  </td>
                </tr>';
        }

        return '
          <div class="col-12">
            <h3>
              <i class="fas fa-globe fa-fw"></i> ' . $domain . '
              <a href="?o=records&m=create&domain=' . $domain . '" title="Add new DNS record">
                <small><i class="fas fa-plus-circle fa-fw"></i></small>
              </a>
            </h3>
          </div>
        </div><!-- END UPPER ROW -->
        <div class="row">
          <table id=records class="table table-sm">
            <thead>
              <tr>
                <th>Name</th>
                <th>Type</th>
                <th>Content</th>
                <th>Priority</th>
                <th>TTL</th>
                <th>&nbsp;&nbsp;</th>
              </tr>
            </thead>
            <tbody>' . $buf . '
            </tbody>
          </table>
        </div>
        <div class="row">
          <div class="col-12 text-right">
            <div class="btn-group">
              <a class="btn btn-secondary" href="?o=domains&m=list">&laquo; Back</a>
              <a class="btn btn-danger" href="?o=domains&m=delete&i=' . $this->g->in['i'] . '" title="Remove this item" onClick="javascript: return confirm(\'Are you sure you want to remove ' . $domain . '?\')">Remove</a>
              <a class="btn btn-primary" href="?o=domains&m=update&i=' . $this->g->in['i'] . '">Update</a>
            </div>
          </div>
        </div>
        <script>$(document).ready(function() { $("#records").DataTable(); });</script>';

    }

    public function update(array $in) : string
    {
error_log(__METHOD__);

        return $this->editor($in);
    }

    private function editor(array $in) : string
    {
error_log(__METHOD__);
error_log('records::editor = '.var_export($in,true));

        $buf = '';
        $types = [
            ['A',          'A'],
            ['AAAA',       'AAAA'],
            ['AFSDB',      'AFSDB'],
            ['CERT',       'CERT'],
            ['CNAME',      'CNAME'],
            ['DHCID',      'DHCID'],
            ['DLV',        'DLV'],
            ['DNSKEY',     'DNSKEY'],
            ['DS',         'DS'],
            ['EUI48',      'EUI48'],
            ['EUI64',      'EUI64'],
            ['HINFO',      'HINFO'],
            ['IPSECKEY',   'IPSECKEY'],
            ['KEY',        'KEY'],
            ['KX',         'KX'],
            ['LOC',        'LOC'],
            ['MINFO',      'MINFO'],
            ['MR',         'MR'],
            ['MX',         'MX'],
            ['NAPTR',      'NAPTR'],
            ['NS',         'NS'],
            ['NSEC',       'NSEC'],
            ['NSEC3',      'NSEC3'],
            ['NSEC3PARAM', 'NSEC3PARAM'],
            ['OPT',        'OPT'],
            ['PTR',        'PTR'],
            ['RKEY',       'RKEY'],
            ['RP',         'RP'],
            ['RRSIG',      'RRSIG'],
            ['SPF',        'SPF'],
            ['SRV',        'SRV'],
            ['SSHFP',      'SSHFP'],
            ['TLSA',       'TLSA'],
            ['TSIG',       'TSIG'],
            ['TXT',        'TXT'],
            ['WKS',        'WKS'],
        ];
        $domain = $in['domain']; unset($in['domain']);
        $domain_id = $in['domain_id']; unset($in['domain_id']);
        $options = $this->dropdown(
            $types,
            'type',
            'A',
            '',
            'custom-select'
        );

        foreach ($in as $row) {
            extract($row);
            $active = $disabled == 0 ? 1 : 0;
            $active_buf = $active
                ? '<i class="fas fa-check fa-fw text-success"></i>'
                : '<i class="fas fa-times fa-fw text-danger"></i>';
            $buf .= '
                <tr class="editrow" data-rowid="' . $id . '" data-active="' . $active . '">
                  <td class="text-truncate"><b title="DNS record ID: ' . $id . '">' . $name . '</b></td>
                  <td>' . $type . '</td>
                  <td class="text-truncate">' . $content . '</td>
                  <td class="text-right">' . $priority . '</td>
                  <td class="text-right">' . $ttl . '</td>
                  <td class="text-right">' . $active_buf . '
                    <a class="editlink" href="#" title="Update DNS record ID: ' . $id . '">
                      <i class="fas fa-edit fa-fw cursor-pointer"></i></a>
                    <a href="?o=records&m=delete&i=' . $id . '&domain_id=' . $domain_id . '" title="Remove DNS record ID: ' . $id . '" onClick="javascript: return confirm(\'Are you sure you want to remove record ID: ' . $id . '?\')">
                      <i class="fas fa-trash fa-fw cursor-pointer text-danger"></i></a>
                  </td>
                </tr>';
        }

        $checked = '';
        return '
          <div class="col-12">
            <h3><a href="?o=domains&m=list">&laquo;</a> ' . $domain . '</h3>
          </div>
        </div><!-- END UPPER ROW -->
        <div class="row">
          <div class="table-responsive">
            <table id=records class="table table-sm" style="min-width:1000px;table-layout:fixed">
              <thead>
                <tr>
                  <th>Name</th>
                  <th>Type</th>
                  <th>Content</th>
                  <th>Priority</th>
                  <th>TTL</th>
                  <th data-sortable="false" class="text-right" style="width:4rem"></th>
                </tr>
              </thead>
              <tbody>' . $buf . '
          </table>
        </div>
      </div>
      <br>
      <form method="post" action="' . $this->g->cfg['self'] . '">
        <div class="row">
          <input type="hidden" id="o" name="o" value="' . $this->g->in['o'] . '">
          <input type="hidden" id="i" name="i" value="0">
          <input type="hidden" id="domain_id" name="domain_id" value="' . $this->g->in['i'] . '">
          <div class="col-3">
            <div class="form-group">
            <input type="text" class="form-control" id="name" name="name" data-regex="^([^.]+\.)*[^.]*$" value="">
            </div>
          </div>
          <div class="col-2">' .  $options. '
          </div>
          <div class="col-4">
            <input type="text" class="form-control" id="content" name="content" data-regex="^.+$" value="">
          </div>
          <div class="col-1">
            <input type="text" class="form-control" id="prio" name="prio" data-regex="^[0-9]*$" value="0">
          </div>
          <div class="col-2">
            <input type="text" class="form-control" id="ttl" name="ttl" data-regex="^[0-9]*$" value="300">
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
            <div class="btn-group">
              <button id="editor" name="m" value="create" class="btn btn-primary">Add</button>
            </div>
          </div>
        </div>
      </form>
      <script>
$("#records").DataTable();
$(".editlink").on("click", function() {
  var row = $(this).closest("tr");
  $("#i").val(row.attr("data-rowid"));
  $("#name").val(row.find("td:eq(0)").text());
  $("#type").val(row.find("td:eq(1)").text());
  $("#content").val(row.find("td:eq(2)").text());
  $("#prio").val(row.find("td:eq(3)").text());
  $("#ttl").val(row.find("td:eq(4)").text());

  if (row.data("active"))
    $("#active").attr("checked", "on");
  else $("#active").removeAttr("checked");

  $("#editor").val("update");
  $("#editor").text("Save");
  $("#editor").attr("class","btn btn-success");

  return false;
});
      </script>';
    }
}

?>
