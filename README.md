# NetServa HCP

This is an ultra simple PHP based Hosting Control Panel for a lightweight
Mail and Web server on a recent (developed on 18.04) Ubuntu Server.

**NOTE:** as of April 2018 this system is about 99% usable and getting
very close to a first beta release.

## Hosting Control Panel (WIP)

This project depends on `Postfix + Dovecot + Spamprobe` for SMTP and spam
filtered IMAP email hosting along with `nginx + PHP7 FPM + LetsEncrypt
SSL` for efficient and secure websites. It can also optionally use
`PowerDNS` for real-world DNS hosting or easy web-based local LAN or
container DNS resolution. It uses either SQLite or MySQL as database
backends and the SQLite version only requires ~100MB of ram (pdns +
pdns-recursor takes another ~30MB) on a fresh install so it is ideal for
LXD containers or cheap 256MB to 512MB VPS plans. Some of the features
are...

- NetServa HCP does not reqire Python or Ruby, just PHP and Bash
- Fully functional Mail, Web and DNS server with Spam filtering
- Built from the ground up using Bootstrap 4 and jQuery 3

## Usage

The PHP web interface relies on the [Shell Helper] scripts being installed
on the primary and target hosts so the first thing to do, as root...

    curl -s https://raw.githubusercontent.com/netserva/sh/master/bin/setup-sh | bash

_Please review the very simple script by removing the `| bash` part first._

This installs the `SH` aliases and scripts into a `/root/.sh` directory and
activates the environment variables and special aliases. See the [Shell
Helper] repo for more information about how to use these aliases and
scripts directly. This `HCP` project is just a web based frontend for the
`SH` system which does all their real work.

The next step is to "normalize" the host by using `setup-host` which
updates the Desktop or Server system to Bionic 18.04 (unless `os release`
is defined.) By default it will use the current `hostname -f` unless a
**hostname.FQDN** is passed in as the first `[domain]` argument...

    Usage: setup-host [domain] [(mysql)|sqlite] [admin(sysadm)] [os release(bionic)] [os mirror(archive.ubuntu.com)]

Assuming a LXD container is to be used for the actual server side
(recommended for initial testing anyway) then use `setup-lxd` to install
and setup the basic LXD container system...

    Usage: setup-lxd [pool size (25) GB] [passwd] [IP]

Now we can setup the actual NetServa SH/HCP system for testing where `FQDN`
needs to be a **hostname** plus **domainname**, like `c1.domain.name`,
where `c1` will be the container label and `domain.name` can either be a
real domainname (if the server has a public IP) or whatever internal
LAN-wide domainname you care to use...

    Usage: newlxd FQDN [(small)|medium|large] [distro(bionic)] [(mysql)|sqlite]

On an internal LAN (without public IP access) this will go ahead and
install the entire system ready to start using at a `http` address. If the
installation procedure can detect an externally available public IP then
it will attempt to install a LetsEncrypt SSL certificate so that web
services can be access via `https` and the mail server will be SSL
enabled and ready for the real-world. The mail, web, sftp and HCP login
credentials will be presented during the installation output.

The essential configuration settings for the default server will be inside
the container (example only for a local LAN domain called `sysadm.lan`)...

    lxc exec c1 bash
    cat ~/.vhosts/c1.sysadm.lan

Or, if you already have a containter or remote server ready to use after a
fresh Ubuntu install then you could install the entire NetServa SH and HCP
system by ssh'ing into the system (or for example, "lxc exec c1 bash" for a
LXD container) and...

    curl -s https://raw.githubusercontent.com/netserva/sh/master/bin/setup-sh | bash
    setup-all

This may take 5 or 10 mintes to complete depending on the bandwidth
available to the target server. Once finished you should be able to go to
`https://real.domain.name/adm` and login to the HCP web interface using the
simple sitewide HTTP `sysadm/1234` authentication first then the real admin
username and password presented at the end of the setup procedure.

## Config Override

The main `index.php` file is actually the configuration for the entire
program so that the rest of the PHP files could actually be included from
anywhere else on the system (not just from `lib/php`) if the `INC` const
is changed. To override the default settings (so sensitive information is
not committed to some Git repo) a config override file can be put anywhere
(the default being `lib/.ht_conf.php`) in which an array is returned where
any of the top level property array values can be overridden. First review
the main `index.php` file top level properties then compare below as an
example of how to override these property values...

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

## Athentication

During installation five random passwords will be auto-created in
`/root/.vhosts/$(hostname -f)` along with a `/root/.my.cnf` with `DPASS`
if MySQL is being used. Example...

    ~ grep PASS ~/.vhosts/$(hostname -f)
    APASS='LheTZOT8eYCrlAk8'  # Admin HCP password
    DPASS='axVps7OIXb7VY4uT'  # Database password, if using MySQL
    EPASS='a5cBBxXL59uAyJkc'  # SMTP/IMAP password for admin@$VHOST
    UPASS='D8G3RgpBgSetyG4o'  # SFTP password
    WPASS='LheTZOT8eYCrlAk8'  # Wordpress admin password, if using MySQL

The initial `sysadm` user has access to most of the server with SUDO
permissions to the provisioning scripts in `/root/.sh/bin/*`. This user
also "owns" the default `YOUR_DOMAIN/adm` web area with the NetServa HCP
web interface. All extra virtual hosts will be owned by `u1000 u1001 u1002
etc` system users which will be chrooted, or locked into, their respective
VHOST web area. For instance...

    ~ shhost all
    sysadm  c1.example.org                          /home/u/c1.example.org
    u1001   example.org                             /home/u/example.org
    u1002   example.com                             /home/u/example.com
    u1003   example.net                             /home/u/example.net

where the above resulted from...

    ~ newlxd c1.example.org
    # then SSH/exec into the container and...
    ~ addvhost example.org
    ~ addvhost example.com
    ~ addvhost example.net

The authentication point being that using SSH or SFTP (ie; from Dolphin) to
this server as...

    ~ ssh -p9 sysadm@example.org
    # or
    sftp://sysadm@example.org:9/

would result in access to the whole (non-root) file system whereas...

    ~ ssh -p9 u1001@example.org
    # or
    sftp://u1001@example.org:9/

would chroot or lock access to the `/home/u/example.org` area with no
possibility of using SUDO so folks only interested in working on a web site
have reasonably safe access to only that web area.

`setup-ssh` can be used on the host to manage local SSH keys making logging
in to a container or remote server much easier...

    Usage: setup-ssh domain [targethost] [user] [port] [sshkeyname]

_All scripts and documentation are Copyright (C) 1995-2018 Mark Constable
and Licensed [AGPL-3.0]_

[Shell Helper]: https://github.com/netserva/sh
[AGPL-3.0]: http://www.gnu.org/licenses/agpl-3.0.html
