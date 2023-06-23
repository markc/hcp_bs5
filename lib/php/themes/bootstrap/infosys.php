<?php

declare(strict_types=1);
// lib/php/themes/bootstrap/infosys.php 20170225 - 20180512
// Copyright (C) 2015-2018 Mark Constable <markc@renta.net> (AGPL-3.0)

class Themes_Bootstrap_InfoSys extends Themes_Bootstrap_Theme
{
    public function list(array $in): string
    {
        elog(var_export($in, true));

        extract($in);

        return '
          <div class="col-6">
            <h3><i class="fas fa-server fa-fw"></i> System Info</h3>
          </div>
          <div class="col-6">
            <form method="post" class="form-inline">
              <input type="hidden" name="c" value="' . $_SESSION['c'] . '">
              <input type="hidden" name="o" value="infosys">
              <div class="form-group ml-auto">
                <button type="submit" class="btn btn-primary"><i class="fas fa-sync-alt fa-fw" aria-hidden="true"></i> Refresh</button>
              </div>
            </form>
          </div>
        </div><!-- END UPPER ROW -->
        <div class="row">
          <div class="col-6">
            <div class="table-responsive">
              <table class="table table-sm table-bordered">
                <tbody>
                  <tr>
                    <td class="w-25"><b>Hostname</b></td>
                    <td class="w-75">' . $hostname . '</td>
                  </tr>
                  <tr>
                    <td class="w-25"><b>Host IP</b></td>
                    <td class="w-75">' . $host_ip . '</td>
                  </tr>
                  <tr>
                    <td class="w-25"><b>Distro</b></td>
                    <td class="w-75">' . $os_name . '</td>
                  </tr>
                  <tr>
                    <td class="w-25"><b>Uptime</b></td>
                    <td class="w-75">' . $uptime . '</td>
                  </tr>
                  <tr>
                    <td class="w-25"><b>CPU Load</b></td>
                    <td class="w-75">' . $loadav . ' (' . $cpu_num . ' cpus)</td>
                  </tr>
                  <tr>
                    <td class="w-25"><b>CPU Model</b></td>
                    <td class="w-75">' . $cpu_name . '</td>
                  </tr>
                  <tr>
                    <td class="w-25"><b>Kernel Version</b></td>
                    <td class="w-75">' . $kernel . '</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
          <div class="col-6">
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
            <h5>CPU <small>' . $cpu_all . '</small></h5>
            <div class="progress">
              <div class="progress-bar bg-' . $cpu_color . '" role="progressbar" aria-valuenow="' . $cpu_pcnt . '"
              aria-valuemin="0" aria-valuemax="100" style="width:' . $cpu_pcnt . '%" title="Used Disk Space">' . $cpu_text . '
              </div>
            </div>
            <br>
          </div>
        </div>';
    }
}
