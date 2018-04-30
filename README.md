# NetServa HCP (BETA)

This is an ultra simple PHP based Hosting Control Panel for a lightweight
Mail and Web server on a recent (developed for 18.04) Ubuntu Server.

## Hosting Control Panel

This project depends on `Postfix + Dovecot + Spamprobe` for SMTP and spam
filtered IMAP email hosting along with `nginx + PHP7 FPM + LetsEncrypt
SSL` for efficient and secure websites. It can also optionally use
`PowerDNS` for real-world DNS hosting or easy web-based local LAN or
container DNS resolution. It uses either SQLite or MySQL as database
backends and the SQLite version only requires ~100MB of ram (pdns +
pdns-recursor takes another ~30MB) on a fresh install so it is ideal for
LXD containers or cheap 256MB to 512MB VPS plans. Some of the features
are...

- **NetServa HCP/SH** does not reqire Python or Ruby, just PHP and Bash
- Fully functional Mail server with personalised Spam filtering
- Secure SSL enabled [nginx] web server with [PHP FPM 7+]
- Always based and tested on the latest release of [*buntu]
- Optional DNS server for local LAN or real-world DNS provisioning
- Built from the ground up using [Bootstrap 4] and [DataTables]

## Usage

The PHP web interface relies on the [Shell Helper] scripts being installed
on the primary and target hosts so the first thing to do, as root...

    curl -s https://raw.githubusercontent.com/netserva/sh/master/bin/setup-sh | bash

_Please review the very simple script by removing the `| bash` part first._

This installs the `SH` aliases and scripts into a `/root/.sh` directory and
activates the environment variables and special aliases. See the [Shell
Helper] repo for more information about how to use these aliases and
scripts directly. This `HCP` project is just a web based frontend for the
`SH` system which do all the real provisioning and management work.

The first step, after installing the SH scripts, is to make sure the current
host has a hostname and a domainname. The domainname needs to be valid if
using a publically accessible server and that needs the assistence of a real
DNS service provider. Otherwise, if using a local LAN with private IPs
(like 192.168.*, 10.* or 172.*) then you can make up any domainname as long
as your are consistent within your local LAN. In fact using `local.lan` is
a good candidate unless you prefer something else. The hostname of your
current host computer is usually determined when the OS is installed but
can be changed by editing `/etc/hostname` and making sure `/etc/resolv.conf`
has a reference like `search local.lan`. Once you get results like this...

    ~ hostname
    myhost
    ~ hostname -d
    local.lan
    ~ hostname -f
    myhost.local.lan

(where `myhost` and `local.lan` are your real or made up names) then
continue on with the next step.

Now we "normalize" the host by using `setup-host` which updates the primary
hosting **Desktop** or **Server** system to Bionic 18.04 (unless `os release`
is defined.) using the current `hostname -f` unless a **hostname.FQDN** is
passed in as the first `[domain]` argument...

    Usage: setup-host [domain] [(mysql)|sqlite] [admin(sysadm)] [os release(bionic)] [os mirror(archive.ubuntu.com)]

Assuming a LXD container is to be used for the actual server side
(recommended for initial testing anyway) then use `setup-lxd` to install
and setup the basic LXD container system...

    Usage: setup-lxd [pool size (25) GB] [passwd] [IP]

We can now setup the actual NetServa SH/HCP system for testing where `FQDN`
needs to be a **hostname** plus **domainname**, like `c1.domain.name`,
where `c1` will be the container label and `domain.name` can either be a
real domainname (if the server has a public IP) or whatever internal
LAN-wide domainname you care to use...

    Usage: newlxd FQDN [(small)|medium|large] [distro(bionic)] [(mysql)|sqlite]

If the installation procedure can detect an externally available public IP
then it will attempt to install a LetsEncrypt SSL certificate so that the
web server can be accessed via `https` and the mail server will be SSL enabled
and ready for real-world deployment. Otherwise a self-signed certificate will
be installed (which can be a problem for Firefox.) The mail, web, sftp and HCP
login credentials will be available in `cat ~/.vhosts/$(hostname -f).conf`.

The essential configuration settings for the default server will be inside
the container (example only for a local LAN domain called `sysadm.lan`)...

    lxc exec c1 bash
    cat ~/.vhosts/$(hostname -f)

Or, if you already have a containter or remote server ready to use after a
fresh Ubuntu install then you could install the entire NetServa SH and HCP
system by ssh'ing into the system (or for example, "lxc exec c1 bash" for a
local LXD container) and...

    curl -s https://raw.githubusercontent.com/netserva/sh/master/bin/setup-sh | bash
    setup-all

This may take 5 or 15 mintes to complete depending on the bandwidth
available to the target server. Once finished you should be able to go to
`https://real.domain.name/adm` and login to the HCP web interface using the
simple sitewide HTTP `sysadm/1234` authentication first then the real admin
username and password available with `cat ~/.vhosts/$(hostname -f).conf`.

## Config Override

The main `index.php` file is actually the configuration for the entire
program so that the rest of the PHP files could actually be included from
anywhere else on the system (not just from `lib/php`) if the `INC` const
is changed. To override the default settings (so sensitive information is
not committed to some Git repo) a config override file can be put anywhere
(the default being `lib/.ht_conf.php`) in which an array is returned where
any of the top level property array values can be overridden. First review
the main [index.php] file top level properties then compare below as an
example of how to override these property values...

    <?php
    return [
        'cfg' => ['email' => 'YOUR@EMAIL_ADDRESS'],
        'db' => ['type' => 'mysql', 'pass' => 'YOUR_MYSQL_PW'],
        'out' => [
            'doc'   => 'YOUR_SITE_LABEL',
            'head'  => 'YOUR_SITE_LABEL',
            'foot'  => 'Copyright (C) 2018 YOUR_SITE_LABEL',
        ],
    ];

which would change the default email address (for forgotten password etc)
to your email address, set the database to use MySQL with it's password
and change the site titles and footer copyright notice. The SH/HCP system
will use MySQL by default so if you use...

    setup-all $(hostname -f) sqlite

for an extremely lightweight system (minus Wordpress) then use a
`lib/.ht_conf.php` override file like...

    <?php
    return [
        'cfg' => ['email' => 'YOUR@EMAIL_ADDRESS'],
        'db' => ['type' => 'sqlite'],
        'out' => [
            'doc'   => 'YOUR_SITE_LABEL',
            'head'  => 'YOUR_SITE_LABEL',
            'foot'  => 'Copyright (C) 2018 YOUR_SITE_LABEL',
        ],
    ];

Another alternate option for a MySQL password is to create a simple plain
text file called `lib/.ht_pw` and put ONLY the MySQL password in that file
but of course using `lib/.ht_conf.php` instead allows you to modify or
extend any of the top level properties in `index.php`.

The point of the config override is so you can keep doing a `git pull`and
update the HCP web area (either from the NetServa repo or your own fork)
without interference from locally updated files, and `git push` (to your
own git repo) will not upload passwords to a possible public git repo.

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
    # or for KDE kio
    sftp://sysadm@example.org:9/

would result in access to the whole (non-root) file system whereas...

    ~ ssh -p9 u1001@example.org
    # or for KDE kio
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
[Bootstrap 4]: https://getbootstrap.com
[DataTables]: https://datatables.net/examples/styling/bootstrap4
[index.php]: https://github.com/netserva/www/blob/master/index.php
[nginx]: http://nginx.org
[PHP FPM 7+]: http://www.php.net/manual/en/install.fpm.php
[*buntu]: https://kubuntu.org

