# NetServa

_Copyright (C) 2015-2017 Mark Constable <markc@renta.net> (AGPL-3.0)_

## Hosting Control Panel

This is an ultra simple web based Hosting Control Panel for a lightweight
DNS, Mail and Web server based on Ubuntu Server (minimum Zesty 17.04). It
uses PowerDNS for DNS, Postfix/Dovecot + Spamprobe for SMTP and spam
filtered IMAP email hosting along with nginx + PHP7 FPM + LetsEncrypt SSL
for efficient and secure websites. It can use either SQLite or MySQL as
database backends and the SQLite version only requires 60Mb of ram on a
fresh install so is ideal for LXD containers or 256Mb VPS plans. Some of
the features are...

. NetServa does not reqire Python or Ruby, just PHP and Bash
. Fully functional DNS, Mail and Web server with Spam filtering
. Built from the ground up using Bootstrap 4 and jQuery 3
