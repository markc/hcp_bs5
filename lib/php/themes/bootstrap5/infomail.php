<?php

declare(strict_types=1);

// lib/php/themes/bootstrap5/infomail.php 20170225 - 20240906
// Copyright (C) 2015-2024 Mark Constable <markc@renta.net> (AGPL-3.0)

class Themes_Bootstrap5_InfoMail extends Themes_Bootstrap5_Theme
{
    public function list(array $in): string
    {
elog(__METHOD__);

        $csrfToken = $_SESSION['c'] ?? '';
        $mailq = htmlspecialchars($in['mailq'] ?? '');
        $pflogs = htmlspecialchars($in['pflogs'] ?? '');

        return <<<HTML
        <div class="d-flex justify-content-between mb-4">
            <h1 class="mb-0"><i class="bi bi-envelope"></i> Mailserver Info</h1>
            <form method="post" class="form-inline">
                <input type="hidden" name="c" value="{$csrfToken}">
                <input type="hidden" name="m" value="pflog_renew">
                <div class="form-group">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-arrow-repeat"></i> Refresh</button>
                </div>
            </form>
        </div>
        <div class="container">
            <div class="col-md-6 ms-auto me-auto">
                <h3>Mail Queue</h3>
                <pre class="overflow-auto">{$mailq}</pre>
            </div>
        </div>
        <div class="container">
            <div class="col-md-6 ms-auto me-auto">
                <pre class="overflow-auto">{$pflogs}</pre>
            </div>
        </div>
        HTML;
    }
}
