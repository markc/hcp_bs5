<?php

declare(strict_types=1);
// lib/php/themes/mazer/mailgraph.php 20170225 - 20230605
// Copyright (C) 2015-2023 Mark Constable <markc@renta.net> (AGPL-3.0)

class Themes_Mazer_MailGraph extends Themes_Mazer_Theme
{
    public function list(array $in): string
    {
        return '
        <h3><i class="fa fa-envelope fa-fw" aria-hidden="true"></i> MailServer Graph</h3>
        <div class="row">
          <div class="col-md-12 text-center">' . $in['mailgraph'] . '
          </div>
        </div>';
    }
}
