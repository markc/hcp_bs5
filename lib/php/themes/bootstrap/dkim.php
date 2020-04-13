<?php
// lib/php/themes/bootstrap/dkim.php 20180511 - 20180529
// Copyright (C) 2015-2018 Mark Constable <markc@renta.net> (AGPL-3.0)

class Themes_Bootstrap_Dkim extends Themes_Bootstrap_Theme
{
    public function read(array $in) : string
    {
elog(__METHOD__);

        $remove = $this->modal([
            'id'      => 'removemodal',
            'title'   => 'Remove DKIM Record',
            'action'  => 'delete',
            'footer'  => 'Remove',
            'hidden'  => '
                <input type="hidden" name="domain" value="' . $in['domain'] . '">',
            'body'    => '
                  <p class="text-center">Are you sure you want to remove DKIM record for<br><b>' . $in['domain'] . '</b></p>',
        ]);

        return '
            <div class="col-12">
              <h3>
                <a href="?o=dkim&m=list"><i class="fas fa-angle-double-left fa-fw"></i></a> DKIM
                <a href="" title="Remove this DKIM record" data-toggle="modal" data-target="#removemodal">
                  <small><i class="fas fa-trash fa-fw cursor-pointer text-danger"></i></small></a>
              </h3>
            </div>
          </div><!-- END UPPER ROW -->
          <div class="row">
            <div class="col-12">' . $in['buf'] . '
            </div>
          </div>
        </div>' . $remove;

    }

    public function list(array $in) : string
    {
elog(__METHOD__);

        $keybuf = $this->dropdown([
            ['1024', '1024'],
            ['2048', '2048'],
            ['4096', '4096'],
        ], 'keylen', '2048', '', 'custom-select');

        $create = $this->modal([
            'id'      => 'createmodal',
            'title'   => 'Create DKIM Record',
            'action'  => 'create',
            'footer'  => 'Create',
            'body'    => '
                  <div class="form-group">
                    <label for="user" class="form-control-label">Domain</label>
                    <input type="text" class="form-control" id="domain" name="domain">
                  </div>
                  <div class="row">
                    <div class="col-6">
                      <div class="form-group">
                        <label for="selector" class="form-control-label">Selector</label>
                        <input type="text" class="form-control" id="select" name="select" value="mail">
                      </div>
                    </div>
                    <div class="col-6">
                      <div class="form-group">
                        <label for="keylen" class="form-control-label">Key Length</label>' . $keybuf . '
                      </div>
                    </div>
                  </div>',
        ]);

        return '
            <div class="col-12">
              <h3>
                <i class="fas fa-address-card fa-fw"></i> DKIM
                <a href="#" title="Add New DKIM Key" data-toggle="modal" data-target="#createmodal">
                  <small><i class="fas fa-plus-circle fa-fw"></i></small></a>
              </h3>
            </div>
          </div><!-- END UPPER ROW -->
          <div class="row">
            <div class="col-12">' . $in['buf'] . '
            </div>
          </div>
        </div>' . $create;
    }
}

?>
