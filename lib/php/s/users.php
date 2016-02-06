<?php declare(strict_types = 1);?>
<?php error_log(__FILE__);?>
<?php
// users.php 20151018 (C) 2015 Mark Constable <markc@renta.net> (AGPL-3.0)

class s_Users extends Crud
{
    protected $b = '
    <h2>System Users</h2>
    <p>
This is a simple users system, you can
<a href="?o=s_users&m=create" title="Create">create</a> a new user or
<a href="?o=s_users&m=read" title="List">list</a> them at your leisure.
    </p>';
    protected $in = [
        'id'        => '',
        'uid'       => '',
        'gid'       => '',
        'username'  => '',
        'gecos'     => '',
        'homedir'   => '',
        'shell'     => '',
        'password'  => '',
        'lstchg'    => '',
        'min'       => '',
        'max'       => '',
        'warn'      => '',
        'inact'     => '',
        'expire'    => '',
        'flag'      => '',
        'updated'   => '',
        'created'   => '',
    ];
//    protected $update = 'Update System User';
//    protected $create = 'Create System User';
}
