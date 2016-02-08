<?php declare(strict_types = 1);?>
<?php error_log(__FILE__);?>
<?php
// home.php 20151015 (C) 2015 Mark Constable <markc@renta.net> (AGPL-3.0)

//<h2><i class="fa fa-home fa-fw"></i> Netserva SysAdm</h2>

return $t->title('NetServa') . '
      <p>
        <strong>
The beginning of a lightweight admin panel for Ubuntu (xenial) mail and web servers
        </strong>
      </p>
      <p>
This initial import into Github does not actually create or manage any
services yet. It just manipulates the first set of DB tables to flesh out
the web interface. There will be an associated netserva/bin project that
will contain a set of plain old standalone bash scripts that actually
provision the various services and also be available to this web interface
via exec(sudo ns $key $cmd $arg) using sysadm ALL=NOPASSWD:
/usr/local/sbin/ns in /etc/sudoers. The ns shell command router is in a
root owned directory and only executes certain other scripts in the same
/usr/local/sbin directory and requires a special sudo key to be passed in
as the first arg. Very simple but quite safe and much quicker than relying
on a root owned cron job stored in a database.
      </p>
      <p>
Comments and pull requests are most welcome via the Issue Tracker link below.
      </p>
      <p>' . $t->a('https://github.com/netserva/www', 'Project Page', 'primary')
           . $t->a('https://github.com/netserva/www/issues', 'Issue Tracker', 'primary') . '
      </p>';
