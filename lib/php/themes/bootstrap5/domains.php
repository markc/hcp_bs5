<?php

declare(strict_types=1);

// lib/php/themes/bootstrap5/domains.php 20170225 - 20240906
// Copyright (C) 2015-2024 Mark Constable <markc@renta.net> (AGPL-3.0)

class Themes_Bootstrap5_Domains extends Themes_Bootstrap5_Theme
{
    public function create(array $in): string
    {
elog(__METHOD__);

        return $this->modal_content([
            'title'     => 'Create DNS Zone',
            'action'    => 'create',
            'lhs_cmd'   => '',
            'rhs_cmd'   => 'Create',
            'body'      => $this->modal_body($in)
        ]);
    }

    public function update(array $in): string
    {
elog(__METHOD__);

        return $this->editor($in);
    }

    public function list(array $in): string
    {
elog(__METHOD__);

        return <<<HTML
        <div class="row">
          <h1>
            <i class="bi bi-globe"></i> Domains
            <a href="?o=domains&m=create" class="bslink" title="Add new domain">
              <small><i class="bi bi-plus-circle"></i></small>
            </a>
          </h1>
        </div>
        <div class="table-responsive">
            <table id="domains" class="table table-borderless table-striped w-100">
              <thead>
                <tr>
                  <th>Name</th>
                  <th>Type</th>
                  <th>Records</th>
                  <th>Serial</th>
                  <th></th>
                </tr>
              </thead>
              <tbody>
              </tbody>
            </table>
        </div>
        <div class="modal fade" id="createmodal" tabindex="-1" role="dialog" aria-labelledby="createmodal" aria-hidden="true">
          <div class="modal-dialog" id="createdialog">
          </div>
        </div>
        <div class="modal fade" id="shwhomodal" tabindex="-1" role="dialog" aria-labelledby="readmodal" aria-hidden="true">
          <div class="modal-dialog" id="shwhodialog">
          </div>
        </div>
        <div class="modal fade" id="deletemodal" tabindex="-1" role="dialog" aria-labelledby="deletemodal" aria-hidden="true">
          <div class="modal-dialog" id="deletedialog">
          </div>
        </div>
        <script>
        $(document).ready(function() {
          $("#domains").DataTable({
            "processing": true,
            "serverSide": true,
            "ajax": { "url": "?x=json&o=domains&m=list", "deferLoading": 10 },
            "order": [[ 5, "desc" ]],
            "scrollX": true,
            "columnDefs": [
              {"targets":0, "className":"text-truncate", "width":"40%"},
              {"targets":4, "width":"3rem", "className":"text-right", "sortable": false},
              {"targets":5, "visible":false},
            ],
          });

          $(document).on("click", ".bslink", function(event){
            event.preventDefault();
            var url = $(this).attr("href") + "&x=html";
            var m = new URLSearchParams(url).get("m");
            $("#" + m + "dialog").load(url, function() {
              $("#" + m + "modal", document).modal("show");
            });
          });

          $(document).on("click", ".serial", function() {
            var a = $(this);
            $.post("?x=text&increment=1&" + this.toString().split("?")[1], function(data) {
              $(a).text(data);
            });
            return false;
          });
        });
        </script>
        HTML;
    }

