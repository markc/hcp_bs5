<?php

declare(strict_types=1);

// lib/php/themes/bootstrap5/home.php 20150101 - 20240906
// Copyright (C) 2015-2024 Mark Constable <markc@renta.net> (AGPL-3.0)

class Themes_Bootstrap5_Home extends Themes_Bootstrap5_Theme
{
    public function list(array $in): string
    {
elog(__METHOD__);

        return <<<HTML
        <div class="p-md-5 p-2 mb-4 bg-body-tertiary rounded-3 text-center">
            <h1 class="display-5 fw-bold mt-3"><i class="bi bi-boxes"></i> NetServa HCP</h1>
            <p class="col col-md-8 fs-5 ms-auto me-auto py-md-3">
                This is a lightweight Web, Mail and DNS server with a PHP based
                <b>Hosting Control Panel</b> for servicing multiple virtually
                hosted domains. The operating system is based on the latest
                Debian or Ubuntu packages and can use either SQLite or MySQL as
                a backend database. The entire server can run in as little as 256
                MB of ram when paired with SQLite and still serve a dozen lightly
                loaded hosts so it is ideal for LXD and Proxmox virtual machines
                and containers.
            </p>
            <a class="btn btn-primary btn-lg m-2" href="https://github.com/markc/hcp">
                <i class="bi bi-github"></i> Project Page
            </a>
            <a class="btn btn-primary btn-lg m-2" href="https://github.com/markc/hcp/issues">
                <i class="bi bi-github"></i> Issue Tracker
            </a>
        </div>
        <div class="row align-items-md-stretch">
            <div class="col-md-6 mb-4 order-md-0 order-last">
                <div class="h-100 p-md-5 p-2 border rounded-3">
                    <h2 class="text-md-start text-center">Features</h2>
                    <ul>
                        <li><b>NetServa HCP</b> does not require Python or Ruby, just PHP and Bash</li>
                        <li>Fully functional Mail server with personalised Spam filtering</li>
                        <li>Secure SSL enabled <a href="http://nginx.org/">nginx</a> web server with <a href="http://www.php.net/manual/en/install.fpm.php">PHP FPM 8+</a></li>
                        <li>Always based and tested on the latest release on <a href="https://ubuntu.com">Ubuntu</a> and <a href="https://debian.org">Debian</a></li>
                        <li>Optional DNS server for local LAN or real-world DNS provisioning</li>
                        <li>Built from the ground up using <a href="https://getbootstrap.com">Bootstrap 5</a> and <a href="https://datatables.net/examples/styling/bootstrap5">DataTables</a></li>
                    </ul>
                    <h2 class="text-md-start text-center">Software</h2>
                    <ul>
                        <li>nginx and PHP8+ FPM for web services</li>
                        <li>Postfix for SMTP email delivery</li>
                        <li>Dovecot and Spamprobe spam filtered IMAP email</li>
                        <li>Acme.sh and LetsEncrypt SSL for SSL certificates</li>
                        <li>PowerDNS for DNS</li>
                        <li>WordPress when paired with Mysql/Mariadb</li>
                    </ul>
                </div>
            </div>
            <div class="col-md-6 mb-4">
                <div class="h-100 p-md-5 p-2 border rounded-3">
                    <h2 class="text-md-start text-center">Notes</h2>
                    <p>
                        You can change the content of this page by creating a file
                        called <code>lib/php/home.tpl</code> and add any Bootstrap 5
                        based layout and text you care to. Modifying the navigation
                        menus above can be done by creating a <code>lib/.ht_conf.php</code>
                        file and copying the
                        <a href="https://github.com/markc/hcp/blob/master/index.php#L77">\$nav1 array</a>
                        from <code>index.php</code> into that optional config override file.
                    </p>
                </div>
            </div>
        </div>
        HTML;
    }
}
