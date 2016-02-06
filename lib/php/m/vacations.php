<?php declare(strict_types = 1);?>
<?php error_log(__FILE__);?>
<?php
// m_forwards.php 20160205 (C) 2015 Mark Constable <markc@renta.net> (AGPL-3.0)

class m_Vacations extends Crud
{
    protected $b = '
    <h2>Mail Vacations</h2>
    <p>
This is a simple users system, you can
<a href="?o=m_vacations&m=create" title="Create">create</a> a new vacation or
<a href="?o=m_vacations&m=read" title="List">list</a> them at your leisure.
    </p>';
    protected $in = [
        'id'        => '',
        'uid'       => '',
        'spacing'   => '',
        'active'    => '',
        'message'   => '',
        'updated'   => '',
        'created'   => '',
    ];
}
