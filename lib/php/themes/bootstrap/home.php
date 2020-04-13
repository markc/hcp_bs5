<?php
// lib/php/themes/bootstrap/home.php 20150101 - 20180503
// Copyright (C) 2015-2018 Mark Constable <markc@renta.net> (AGPL-3.0)

class Themes_Bootstrap_Home extends Themes_Bootstrap_Theme
{
    public function list(array $in) : string
    {
elog(__METHOD__);

        return '
    <div class="col-12">
      <h3>
        <i class="fas fa-server fa-fw"></i> NetServa HCP
      </h3>
      <p class="columns">
This is an ultra simple web based <b>Hosting Control Panel</b> for a
lightweight Mail, Web and DNS server based on Ubuntu Bionic (18.04). It
uses PowerDNS for DNS, Postfix/Dovecot + Spamprobe for SMTP and spam
filtered IMAP email hosting along with nginx + PHP7 FPM + LetsEncrypt SSL
for efficient and secure websites. It can use either SQLite or MySQL as
database backends and the SQLite version only requires <b>60Mb</b> of ram
on a fresh install so it is ideal for lightweight 256Mb ram LXD containers
or KVM/Xen cloud provisioning.
      </p>
      <p>
Some of the features are...
      </p>
      <ul>
        <li><b>NetServa HCP</b> does not reqire Python or Ruby, just PHP and Bash</li>
        <li>Fully functional Mail server with personalised Spam filtering</li>
        <li>Secure SSL enabled <a href="http://nginx.org/">nginx</a> web server with <a href="http://www.php.net/manual/en/install.fpm.php">PHP FPM 7+</a></li>
        <li>Always based and tested on the latest release of <a href="https://kubuntu.org">*buntu</a></li>
        <li>Optional DNS server for local LAN or real-world DNS provisioning</li>
        <li>Built from the ground up using <a href="https://getbootstrap.com">Bootstrap 4</a> and <a href="https://datatables.net/examples/styling/bootstrap4">DataTables</a></li>
      </ul>
      <p>
You can change the content of this page by creating a file called
<code>lib/php/home.tpl</code> and add any Bootstrap 4 based layout and
text you care to. For example...
      </p>
<pre>
&lt;div class="col-12"&gt;
&lt;h1>Your Page Title&lt;/h1&gt;
&lt;p>Lorem ipsum...&lt;/p&gt;
&lt;/div&gt;
</pre>
      <p>
Modifying the navigation menus above can be done by creating
a <code>lib/.ht_conf.php</code> file and copying the
<a href="https://github.com/netserva/hcp/blob/master/index.php#L60">
$nav1 array</a> from <code>index.php</code> into that optional config override file.
Comments and pull requests are most welcome via the Issue Tracker link below.
      </p>
      <p class="text-center">
        <a class="btn btn-primary" href="https://github.com/netserva/hcp">
          <i class="fas fa-code-branch fa-fw"></i> Project Page</a>
        <a class="btn btn-primary" href="https://github.com/netserva/hcp/issues">
          <i class="fas fa-ticket-alt fa-fw"></i> Issue Tracker</a>
      </p>
    </div>';
    }
}

?>
