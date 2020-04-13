<?php
// lib/php/plugins/mailgraph.php 20170225 - 20170514
// Copyright (C) 2015-2017 Mark Constable <markc@renta.net> (AGPL-3.0)

class Plugins_MailGraph extends Plugin
{
    public function list() : string
    {
elog(__METHOD__);

        $return = '';
        $images = ['0-n', '1-n', '2-n', '3-n'];
        foreach ($images as $image) {
            $image = 'http://localhost:81/mailgraph.cgi?' . $image;
            $headers = get_headers($image);
            $return_code = substr($headers[0], 9, 3);
            if ($return_code >= 400) {
                $return .= '<img class="img-responsive" alt="not-yet-available" src="data:image/png;base64,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" /><p>Not enough data</p>';
            } else {
                $imageData = base64_encode(file_get_contents($image));
                $return .= '<img class="img-responsive" alt="' . $image . '" src="data:image/png;base64,' . $imageData . '" />';
            }
        }
        return $this->t->list(['mailgraph' => $return]);
    }
}

?>
