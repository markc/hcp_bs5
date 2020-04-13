<?php
// lib/php/themes/bootstrap/processes.php 20170225 - 20180512
// Copyright (C) 2015-2018 Mark Constable <markc@renta.net> (AGPL-3.0)

class Themes_Bootstrap_Processes extends Themes_Bootstrap_Theme
{
    public function list(array $in) : string
    {
elog(__METHOD__);

        return '
          <div class="col-12 col-sm-6">
            <h3><i class="fas fa-code-branch fa-fw"></i> Processes</h3>
          </div>
          <div class="col-12 col-sm-6">
            <form method="post" class="form-inline">
              <input type="hidden" name="c" value="' . $_SESSION['c'] . '">
              <input type="hidden" id="o" name="o" value="processes">
              <div class="form-group ml-auto">
                <button type="submit" class="btn btn-primary"><i class="fas fa-sync-alt fa-fw" aria-hidden="true"></i> Refresh</button>
              </div>
            </form>
          </div>
        </div><!-- END UPPER ROW -->
        <div class="row">
          <div class="col-12">
            <h5>Process List <small>(' . (count(explode("\n", $in['procs'])) - 1) . ')</small></h5>
            <pre><code>' . $in['procs'] . '
            </code></pre>
          </div>
        </div>';
    }
}

?>
