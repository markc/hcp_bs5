<?php
// lib/php/themes/bootstrap/records.php 20180714
// Copyright (C) 2015-2018 Mark Constable <markc@renta.net> (AGPL-3.0)

class Themes_Bootstrap_Records extends Themes_Bootstrap_Theme
{
    private $types = [
        ['A',          'A'],
        ['MX',         'MX'],
        ['NS',         'NS'],
        ['TXT',        'TXT'],
        ['AAAA',       'AAAA'],
        ['CAA',        'CAA'],
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
        ['NAPTR',      'NAPTR'],
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
        ['WKS',        'WKS'],
    ];

    public function list(array $in) : string
    {
elog(__METHOD__);

elog('in='.var_export($in, true));

        return '
        <div class="col-12">
          <h3>
            <a href="?o=domains&m=list"><i class="fas fa-angle-double-left fa-fw"></i></a>' . $in['domain'] . '
            <a class="create" href="" title="Create new DNS record">
              <small><i class="fas fa-plus-circle fa-fw"></i></small></a>
          </h3>
        </div>
      </div>
      <div class="row">
        <div class=col-12>
        <form class="form-inline" method="post" action="' . $this->g->cfg['self'] . '">
          <input type="hidden" name="c" value="' . $_SESSION['c'] . '">
          <input type="hidden" name="o" value="' . $this->g->in['o'] . '">
          <input type="hidden" name="i" id="i" value="0">
          <input type="hidden" name="did" value="' . $in['did'] . '">
          <input type="hidden" name="domain" value="' . $in['domain'] . '">
          <div class="col-xl-3 col-md-6 col-12 my-2">
            <div class="input-group">
              <input type="text" class="form-control" id="name" name="name" data-regex="^([^.]+\.)*[^.]*$" value="">
            </div>
          </div>
          <div class="col-xl-3 col-md-6 col-12 my-2">
            <div class="input-group">
              <input type="text" class="form-control" id="content" name="content" data-regex="^.+$" value="">
            </div>
          </div>
          <div class="col-xl-2 col-md-4 col-8 my-2">
            <div class="input-group">' . ($this->dropdown($this->types, 'type', 'A', '', 'custom-select')) . '
            </div>
          </div>
          <div class="col-xl-1 col-md-2 col-4 my-2">
            <div class="input-group">
              <input type="text" class="form-control" id="prio" name="prio" data-regex="^[0-9]*$" value="0">
            </div>
          </div>
          <div class="col-xl-2 col-md-4 col-8 my-2">
            <div class="input-group">
              <input type="text" class="form-control" id="ttl" name="ttl" data-regex="^[0-9]*$" value="300">
            </div>
          </div>
          <div class="col-xl-1 col-md-2 col-4 my-2">
            <div class="input-group">
              <input class="btn btn-secondary form-control" type="submit" id="m" name="m" value="?" disabled>
            </div>
          </div>
        </form>
      </div>
      </div>
      <div class="row">
        <div class="col-12">
          <table id=records class="table table-sm" style="min-width:1100px;table-layout:fixed">
            <thead>
              <tr>
                <th>Name</th>
                <th>Content</th>
                <th>Type</th>
                <th>Priority</th>
                <th>TTL</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
            </tbody>
          </table>
        </div>
        <script>
$(document).ready(function() {

  $("#records").DataTable({
    "processing": true,
    "serverSide": true,
    "ajax": "?x=json&o=records&m=list&did=' . $in['did'] . '",
    "order": [[ 9, "desc" ]],
    "scrollX": true,
    "columnDefs": [
      {"targets":0,   "width":"30%"},
      {"targets":1,   "width":"40%",
        render: function ( data, type, row ) {
            return type === "display" && data.length > 40 ? data.substr( 0, 40 ) + "…" : data; }},
      {"targets":2,   "width":"3rem"},
      {"targets":3,   "width":"3rem"},
      {"targets":4,   "width":"3rem"},
      {"targets":5,   "width":"2rem", "className":"text-right", "sortable": false},
      {"targets":6,   "visible":false},
      {"targets":7,   "visible":false},
      {"targets":8,   "visible":false},
      {"targets":9,   "visible":false},
    ],
  });

  $(document).on("click", ".create", {}, function() {
    $("#m").val("Create");
    $("#m").attr("class", "btn btn-success");
    $("#m").removeAttr("disabled");
    $("#name").val("");
    $("#content").val("");
    return false;
  });

  $(document).on("click", ".delete", {}, function() {
    $("#m").val("Delete");
    $("#m").attr("class", "btn btn-danger");
    $("#m").removeAttr("disabled");
    return false;
  });

  $(document).on("click", ".update", {}, function() {
    $("#m").val("Update");
    $("#m").attr("class", "btn btn-primary");
    $("#m").removeAttr("disabled");
    return false;
  });

  $(document).on("click", ".update,.delete", {}, function() {
    var row = $(this).closest("tr");
    $("#i").val($(this).attr("data-rowid"));
    $("#name").val(row.find("td:eq(0)").text());
    $("#content").val(row.find("td:eq(1)").text());
    $("#type").val(row.find("td:eq(2)").text());
    $("#prio").val(row.find("td:eq(3)").text());
    $("#ttl").val(row.find("td:eq(4)").text());
    return false;
  });

//    if (row.data("active"))
//      $("#active").attr("checked", "on");
//    else $("#active").removeAttr("checked");

});
        </script>';
    }
}

?>
