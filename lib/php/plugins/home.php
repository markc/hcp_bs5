<?php
// lib/php/plugins/home.php 20150101 - 20170317
// Copyright (C) 2015-2017 Mark Constable <markc@renta.net> (AGPL-3.0)

class Plugins_Home extends Plugin
{
    public function list() : string
    {
error_log(__METHOD__);

        $buf = '
      <h3>
        <i class="fa fa-home fa-fw"></i> NetServa
        <small>(Hosting Control Panel)</small>
      </h3>
      <p>
This is an ultra simple web based Hosting Control Panel for a lightweight
DNS, Mail and Web server based on Ubuntu Server (minimum Zesty 17.04). It
uses PowerDNS for DNS, Postfix/Dovecot + Spamprobe for SMTP and spam filtered
IMAP email hosting along with nginx + PHP7 FPM + LetsEncrypt SSL for efficient
and secure websites. It can use either SQLite or MySQL as database backends
and the SQLite version only requires <b>60Mb</b> of ram on a fresh install so
is ideal for LXD containers or 256Mb VPS plans. Some of the features are...
      </p>
      <ul>
        <li> <b>NetServa</b> does not reqire Python or Ruby, just PHP and Bash</li>
        <li> Fully functional DNS, Mail and Web server with Spam filtering</li>
        <li> Built from the ground up using Bootstrap 4 and jQuery 3</li>
      </ul>
      <p>
Comments and pull requests are most welcome via the Issue Tracker link below.
      </p>
      <p class="text-center">
        <a class="btn btn-primary" href="https://github.com/netserva/www">Project Page</a>
        <a class="btn btn-primary" href="https://github.com/netserva/www/issues">Issue Tracker</a>
      </p>

<table data-toggle="table"
       data-page-list="[5, 10, 20, 50, 100]"
       data-pagination="true"
       data-search="true"
       data-side-pagination="server"
       data-url="?o=home&m=read&x=json">
    <thead>
    <tr>
        <th data-field="state" data-checkbox="true"></th>
        <th data-field="id" data-align="right" data-sortable="true">Item ID</th>
        <th data-field="name" data-align="center" data-sortable="true">Item Name</th>
        <th data-field="price" data-sortable="true">Item Price</th>
    </tr>
    </thead>
</table>';
        return $this->t->list(['buf' => $buf]);
    }

    public function read() : string
    {
error_log(__METHOD__);

        $buf = '
{
  "total": 20,
  "rows": [
    {
      "id": 0,
      "name": "Item 0",
      "price": "$0"
    },
    {
      "id": 1,
      "name": "Item 1",
      "price": "$1"
    },
    {
      "id": 2,
      "name": "Item 2",
      "price": "$2"
    },
    {
      "id": 3,
      "name": "Item 3",
      "price": "$3"
    },
    {
      "id": 4,
      "name": "Item 4",
      "price": "$4"
    },
    {
      "id": 5,
      "name": "Item 5",
      "price": "$5"
    },
    {
      "id": 6,
      "name": "Item 6",
      "price": "$6"
    },
    {
      "id": 7,
      "name": "Item 7",
      "price": "$7"
    },
    {
      "id": 8,
      "name": "Item 8",
      "price": "$8"
    },
    {
      "id": 9,
      "name": "Item 9",
      "price": "$9"
    },
    {
      "id": 10,
      "name": "Item 10",
      "price": "$10"
    },
    {
      "id": 11,
      "name": "Item 11",
      "price": "$11"
    },
    {
      "id": 12,
      "name": "Item 12",
      "price": "$12"
    },
    {
      "id": 13,
      "name": "Item 13",
      "price": "$13"
    },
    {
      "id": 14,
      "name": "Item 14",
      "price": "$14"
    },
    {
      "id": 15,
      "name": "Item 15",
      "price": "$15"
    },
    {
      "id": 16,
      "name": "Item 16",
      "price": "$16"
    },
    {
      "id": 17,
      "name": "Item 17",
      "price": "$17"
    },
    {
      "id": 18,
      "name": "Item 18",
      "price": "$18"
    },
    {
      "id": 19,
      "name": "Item 19",
      "price": "$19"
    }
  ]
}
';
        return $this->t->list(['buf' => $buf]);
    }
}

?>
