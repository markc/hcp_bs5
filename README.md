# netserva/www

**2016-02-06** -- _Copyright (C) 2016 Mark Constable (AGPL-3.0)_

The beginning of a lightweight admin panel for Ubuntu (xenial) mail and web
servers using nginx (HTTP/2) with PHP7 and courier-mta from this PPA...

    deb http://ppa.launchpad.net/ondrej/php-7.0/ubuntu xenial main
    deb http://ppa.launchpad.net/ondrej/courier/ubuntu xenial main
    deb http://ppa.launchpad.net/nginx/development/ubuntu xenial main

To get started testing this web application...

* create a MySQL database called `sysadm`
* give a `sysadm` MySQL user access to the `sysadm` database
* `cat lib/sql/sysadm.sql | mysql sysadm`
* create `lib/.ht_pw.php` with `<?php return 'YOUR_MYSQL_PW';` 
* make sure nginx is pointed to this cloned repo directory
* for now, manually add a user to the `w_users` table
* surf to the URL and play around
* TODO: provide some example SQL data to get started

NOTE: this initial import does not actually create or manage any services yet.
It just manipulates the first set of DB tables to flesh out the web interface.
There will be an associated `netserva/bin` project that will contain a set of
plain old standalone bash scripts that actually provision the various services
and also be available to this web interface via `exec(sudo ns $key $cmd $arg)`
using `sysadm ALL=NOPASSWD: /usr/local/sbin/ns` in `/etc/sudoers`. The `ns`
shell command router is in a root owned directory and only executes certain
other scripts in the same `/usr/local/sbin` directory and requires a special
`sudo key` to be passed in as the first arg. Very simple but quite safe and
much quicker than relying on a root owned cron job stored in a database.
