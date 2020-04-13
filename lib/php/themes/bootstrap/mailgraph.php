<?php
// lib/php/themes/bootstrap/mailgraph.php 20170225 - 20170514
// Copyright (C) 2015-2017 Mark Constable <markc@renta.net> (AGPL-3.0)

class Themes_Bootstrap_MailGraph extends Themes_Bootstrap_Theme
{
    public function list(array $in) : string
    {
elog(__METHOD__);

        return '
        <h3><i class="fa fa-envelope fa-fw" aria-hidden="true"></i> MailServer Graph</h3>
        <div class="row">
          <div class="col-md-12 text-center">' . $in['mailgraph'] . '
          </div>
        </div>';
    }
}

?>
