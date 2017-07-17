<?php
// lib/php/plugins/users.php 20150101 - 20170306
// Copyright (C) 2015-2017 Mark Constable <markc@renta.net> (AGPL-3.0)

class Plugins_Accounts extends Plugin
{
    protected
    $tbl = 'accounts',
    $in = [
        'grp'       => 1,
        'acl'       => 2,
        'vhosts'    => 1,
        'login'     => '',
        'fname'     => '',
        'lname'     => '',
        'altemail'  => '',
        'webpw'     => '',
    ];

    protected function list() : string
    {
error_log(__METHOD__);

        if (util::is_acl(0)) { // superadmin
            $where = '';
            $wval = '';
        } elseif (util::is_acl(1)) { // normal admin
            $where = 'grp';
            $wval = $_SESSION['usr']['id'];
        } else {
            $where = 'id';
            $wval = $_SESSION['usr']['id'];
         }

        $pager = util::pager(
            (int) util::ses('p'),
            (int) $this->g->cfg['perp'],
            (int) db::read('count(id)', $where, $wval, '', 'col')
        );

        return $this->t->list(array_merge(
            db::read('*', $where, $wval, 'ORDER BY `updated` DESC LIMIT ' . $pager['start'] . ',' . $pager['perp']),
            ['pager' => $pager]
        ));
    }

    protected function switch_user()
    {
error_log(__METHOD__);

        if (util::is_adm() and !is_null($this->g->in['i'])) {
            $_SESSION['usr'] = db::read('id,acl,grp,login,fname,lname,webpw,cookie', 'id', $this->g->in['i'], '', 'one');
            util::log('Switch to user: ' . $_SESSION['usr']['login'], 'success');
        } else util::log('Not authorized to switch users');
        return $this->list();
    }
}

?>
