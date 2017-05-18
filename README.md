# NetServa HCP

This is an ultra simple web based Hosting Control Panel for a lightweight
Mail and Web server based on a recent Ubuntu Server.

## Hosting Control Panel (WIP)

This project Postfix + Dovecot + Spamprobe for SMTP and spam filtered IMAP
email hosting along with nginx + PHP7 FPM + LetsEncrypt SSL for efficient
and secure websites. It can also optionally use PowerDNS for live DNS
hosting or easy web-based local LAN or container DNS resolution. It uses
either SQLite or MySQL as database backends and the SQLite version only
requires ~100Mb of ram on a fresh install so is ideal for LXD containers
or 256Mb VPS plans. Some of the features are...

- NetServa HCP does not reqire Python or Ruby, just PHP and Bash
- Fully functional DNS, Mail and Web server with Spam filtering
- Built from the ground up using Bootstrap 4 and jQuery 3

## Usage

This project relies on the [Shell Helper] scripts being installed on the
primary and target hosts so as root (please review the script first)...

    curl -s https://raw.githubusercontent.com/netserva/sh/master/bin/setup-sh | bash

Once installed and activated then `gethost` should show the current config
settings. Use the `es` alias to edit and activate any custom env vars and
aliases. The next step is to install a local LXD container or remote server
and repeat setting up the `sh` script repo inside the target server.

TODO: to be continued rsn...

_All scripts and documentation are Copyright (C) 1995-2017 Mark Constable and Licensed [AGPL-3.0]_

[Shell Helper]: https://github.com/netserva/sh
