<?php

declare(strict_types=1);
// lib/php/themes/bootstrap/infosys.php 20170225 - 20230625
// Copyright (C) 2015-2023 Mark Constable <markc@renta.net> (AGPL-3.0)

class Themes_Bootstrap_InfoSys extends Themes_Bootstrap_Theme
{
    public function list(array $in): string
    {
        extract($in);

        return '
        <div class="d-flex justify-content-between mb-4">
          <h3 class="mb-0"><i class="bi bi-server"></i> System Info</h3>
          <form method="post" class="form-inline">
            <input type="hidden" name="c" value="' . $_SESSION['c'] . '">
            <input type="hidden" name="o" value="infosys">
            <div class="form-group">
                <button type="submit" class="btn btn-primary"><i class="bi bi-arrow-repeat"></i> Refresh</button>
            </div>
          </form>
        </div>
        <div class="row align-items-md-stretch">
          <div class="col-md-6 mb-4 order-md-0 order-last">
            <div class="pt-md-2 px-md-5 py-3 px-2 border rounded-3">
              <table class="table table-sm table-borderless mb-0">
                <tbody>
                  <tr>
                    <td><b>Hostname</b></td>
                    <td>' . $hostname . '</td>
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
                    <td>' . $loadav . ' - ' . $cpu_num . ' cpus</td>
                  </tr>
                  <tr>
                    <td><b>CPU Model</b></td>
                    <td>' . $cpu_name . '</td>
                  </tr>
                  <tr>
                    <td><b>Kernel&nbsp;Version</b></td>
                    <td>' . $kernel . '</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
          <div class="col-md-6 mb-4">
            <div class="py-md-3 px-md-5 py-3 px-2 border rounded-3">
              <div><b>RAM</b><br>Used: ' . $mem_used . ' - Total: ' . $mem_total . ' - Free: ' . $mem_free . '</div>
              <div class="progress mb-2">
                <div class="progress-bar bg-' . $mem_color . '" role="progressbar" aria-valuenow="' . $mem_pcnt . '"
                  aria-valuemin="0" aria-valuemax="100" style="width:' . $mem_pcnt . '%" title="Used Memory">' . $mem_text . '
                </div>
              </div>
              <div><b>DISK</b><br>Used: ' . $dsk_used . ' - Total: ' . $dsk_total . ' - Free: ' . $dsk_free . '</div>
              <div class="progress mb-2">
                <div class="progress-bar bg-' . $dsk_color . '" role="progressbar" aria-valuenow="' . $dsk_pcnt . '"
                  aria-valuemin="0" aria-valuemax="100" style="width:' . $dsk_pcnt . '%" title="Used Disk Space">' . $dsk_text . '
                </div>
              </div>
              <div><b>CPU</b><br>' . $cpu_all . '</div>
              <div class="progress mb-2">
                <div class="progress-bar bg-' . $cpu_color . '" role="progressbar" aria-valuenow="' . $cpu_pcnt . '"
                  aria-valuemin="0" aria-valuemax="100" style="width:' . $cpu_pcnt . '%" title="Used Disk Space">' . $cpu_text . '
                </div>
              </div>
            </div>
          </div>
        </div>';
    }
}
