<?php
// lib/php/themes/bootstrap/infomail.php 20170225 - 20170513
// Copyright (C) 2015-2017 Mark Constable <markc@renta.net> (AGPL-3.0)

class Themes_Bootstrap_InfoMail extends Themes_Bootstrap_Theme
{
    public function list(array $in) : string
    {
error_log(__METHOD__);

        extract($in);

        return '
          <div class="col-6">
            <h3><i class="fas fa-envelope fa-fw"></i> MailServer Info</h3>
          </div>
          <div class="col-6">
            <form method="post" class="form-inline">
              <div class="form-group ml-auto">
                <input type="hidden" id="m" name="m" value="pflog_renew">
                <button type="submit" class="btn btn-primary"><i class="fas fa-sync-alt fa-fw" aria-hidden="true"></i> Refreshed ' . $pflog_time . ' ago</button>
              </div>
            </form>
          </div>
        </div><!-- END UPPER ROW -->
        <div class="row">
          <div class="col-12">
            <h5>Mail Queue</h5>
            <pre>' . $mailq . '</pre>
          </div>
        </div>
        <div class="row">
          <div class="col-12">
            <pre>' . $pflogs . '
            </pre>
          </div>
        </div>';
    }
}
//            <textarea rows="20" style="font-family:monospace;font-size:9pt;width:100%;">' . $pflogs . '</textarea>

?>
