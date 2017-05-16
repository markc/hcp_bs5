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
        <div class="row">
          <div class="col-md-6">
            <h3 class="min300">
              <i class="fa fa-envelope-o fa-fw"></i> MailServer Info
            </h3>
          </div>
          <div class="col-md-6">
            <form method="post" class="form-inline pull-right">
              <label class="mr-sm-2" for="m">
                <i class="fa fa-clock-o fa-fw" aria-hidden="true"></i> ' . $pflog_time . '
              </label>
              <div class="form-group">
                <input type="hidden" id="m" name="m" value="pflog_renew">
                <button type="submit" class="btn btn-primary"><i class="fa fa-refresh fa-fw" aria-hidden="true"></i> Refresh</button>
              </div>
            </form>
          </div>
        </div>
        <div class="row">
          <div class="col-md-12">
            <h5>Mail Queue</h5>
            <pre>' . $mailq . '</pre>
          </div>
        </div>
        <div class="row">
          <div class="col-md-12">
            <pre>' . $pflogs . '
            </pre>
          </div>
        </div>';
    }
}
//            <textarea rows="20" style="font-family:monospace;font-size:9pt;width:100%;">' . $pflogs . '</textarea>

?>
