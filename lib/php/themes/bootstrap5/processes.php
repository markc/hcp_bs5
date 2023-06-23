<?php

declare(strict_types=1);
// lib/php/themes/bootstrap/processes.php 20170225 - 20230604
// Copyright (C) 2015-2023 Mark Constable <markc@renta.net> (AGPL-3.0)

class Themes_Bootstrap5_Processes extends Themes_Bootstrap5_Theme
{
    public function list(array $in): string
    {
        return '
        <div class="d-flex justify-content-between mb-4">
          <h3 class="mb-0"><i class="bi bi-envelope"></i> Processes <small>(' . (count(explode("\n", $in['procs'])) - 1) . ')</small></h3>
          <form method="post" class="form-inline">
            <input type="hidden" name="c" value="' . $_SESSION['c'] . '">
            <input type="hidden" name="m" value="processes">
            <div class="form-group">
                <button type="submit" class="btn btn-primary"><i class="bi bi-arrow-repeat"></i> Refresh</button>
            </div>
          </form>
        </div>
        <div class="row">
          <div class="col-md-8 ms-auto me-auto">
            <pre style="overflow-x: auto;">' . $in['procs'] . '
            </pre>
          </div>
        </div>';
    }
}
