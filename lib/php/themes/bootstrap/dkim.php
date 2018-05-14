<?php
// lib/php/themes/bootstrap/dkim.php 20180511 - 20180514
// Copyright (C) 2015-2018 Mark Constable <markc@renta.net> (AGPL-3.0)

class Themes_Bootstrap_Dkim extends Themes_Bootstrap_Theme
{
     public function list(array $in) : string
    {
error_log(__METHOD__);

        $dkim_length_buf = $this->dropdown([
            ['1024', '1024'],
            ['2048', '2048'],
        ], 'bits', '', '', 'custom-select');

        return '
            <div class="col-12">
              <h3>
                <i class="fas fa-address-card fa-fw"></i> DKIM
                <a href="#" title="Add New DKIM" data-toggle="modal" data-target="#createmodal">
                  <small><i class="fas fa-plus-circle fa-fw"></i></small>
                </a>
              </h3>
            </div>
          </div><!-- END UPPER ROW -->
          <div class="row">
            <div class="col-12">' . $in['buf'] . '
            </div>
          </div>
        </div>
        <div class="modal fade" id="createmodal" tabindex="-1" role="dialog" aria-labelledby="createmodal" aria-hidden="true">
          <div class="modal-dialog" role="document">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title">Add DKIM</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>
              <form method="post" action="' . $this->g->cfg['self'] . '">
                <input type="hidden" name="c" value="' . $_SESSION['c'] . '">
                <input type="hidden" name="o" value="' . $this->g->in['o'] . '">
                <input type="hidden" name="i" value="' . $this->g->in['i'] . '">
                <input type="hidden" name="m" value="create">
                <div class="modal-body">
                  <div class="form-group">
                    <label for="user" class="form-control-label">Domain</label>
                    <input type="text" class="form-control" id="domain" name="domain">
                  </div>
                  <div class="row">
                    <div class="col-6">
                      <div class="form-group">
                        <label for="selector" class="form-control-label">Selector</label>
                        <input type="text" class="form-control" id="selector" name="selector" value="dkim">
                      </div>
                    </div>
                    <div class="col-6">
                      <div class="form-group">
                        <label for="bits" class="form-control-label">Bit Length</label>' . $dkim_length_buf . '
                      </div>
                    </div>
                  </div>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                  <button type="submit" class="btn btn-primary">Add New DKIM Record</button>
                </div>
              </form>
            </div>
          </div>
        </div>
    ';
    }
}

?>
