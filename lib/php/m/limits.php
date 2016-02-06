<?php declare(strict_types = 1);?>
<?php error_log(__FILE__);?>
<?php
// m_limits.php 20160205 (C) 2015 Mark Constable <markc@renta.net> (AGPL-3.0)

class m_Limits extends Crud
{
    protected $b = '
    <h2>Mail Limits</h2>
    <p>
This is a simple users system, you can
<a href="?o=m_limits&m=create" title="Create">create</a> a new limit or
<a href="?o=m_limits&m=read" title="List">list</a> them at your leisure.
    </p>';
    protected $in = [
        'id'        => '',
        'domain'    => '',
        'maxaccounts' => '',
        'maxaccountsize' => '',
        'maxaccountcount' => '',
        'maxforwards' => '',
        'maxforwardsrcpt' => '',
        'updated'   => '',
        'created'   => '',
    ];
}
