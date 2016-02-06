<?php declare(strict_types = 1);?>
<?php error_log(__FILE__);?>
<?php
// m_users.php 20151018 (C) 2015 Mark Constable <markc@renta.net> (AGPL-3.0)

class m_Users extends Crud
{
    protected $b = '
    <h2>Mail Users</h2>
    <p>
This is a simple users system, you can
<a href="?o=m_users&m=create" title="Create">create</a> a new user or
<a href="?o=m_users&m=read" title="List">list</a> them at your leisure.
    </p>';
    protected $in = [
        'id'        => '',
        'uid'       => '',
        'crypt'     => '',
        'clear'     => '',
        'name'      => '',
        'muid'      => '',
        'mgid'      => '',
        'mquota'    => '',
        'mpath'     => '',
        'maildir'   => '',
        'delivery'  => '',
        'options'   => '',
        'acl'       => '',
        'spam'      => '',
        'updated'   => '',
        'created'   => '',
    ];
}
