<?php
// lib/php/themes/bootstrap/records.php 20170225
// Copyright (C) 2015-2017 Mark Constable <markc@renta.net> (AGPL-3.0)

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
                  <td class="nowrap ellide max200">' . $content . '
                  </td>
                  <td>' . $priority . '
                  </td>
                  <td>' . $ttl . '
                  </td>
                </tr>';
        }

        return '
          <h3 class="w30">
            <a href="?o=records&m=create&domain=' . $domain . '" title="Add new DNS record">
              <i class="fa fa-globe fa-fw"></i> ' . $domain . '
              <small><i class="fa fa-plus-circle fa-fw"></i></small>
            </a>
          </h3>
          <div class="table-responsive">
            <table class="table table-sm w30">
              <thead>
                <tr class="bg-primary text-white">
                  <th>Name</th>
                  <th>Type</th>
                  <th>Content</th>
                  <th>Priority</th>
                  <th>TTL</th>
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
          </div>';
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
//        $checked = $disabled == 0 ? ' checked' : '';
//        $checked = $active ? ' checked' : '';
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
                ? '<i class="fa fa-check fa-fw text-success active_icon"></i>'
                : '<i class="fa fa-times fa-fw text-danger active_icon"></i>';
            $buf .= '
                <tr class="editrow" data-rowid="' . $id . '" data-active="' . $active . '">
                  <td class="min300"><b title="DNS record ID: ' . $id . '">' . $name . '</b></td>
                  <td class="min50">' . $type . '</td>
                  <td class="max300 ellide nowrap">' . $content . '</td>
                  <td class="min100">' . $priority . '</td>
                  <td class="min100">' . $ttl . '</td>
                  <td class="min100 text-right">' . $active_buf . '
                    <a class="editlink" href="#" title="Update DNS record ID: ' . $id . '">
                      <i class="fa fa-pencil fa-fw cursor-pointer"></i>
                    </a>
                    <a href="?o=records&m=delete&i=' . $id . '&domain_id=' . $domain_id . '" title="Remove DNS record ID: ' . $id . '" onClick="javascript: return confirm(\'Are you sure you want to remove record ID: ' . $id . '?\')">
                      <i class="fa fa-trash fa-fw cursor-pointer text-danger"></i>
                    </a>
                  </td>
                </tr>';
        }
        $checked = '';
        return '
              <div class="row">
                <div class="col-md-6">
                  <h3 class="min600">
                    <a href="?o=domains&m=list">
                      <i class="fa fa-chevron-left fa-fw"></i> ' . $domain . '
                    </a>
                  </h3>
                </div>
                <div class="col-md-6 text-right">
                  <a href="?o=records&m=update&i=' . $this->g->in['i'] . '">
                    <i class="fa fa-refresh fa-fw"></i>
                  </a>
                </div>
              </div>
              <div class="table-responsive">
                <table class="table table-sm min900">
                  <thead>
                    <tr>
                      <th>Name</th>
                      <th>Type</th>
                      <th>Content</th>
                      <th>Priority</th>
                      <th>TTL</th>
                      <th></th>
                    </tr>
                  </thead>
                  <tbody>' . $buf . '
              </table>
            </div>
              <form method="post" action="' . $this->g->self . '">
            <div class="row">
                <input type="hidden" id="o" name="o" value="' . $this->g->in['o'] . '">
                <input type="hidden" id="i" name="i" value="0">
                <input type="hidden" id="domain_id" name="domain_id" value="' . $this->g->in['i'] . '">
                <div class="col-md-3">
                  <div class="form-group">
                  <input type="text" class="form-control" id="name" name="name" data-regex="^([^.]+\.)*[^.]*$" value="">
                  </div>
                </div>
                <div class="col-md-2">' .  $options. '
                </div>
                <div class="col-md-4">
                  <input type="text" class="form-control" id="content" name="content" data-regex="^.+$" value="">
                </div>
                <div class="col-md-1">
                  <input type="text" class="form-control" id="prio" name="prio" data-regex="^[0-9]*$" value="0">
                </div>
                <div class="col-md-2">
                  <input type="text" class="form-control" id="ttl" name="ttl" data-regex="^[0-9]*$" value="300">
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
                <div class="btn-group">
                  <button id="editor" name="m" value="create" class="btn btn-primary">Add</button>
                </div>
              </div>
            </div>
              </form>
            <script>
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
