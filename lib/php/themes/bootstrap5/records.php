<?php

declare(strict_types=1);

// lib/php/themes/bootstrap5/records.php 20180714 - 20240906
// Copyright (C) 2015-2024 Mark Constable <markc@renta.net> (AGPL-3.0)

class Themes_Bootstrap5_Records extends Themes_Bootstrap5_Theme
{
    private const TYPES = [
        'A', 'MX', 'NS', 'TXT', 'AAAA', 'CAA', 'AFSDB', 'CERT', 'CNAME', 'DHCID',
        'DLV', 'DNSKEY', 'DS', 'EUI48', 'EUI64', 'HINFO', 'IPSECKEY', 'KEY', 'KX',
        'LOC', 'MINFO', 'MR', 'NAPTR', 'NSEC', 'NSEC3', 'NSEC3PARAM', 'OPT', 'PTR',
        'RKEY', 'RP', 'RRSIG', 'SPF', 'SRV', 'SSHFP', 'TLSA', 'TSIG', 'WKS'
    ];

    public function list(array $in): string
    {
elog(__METHOD__);

        return $this->generateHtml($in) . $this->generateJavaScript($in);
    }

    private function generateHtml(array $in): string
    {
elog(__METHOD__);

        $csrfToken      = $_SESSION['c'] ?? '';
        $currentObject  = $this->g->in['o'] ?? '';
        $domainId       = $in['did'] ?? '';
        $domain         = htmlspecialchars($in['domain'] ?? '');
        $typeDropdown   = $this->dropdown(array_map(fn($type) => [$type, $type], self::TYPES), 'type', 'A', '', 'form-select');

        return <<<HTML
        <div class="col-12">
          <h1>
            <a href="?o=domains&m=list"><i class="bi bi-chevron-double-left"></i></a>{$domain}
            <a class="create" href="" title="Create new DNS record">
              <small><i class="bi bi-plus-circle"></i></small>
            </a>
          </h1>
        </div>
        </div>
        <div class="row">
          <div class="col-12">
            <form class="form-inline" method="post" action="{$this->g->cfg['self']}">
              <input type="hidden" name="c" value="{$csrfToken}">
              <input type="hidden" name="o" value="{$currentObject}">
              <input type="hidden" name="i" id="i" value="0">
              <input type="hidden" name="did" value="{$domainId}">
              <input type="hidden" name="domain" value="{$domain}">
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
                <div class="input-group">{$typeDropdown}</div>
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
            <table id="records" class="table table-sm" style="min-width:1100px;table-layout:fixed">
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
        HTML;
    }

    private function generateJavaScript(array $in): string
    {
elog(__METHOD__);

        $domainId = $in['did'] ?? '';
        return <<<JavaScript
        <script>
        $(document).ready(function() {
          $("#records").DataTable({
            "processing": true,
            "serverSide": true,
            "ajax": "?x=json&o=records&m=list&did={$domainId}",
            "order": [[ 9, "desc" ]],
            "scrollX": true,
            "columnDefs": [
              {"targets":0, "width":"30%"},
              {"targets":1, "width":"40%",
                render: function ( data, type, row ) {
                    return type === "display" && data.length > 40 ? data.substr( 0, 40 ) + "…" : data; }},
              {"targets":2, "width":"3rem"},
              {"targets":3, "width":"3rem"},
              {"targets":4, "width":"3rem"},
              {"targets":5, "width":"2rem", "className":"text-end", "sortable": false},
              {"targets":6, "visible":false},
              {"targets":7, "visible":false},
              {"targets":8, "visible":false},
              {"targets":9, "visible":false},
            ],
          });

          $(document).on("click", ".create", function(e) {
            e.preventDefault();
            $("#m").val("Create").attr("class", "btn btn-success").removeAttr("disabled");
            $("#name, #content").val("");
          });

          $(document).on("click", ".delete", function(e) {
            e.preventDefault();
            $("#m").val("Delete").attr("class", "btn btn-danger").removeAttr("disabled");
          });

          $(document).on("click", ".update", function(e) {
            e.preventDefault();
            $("#m").val("Update").attr("class", "btn btn-primary").removeAttr("disabled");
          });

          $(document).on("click", ".update,.delete", function(e) {
            e.preventDefault();
            var row = $(this).closest("tr");
            $("#i").val($(this).attr("data-rowid"));
            $("#name").val(row.find("td:eq(0)").text());
            $("#content").val(row.find("td:eq(1)").text());
            $("#type").val(row.find("td:eq(2)").text());
            $("#prio").val(row.find("td:eq(3)").text());
            $("#ttl").val(row.find("td:eq(4)").text());
          });
        });
        </script>
        JavaScript;
    }
}
