# NetServa HCP

This is an ultra simple PHP based Hosting Control Panel for a lightweight
Mail and Web server on a recent (developed on 17.04) Ubuntu Server.

**NOTE:** as of August 2017 this system is about 95% usable and getting
very close to a first alpha release.

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

Assuming a LXD container is to be used (recommended for initial testing)
then try `setup-lxd C1.DOMAIN.NAME` where `C1` will be the container
name and `C1.DOMAIN.NAME` will be the internal FQDN hostname. This setup
script will also install LXD and create a ZFS pool of 50GB by default.

    Usage: setup-lxd FQDN [small|medium|large] [pool size (50)]

Or if you already have a containter or remote server ready to use after
a fresh Ubuntu install then you could install the entire NetServa SH
and HCP system by ssh'ing into the system (or "lxc exec C1 bash" for
a container.)

    setup-all sqlite

This may take 5 or 10 mintes to complete depending on the bandwidth
available to the target server. Once finished you should be able to go
to `http://$VHOST` and login to the HCP web interface as `admin@$VHOST`
with a default password of `changeme_N0W`, and immediately change that
password.

---

## Config Override

The main `index.php` file is actually the configuration for the entire
program so that the rest of the PHP files could actually be included from
anywhere else on the system, not just from `lib/php`, if the `INC` const
is changed. To override the default settings (so sensitive information
is not committed to some Git repo) a config override file can be put
anywhere (the default being `lib/.ht_conf.php`) in which an array is
returned where any of the top level property array values can be overridden.
First review the main `index.php` file top level properties then compare
below as an example of how to override those property values...

    <?php
    return [
        'cfg' => ['email' => 'YOUR@EMAIL_ADDRESS'],
        'db' => ['type' => 'mysql', 'pass' => 'YOUR_MYSQL_PW'],
    ];

which would change the default email address (for forgotten password etc)
to your email address and set the database to use MySQL with it's password.

Another alternate option for a MySQL password is to create a simple plain
text file called `lib/.ht_pw` and put ONLY the MySQL password in that file
but of course using `lib/.ht_conf.php` allows you to modify or extend any
of the top level properties in `index.php`.

---

## Athentication

During installation three random passwords will be auto-created in `/root/.pw`
along with a `/root/.my.cnf` if MySQL is being used. The first one will be
used for the HCP web interface admin@VHOST user, the second will be the MySQL
password for `root` and `sysadm` and the third one will be for the `sysadm`
SSH password if a SSH key is not available.

The `sysadm` user has access to the entire server with SUDO permissions to
the `/root/.sh/bin/*` shell scripts but not general SUDO access to any other
executables. This user also "owns" the default `adm.YOUR_DOMAIN` web area
with the NetServa HCP web interface, if used.

All extra virtual hosts will be owned by `u1000 u1001 u1002 etc` system users
which will be chrooted, or locked into, their respective VHOST web area. For
instance...

    ~ shhost all
    sysadm  adm.example.org                         /home/u/adm.example.org
    u1001   example.org                             /home/u/example.org
    u1002   example.com                             /home/u/example.com
    u1003   example.net                             /home/u/example.net

where the above resulted from...

    setup-lxd example.org
    # then SSH/exec into the server and...
    addvhost example.com sqlite
    addvhost example.net sqlite

The authentication point being that using SSH or SFTP (ie; from Dolphin) to
this server as...

    ssh -p9 sysadm@example.org
    # or
    sftp://sysadm@example.org:9/

would result in access to the whole (non-root) file system whereas...

    ssh -p9 u1001@example.org
    # or
    sftp://u1001@example.org:9/

would chroot or lock access to the `/home/u/example.org` area with no
possibility of using SUDO so folks only interested in working on a web site
have reasonably safe access to only that web area.

`setup-ssh` can be used to manage local SSH keys to make logging in to a
container or remote server much easier.

    Usage: setup-ssh domain [targethost] [user] [port] [sshkey]


_All scripts and documentation are Copyright (C) 1995-2017 Mark Constable
and Licensed [AGPL-3.0]_

[Shell Helper]: https://github.com/netserva/sh
[AGPL-3.0]: http://www.gnu.org/licenses/agpl-3.0.html
