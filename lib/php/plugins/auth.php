<?php
// lib/php/plugins/auth.php 20150101 - 20170307
// Copyright (C) 2015-2017 Mark Constable <markc@renta.net> (AGPL-3.0)

class Plugins_Auth extends Plugin
{
    protected
    $tbl = 'accounts',
    $in = [
        'id'        => null,
        'acl'       => null,
        'grp'       => null,
        'login'     => '',
        'webpw'     => '',
        'remember'  => '',
        'otp'       => '',
        'passwd1'   => '',
        'passwd2'   => '',
    ];

    public function create() : string
    {
error_log(__METHOD__);

        $u = $this->in['login'];

        if ($_POST) {
            if (filter_var($u, FILTER_VALIDATE_EMAIL)) {
                if ($usr = db::read('id,acl', 'login', $u, '', 'one')) {
                    if ($usr['acl'] != 9) {
                        $newpass = util::genpw();
                        if ($this->mail_forgotpw($u, $newpass, 'From: ' . $this->g->cfg['email'])) {
                            db::update([
                                'otp' => $newpass,
                                'otpttl' => time()
                            ], [['id', '=', $usr['id']]]);
                            util::log('Sent reset password key for "' . $u . '" so please check your mailbox and click on the supplied link.', 'success');
                        } else util::log('Problem sending message to ' . $u, 'danger');
                        return $this->t->list(['login' => $u]);
                    } else util::log('Account is disabled, contact your System Administrator');
                } else util::log('User does not exist');
            } else util::log('You must provide a valid email address');
        }
        return $this->t->create(['login' => $u]);
    }

    public function list() : string
    {
error_log(__METHOD__);

        $u = $this->in['login'];
        $p = $this->in['webpw'];
        $c = $this->in['remember'];

        if ($u) {
            if ($usr = db::read('id,grp,acl,login,fname,lname,webpw,cookie', 'login', $u, '', 'one')) {
                extract($usr);
                if ($acl !== 9) {
//                    if ($p == 'changeme') { // for testing a clear text password
                    if (password_verify(html_entity_decode($p), $webpw)) {
                        $uniq = md5(uniqid());
                        if ($c) {
                            db::update(['cookie' => $uniq], [['login', '=', $u]]);
                            util::put_cookie('remember', $uniq, 60*60*24*7);
                            $tmp = $uniq;
                        } else $tmp = '';
                        $_SESSION['usr'] = $usr;
                        util::log($login.' is now logged in', 'success');
                        if ((int) $acl === 0) $_SESSION['adm'] = $id;
                        $_SESSION['m'] = 'list';
                        header('Location: ' . $this->g->cfg['self']);
                        exit();
                    } else util::log('Incorrect password');
                } else util::log('Account is disabled, contact your System Administrator');
            } else util::log('Username does not exist');
        }
        return $this->t->list(['login' => $u]);
    }

    public function update() : string
    {
error_log(__METHOD__);

        $i = !is_null($this->in['id']) ? $this->in['id'] : $_SESSION['usr']['id'];
        $u = !empty($this->in['login']) ? $this->in['login'] : $_SESSION['usr']['login'];

        if ($_POST) {
            if ($usr = db::read('login,acl,otpttl', 'id', $i, '', 'one')) {
                $p1 = html_entity_decode($this->in['passwd1']);
                $p2 = html_entity_decode($this->in['passwd2']);
                if (util::chkpw($p1, $p2)) {
                    if (util::is_usr() or ($usr['otpttl'] && (($usr['otpttl'] + 3600) > time()))) {
                        if (!is_null($usr['acl'])) {
                            if (db::update([
                                    'webpw'   => password_hash($p1, PASSWORD_DEFAULT),
                                    'otp'     => '',
                                    'otpttl'  => '',
                                    'updated' => date('Y-m-d H:i:s'),
                                ], [['id', '=', $i]])) {
                                util::log('Password reset for ' . $usr['login'], 'success');
                                if (util::is_usr()) {
                                    header('Location: ' . $this->g->cfg['self']);
                                    exit();
                                } else return $this->t->list(['login' => $usr['login']]);
                            } else util::log('Problem updating database');
                        } else util::log($usr['login'] . ' is not allowed access');
                    } else util::log('Your one time password key has expired');
                }
            } else util::log('User does not exist');
        }
        return $this->t->update(['id' => $i, 'login' => $u]);
    }

    public function delete() : string
    {
error_log(__METHOD__);

        $u = $_SESSION['usr']['login'];
        if (isset($_SESSION['adm']) and $_SESSION['usr']['id'] === $_SESSION['adm'])
            unset($_SESSION['adm']);
        unset($_SESSION['usr']);
        util::del_cookie('remember');
        util::log($u . ' is now logged out', 'success');
        header('Location: ' . $this->g->cfg['self']);
        exit();
    }

    // Utilities

    public function resetpw() : string
    {
error_log(__METHOD__);

        $otp = html_entity_decode($this->in['otp']);
        if (strlen($otp) === 10) {
            if ($usr = db::read('id,acl,login,otp,otpttl', 'otp', $otp, '', 'one')) {
                extract($usr);
                if ($otpttl && (($otpttl + 3600) > time())) {
                    if ($acl != 3) { // suspended
                        return $this->t->update(['id' => $id, 'login' => $login]);
                    } else util::log($login . ' is not allowed access');
                } else util::log('Your one time password key has expired');
            } else util::log('Your one time password key no longer exists');
        } else util::log('Incorrect one time password key');
        header('Location: ' . $this->g->cfg['self']);
        exit();
    }

    private function mail_forgotpw(string $email, string $newpass, string $headers = '') : bool
    {
error_log(__METHOD__);

        $host = $_SERVER['REQUEST_SCHEME'] . '://'
            . $_SERVER['HTTP_HOST']
            . $this->g->cfg['self'];
        return mail(
            "$email",
            'Reset password for ' . $_SERVER['HTTP_HOST'],
'Here is your new OTP (one time password) key that is valid for one hour.

Please click on the link below and continue with reseting your password.

If you did not request this action then please ignore this message.

' . $host . '?o=auth&m=resetpw&otp=' . $newpass,
            $headers
        );
    }
}

?>
