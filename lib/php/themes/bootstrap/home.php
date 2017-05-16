<?php
// lib/php/themes/bootstrap/home.php 20150101 - 20170317
// Copyright (C) 2015-2017 Mark Constable <markc@renta.net> (AGPL-3.0)

class Themes_Bootstrap_Home extends Themes_Bootstrap_Theme
{
    public function list(array $in) : string
    {
error_log(__METHOD__);

        return $in['buf'];
    }
}

?>
