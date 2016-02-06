<?php declare(strict_types = 1);?>
<?php error_log(__FILE__);?>
<?php
// s_usergroups.php 20160206 (C) 2015 Mark Constable <markc@renta.net> (AGPL-3.0)

class s_UserGroups extends Crud
{
    protected $b = '
    <h2>System User Groups</h2>
    <p>
This is a simple users system, you can
<a href="?o=s_usergroups&m=create" title="Create">create</a> a new user or
<a href="?o=s_usergroups&m=read" title="List">list</a> them at your leisure.
    </p>';
    protected $in = [
        'id'        => '',
        'name'      => '',
        'password'  => '',
        'gid'       => '',
        'updated'   => '',
        'created'   => '',
    ];
//    protected $update = 'Update System User';
//    protected $create = 'Create System User';
}
