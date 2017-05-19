# NetServa HCP

This is an ultra simple PHP based Hosting Control Panel for a lightweight
Mail and Web server on a recent (developed on 17.04) Ubuntu Server.

## Hosting Control Panel (WIP)

This project depends on `Postfix + Dovecot + Spamprobe` for SMTP and spam
filtered IMAP email hosting along with `nginx + PHP7 FPM + LetsEncrypt SSL`
for efficient and secure websites. It can also optionally use `PowerDNS`
for real-world DNS hosting or easy web-based local LAN or container DNS
resolution. It uses either SQLite or MySQL as database backends and the
SQLite version only requires ~100MB of ram (pdns + pdns-recursor takes
another ~30MB) on a fresh install so it is ideal for LXD containers or
cheap 256MB to 512MB VPS plans. Some of the features are...

- NetServa HCP does not reqire Python or Ruby, just PHP and Bash
- Fully functional Mail, Web and DNS server with Spam filtering
- Built from the ground up using Bootstrap 4 and jQuery 3

## Usage

The PHP web interface relies on the [Shell Helper] scripts being installed
on the primary and target hosts so as root...

    curl -s https://raw.githubusercontent.com/netserva/sh/master/bin/setup-sh | bash

_Please review the very simple script by removing the `| bash` part first._

Once installed and activated then `gethost` should show the current config
settings. Use the `es` alias to edit (ctrl-x to save and quit) and activate
any custom env vars and aliases.

TODO: describe LXD testing setup

Once the target system is available then `ssh` (or `lxc exec LXD bash`)
into the target system, install and activate the [Shell Helper] scripts
again and...

    setup-all sqlite

This may take 5 or 10 mintes to complete depending on the bandwidth
available to the target server. Once finished you should be able to go to
`http://$VHOST` and login to the HCP web interface as `admin@$VHOST`
with a default password of `changeme_N0W`, and immediately change that
password.

---

If using `mysql` then run `es` and set the `DPASS` variable to the MySQL
user password.

#### Note: mysql setup is not fully tested and working yet.

_All scripts and documentation are Copyright (C) 1995-2017 Mark Constable
and Licensed [AGPL-3.0]_

[Shell Helper]: https://github.com/netserva/sh
[AGPL-3.0]: http://www.gnu.org/licenses/agpl-3.0.html
