<?php declare(strict_types = 1);?>
<?php error_log(__FILE__);?>
<?php
// m_forwards.php 20160205 (C) 2015 Mark Constable <markc@renta.net> (AGPL-3.0)

class m_Forwards extends Crud
{
    protected $b = '
    <h2>Mail Forwards</h2>
    <p>
This is a simple users system, you can
<a href="?o=m_forwards&m=create" title="Create">create</a> a new forward or
<a href="?o=m_forwards&m=read" title="List">list</a> them at your leisure.
    </p>';
    protected $in = [
        'id'        => '',
        'uid'       => '',
        'recipient' => '',
        'updated'   => '',
        'created'   => '',
    ];
}
