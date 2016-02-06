<?php declare(strict_types = 1);?>
<?php error_log(__FILE__);?>
<?php
// s_usergrouplist.php 20160206 (C) 2015 Mark Constable <markc@renta.net> (AGPL-3.0)

class s_UserGroupList extends Crud
{
    protected $b = '
    <h2>System User Group List</h2>
    <p>
This is a simple users system, you can
<a href="?o=s_usergrouplist&m=create" title="Create">create</a> a new user or
<a href="?o=s_usergrouplist&m=read" title="List">list</a> them at your leisure.
    </p>';
    protected $in = [
        'id'        => '',
        'gid'       => '',
        'username'  => '',
        'updated'   => '',
        'created'   => '',
    ];
//    protected $update = 'Update System User';
//    protected $create = 'Create System User';
}
