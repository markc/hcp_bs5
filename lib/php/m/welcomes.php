<?php declare(strict_types = 1);?>
<?php error_log(__FILE__);?>
<?php
// m_welcomes.php 20160205 (C) 2015 Mark Constable <markc@renta.net> (AGPL-3.0)

class m_Welcomes extends Crud
{
    protected $b = '
    <h2>Mail Welcomes</h2>
    <p>
This is a simple users system, you can
<a href="?o=m_welcomes&m=create" title="Create">create</a> a new welcome or
<a href="?o=m_welcomes&m=read" title="List">list</a> them at your leisure.
    </p>';
    protected $in = [
        'id'        => '',
        'domain'    => '',
        'deliver'   => '',
        'use_default' => '',
        'process'   => '',
        'from_addr' => '',
        'from_name' => '',
        'subject'   => '',
        'message'   => '',
        'updated'   => '',
        'created'   => '',
    ];
}
