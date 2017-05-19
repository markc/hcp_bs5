<?php
// lib/php/themes/bootstrap/infosys.php 20170225 - 20170513
// Copyright (C) 2015-2017 Mark Constable <markc@renta.net> (AGPL-3.0)

class Themes_Bootstrap_InfoSys extends Themes_Bootstrap_Theme
{
    public function list(array $in) : string
    {
error_log(__METHOD__);

error_log(var_export($in,true));

        extract($in);

        return '
        <div class="row">
          <div class="col-6">
            <h3><i class="fa fa-server fa-fw"></i> System Info</h3>
          </div>
          <div class="col-6">
            <form method="post" class="form-inline pull-right">
              <div class="form-group">
                <input type="hidden" id="o" name="o" value="infosys">
                <button type="submit" class="btn btn-primary"><i class="fa fa-refresh fa-fw" aria-hidden="true"></i> Refresh</button>
              </div>
            </form>
          </div>
        </div>
        <div class="row">
          <div class="col-md-6">
            <br>
            <h5>RAM <small>' . $mem_used . ' / ' . $mem_total . ', ' . $mem_free . ' free</small></h5>
            <div class="progress">
              <div class="progress-bar bg-' . $mem_color . '" role="progressbar" aria-valuenow="' . $mem_pcnt . '"
              aria-valuemin="0" aria-valuemax="100" style="width:' . $mem_pcnt . '%" title="Used Memory">' . $mem_text . '
              </div>
            </div>
            <br>
            <h5>Disk <small>' . $dsk_used . ' / ' . $dsk_total . ', ' . $dsk_free . ' free</small></h5>
            <div class="progress">
              <div class="progress-bar bg-' . $dsk_color . '" role="progressbar" aria-valuenow="' . $dsk_pcnt . '"
              aria-valuemin="0" aria-valuemax="100" style="width:' . $dsk_pcnt . '%" title="Used Disk Space">' . $dsk_text . '
              </div>
            </div>
            <br>
            <h5>CPU <small>' .$cpu_all  . '</small></h5>
            <div class="progress">
              <div class="progress-bar bg-' . $cpu_color . '" role="progressbar" aria-valuenow="' . $cpu_pcnt . '"
              aria-valuemin="0" aria-valuemax="100" style="width:' . $cpu_pcnt . '%" title="Used Disk Space">' . $cpu_text . '
              </div>
            </div>
            <br>
          </div>
          <div class="col-md-6">
            <div class="table-responsive">
              <table class="table table-sm table-bordered">
                <tbody>
                  <tr>
                    <td><b>Hostname</b></td>
                    <td>' .$hostname  . '</td>
                  </tr>
                  <tr>
                    <td><b>Host IP</b></td>
                    <td>' . $host_ip . '</td>
                  </tr>
                  <tr>
                    <td><b>Distro</b></td>
                    <td>' . $os_name . '</td>
                  </tr>
                  <tr>
                    <td><b>Uptime</b></td>
                    <td>' . $uptime . '</td>
                  </tr>
                  <tr>
                    <td><b>CPU Load</b></td>
                    <td>' . $loadav . ' (' . $cpu_num . ' cpus)</td>
                  </tr>
                  <tr>
                    <td><b>CPU Model</b></td>
                    <td>' . $cpu_name . '</td>
                  </tr>
                  <tr>
                    <td><b>Kernel Version</b></td>
                    <td>' . $kernel . '</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-md-12">
            <h5>Process List <small>(' . $proc_num . ')</small></h5>
            <pre>' . $proc_list . '
            </pre>
          </div>
        </div>';
    }
}

?>