    private function editor(array $in): string
    {
elog(__METHOD__);

        $domain = $in['name'];
        $soa = $in['soa'] ?? ['', '', '', 7200, 540, 604800, 300];
        $soa = is_array($soa) ? $soa : explode(' ', $soa);

        $isCreate = $this->g->in['m'] === 'create';
        $serial = $isCreate ? '' : "&nbsp;&nbsp;<small>Serial: {$soa[2]}</small>";
        $header = $isCreate ? 'Add Domain' : $domain;
        $submit = $isCreate
            ? '<a class="btn btn-secondary" href="?o=domains&m=list">&laquo; Back</a>
               <button type="submit" id="m" name="m" value="create" class="btn btn-primary">Add Domain</button>'
            : '<a class="btn btn-secondary" href="?o=domains&m=list">&laquo; Back</a>
               <button type="submit" id="m" name="m" value="update" class="btn btn-primary">Update</button>';
        $hidden = $isCreate ? '' : "<input type=\"hidden\" name=\"serial\" value=\"{$soa[2]}\">";

        return <<<HTML
        <div class="col-12">
          <h1>
            <i class="bi bi-globe"></i> {$header}{$serial}
            <a href="" title="Add new domain" data-bs-toggle="modal" data-bs-target="#createmodal">
              <small><i class="bi bi-plus-circle"></i></small>
            </a>
          </h1>
        </div>
        <div class="row">
          <div class="col-12">
            <form method="post" action="{$this->g->cfg['self']}">
              <input type="hidden" name="c" value="{$_SESSION['c']}">
              <input type="hidden" name="o" value="{$this->g->in['o']}">
              <input type="hidden" name="i" value="{$this->g->in['i']}">
              {$hidden}
              <div class="row">
                <div class="col-3">
                  <div class="mb-3">
                    <label for="primary" class="form-label">Primary</label>
                    <input type="text" class="form-control" id="primary" name="primary" value="{$soa[0]}" required>
                  </div>
                </div>
                <div class="col-3">
                  <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="text" class="form-control" id="email" name="email" value="{$soa[1]}" required>
                  </div>
                </div>
                <div class="col-1">
                  <div class="mb-3">
                    <label for="refresh" class="form-label">Refresh</label>
                    <input type="text" class="form-control" id="refresh" name="refresh" value="{$soa[3]}" required>
                  </div>
                </div>
                <div class="col-1">
                  <div class="mb-3">
                    <label for="retry" class="form-label">Retry</label>
                    <input type="text" class="form-control" id="retry" name="retry" value="{$soa[4]}" required>
                  </div>
                </div>
                <div class="col-2">
                  <div class="mb-3">
                    <label for="expire" class="form-label">Expire</label>
                    <input type="text" class="form-control" id="expire" name="expire" value="{$soa[5]}" required>
                  </div>
                </div>
                <div class="col-2">
                  <div class="mb-3">
                    <label for="ttl" class="form-label">TTL</label>
                    <input type="text" class="form-control" id="ttl" name="ttl" value="{$soa[6]}" required>
                  </div>
                </div>
              </div>
              <div class="row">
                <div class="col-12 text-end">
                  <div class="btn-group">
                    {$submit}
                  </div>
                </div>
              </div>
            </form>
          </div>
        </div>
        HTML;
    }

    public function delete(): ?string
    {
elog(__METHOD__);

        $tmp = db::read('name', 'id', $this->g->in['i'], '', 'one');

        return $this->modal_content([
            'title'     => 'Remove Domain',
            'action'    => 'delete',
            'lhs_cmd'   => '',
            'rhs_cmd'   => 'Remove',
            'hidden'    => "<input type=\"hidden\" name=\"i\" value=\"{$this->g->in['i']}\">",
            'body'      => "<p class=\"text-center\">Are you sure you want to remove this domain?<br><b>{$tmp['name']}</b></p>",
        ]);
    }

    public function shwho(string $name, string $body): string
    {
elog(__METHOD__);

        return $this->modal_content([
            'title'     => "Whois summary: <b>{$name}</b>",
            'action'    => 'shwho',
            'lhs_cmd'   => '',
            'rhs_cmd'   => '',
            'body'      => "<pre>{$body}</pre>",
        ]);
    }

    private function modal_body(array $in): string
    {
elog(__METHOD__);

        return <<<HTML
        <div class="row mb-3">
          <div class="col-6">
            <label for="domain" class="form-label">Domain</label>
            <input type="text" class="form-control" id="domain" name="domain" value="{$in['domain']}" required>
          </div>
          <div class="col-6">
            <label for="ip" class="form-label">IP</label>
            <input type="text" class="form-control" id="ip" name="ip" value="{$in['ip']}" required>
          </div>
        </div>
        <div class="row mb-3">
          <div class="col-6">
            <label for="ns1" class="form-label">NS1</label>
            <input type="text" class="form-control" id="ns1" name="ns1" value="{$in['ns1']}" required>
          </div>
          <div class="col-6">
            <label for="ns2" class="form-label">NS2</label>
            <input type="text" class="form-control" id="ns2" name="ns2" value="{$in['ns2']}" required>
          </div>
        </div>
        <div class="row mb-3">
          <div class="col-6">
            <label for="mxhost" class="form-label">MXHost</label>
            <input type="text" class="form-control" id="mxhost" name="mxhost" value="{$in['mxhost']}" required>
          </div>
          <div class="col-6">
            <label for="spfip" class="form-label">SPF IP</label>
            <input type="text" class="form-control" id="spfip" name="spfip" value="{$in['spfip']}" required>
          </div>
        </div>
        HTML;
    }
}
