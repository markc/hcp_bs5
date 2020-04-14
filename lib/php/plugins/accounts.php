<?php
// lib/php/plugins/users.php 20150101 - 20200414
// Copyright (C) 2015-2020 Mark Constable <markc@renta.net> (AGPL-3.0)

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
    ];

    protected function create() : string
    {
        if (util::is_adm()) return parent::create();
        util::log('You are not authorized to perform this action, please contact your administrator.');
        util::relist();
    }

    protected function read() : string
    {
elog(__METHOD__);

        $usr = db::read('*', 'id', $this->g->in['i'], '', 'one');
        if (!$usr) {
            util::log('User not found.');
            util::relist();
        }

        if (util::is_acl(0)) {
            // superadmin
        } elseif (util::is_acl(1)) { // normal admin
            if ($_SESSION['usr']['grp'] != $usr['grp']) {
                util::log('You are not authorized to perform this action.');
                util::relist();
            }
        } else { // Other users
            if ($_SESSION['usr']['id'] != $usr['id']) {
                util::log('You are not authorized to perform this action.');
                util::relist();
            }
        }
        return $this->t->read($usr);
    }

    protected function delete() : string
    {
elog(__METHOD__);

        if (util::is_post()) return parent::delete();
    }

    protected function list() : string
    {
elog(__METHOD__);

        if ($this->g->in['x'] === 'json') {
            $columns = [
                ['dt' => null, 'db' => 'id'],
                ['dt' => 0, 'db' => 'login', 'formatter' => function($d, $row) {
                    return '
                    <b><a href="?o=accounts&m=read&i=' . $row['id'] . '">' . $d . '</a></b>';
                }],
                ['dt' => 1, 'db' => 'fname'],
                ['dt' => 2, 'db' => 'lname'],
                ['dt' => 3, 'db' => 'altemail'],
                ['dt' => 4, 'db' => 'acl', 'formatter' => function($d) { return $this->g->acl[$d]; }],
                ['dt' => 5, 'db' => 'grp'],
            ];
            return json_encode(db::simple($_GET, 'accounts', 'id', $columns), JSON_PRETTY_PRINT);
        }
        return $this->t->list($this->in);
    }

    protected function switch_user()
    {
elog(__METHOD__);

        if (util::is_adm() and !is_null($this->g->in['i'])) {
            $_SESSION['usr'] = db::read('id,acl,grp,login,fname,lname,webpw,cookie', 'id', $this->g->in['i'], '', 'one');
            util::log('Switch to user: ' . $_SESSION['usr']['login'], 'success');
        } else util::log('Not authorized to switch users');
        util::relist();
    }
}

?>
