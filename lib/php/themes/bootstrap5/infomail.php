<?php

declare(strict_types=1);
// lib/php/themes/bootstrap/infomail.php 20170225 - 20230604
// Copyright (C) 2015-2023 Mark Constable <markc@renta.net> (AGPL-3.0)

class Themes_Bootstrap5_InfoMail extends Themes_Bootstrap5_Theme
{
    public function list(array $in): string
    {
        extract($in);

        return '
        <div class="d-flex justify-content-between mb-4">
          <h3 class="mb-0"><i class="bi bi-envelope"></i> Mailserver Info</h3>
          <form method="post" class="form-inline">
            <input type="hidden" name="c" value="' . $_SESSION['c'] . '">
            <input type="hidden" name="m" value="pflog_renew">
            <div class="form-group">
                <button type="submit" class="btn btn-primary"><i class="bi bi-arrow-repeat"></i> Refresh</button>
            </div>
          </form>
        </div>
        <div class="container">
          <div class="col-md-6 ms-auto me-auto">
            <h3>Mail Queue</h3>
            <pre style="overflow-x: auto;">' . $mailq . '
            </pre>
          </div>
        </div>
        <div class="container">
          <div class="col-md-6 ms-auto me-auto">
            <pre style="overflow-x: auto;">' . $pflogs . '
            </pre>
          </div>
        </div>';
    }
}
//            <textarea rows="20" style="font-family:monospace;font-size:9pt;width:100%;">' . $pflogs . '</textarea>
