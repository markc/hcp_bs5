<?php declare(strict_types = 1);?>
<?php error_log(__FILE__);?>
<?php
// w_users.php 20151018 (C) 2015-2016 Mark Constable <markc@renta.net> (AGPL-3.0)

class w_Users extends Crud
{
    protected $b = '
    <h2>Web Users</h2>
    <p>
This is a simple users system, you can
<a href="?o=w_users&m=create" title="Create">create</a> a new user or
<a href="?o=w_users&m=read" title="List">list</a> them at your leisure.
    </p>';
    protected $in = [
        'uid'       => '',
        'fname'     => '',
        'lname'     => '',
        'altemail'  => '',
        'webpw'     => '',
        'otp'       => '',
        'anote'     => '',
        'updated'   => '',
        'created'   => '',
    ];
}
