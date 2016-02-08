<?php declare(strict_types = 1);?>
<?php error_log(__FILE__);?>
<?php
// auth.php 20151015 (C) 2015 Mark Constable <markc@renta.net> (AGPL-3.0)

class Auth
{
    const TABLE = 'w_users';

    private $g  = null;
    private $t  = null;
    private $b  = '';
    private $in = [
        'uid'           => '',
        'webpw'         => '',
        'remember'      => '',
        'otp'           => '',
        'passwd1'       => '',
        'passwd2'       => '',
    ];

    public function __construct(View $t)
    {
error_log(__METHOD__);

        $this->t  = $t;
        $this->g  = $t->g;
        db::$tbl  = self::TABLE;
        $this->in = util::esc($this->in);
        $this->b  = $this->{$t->g->in['m']}();
    }

    public function __toString() : string
    {
error_log(__METHOD__);

        return $this->b;
    }

    public function read() : string
    {
error_log(__METHOD__);

        return $this->signin();
    }

    public function signin() : string
    {
error_log(__METHOD__);

        $u = $this->in['uid'];
        $p = $this->in['webpw'];
        $c = $this->in['remember'];

        if ($u) {
            if ($usr = db::read('id,acl,uid,webpw,cookie', 'uid', $u, '', 'one')) {
                if ($usr['acl']) {
//                    if ($p === $usr['webpw']) { // for testing a clear text password
                    if (password_verify(html_entity_decode($p), $usr['webpw'])) {
                        $uniq = md5(uniqid());
                        if ($c) {
                            db::update(['cookie' => $uniq], [['uid', '=', $u]]);
                            util::cookie_put('remember', $uniq, 60*60*24*7);
                            $tmp = $uniq;
                        } else $tmp = '';
                        $_SESSION['usr'] = [$usr['id'], $usr['acl'], $u, $tmp];
                        util::log($usr['uid'].' is now logged in', 'success');
                        if ((int) $usr['acl'] === 1) $_SESSION['adm'] = $usr['id'];
                        header('Location: ' . $_SERVER['PHP_SELF']);
                        exit();
                    } else util::log('Incorrect password');
                } else util::log('Account is disabled, contact your System Administrator');
            } else util::log('Username does not exist');
        }
        return $this->t->auth_signin(['uid' => $u]);
    }

    public static function signout() : string
    {
error_log(__METHOD__);

        $u = $_SESSION['usr'][2];
        if (isset($_SESSION['adm']) and $_SESSION['usr'][0] === $_SESSION['adm'])
            unset($_SESSION['adm']);
        unset($_SESSION['usr']);
        util::cookie_del('remember');
        util::log($u . ' is now logged out', 'success');
        header('Location: '.$_SERVER['PHP_SELF']);
        exit();
    }

    public function forgotpw() : string
    {
error_log(__METHOD__);

        $u = $this->in['uid'];

        if (count($_POST)) {
            if (filter_var($u, FILTER_VALIDATE_EMAIL)) {
                if ($usr = db::read('id,acl', 'uid', $u, '', 'one')) {
                    if ($usr['acl']) {
                        $newpass = util::genpw();
                        if ($this->mail_forgotpw($u, $newpass, $this->g->cfg['email'])) {
                            db::update([
                                'otp' => $newpass,
                                'otpttl' => time()
                            ], [['id', '=', $usr['id']]]);
                            util::log('Sent reset password key for "' . $u . '" so please check your mailbox and click on the supplied link.', 'success');
                        } else util::log('Problem sending message to ' . $u, 'danger');
                        return $this->t->auth_signin(['uid' => $u]);
                    } else util::log('Account is disabled, contact your System Administrator');
                } else util::log('User does not exist');
            } else util::log('You must provide a valid email address');
        }
        return $this->t->auth_forgotpw(['uid' => $u]);
    }

    public function newpw() : string
    {
error_log(__METHOD__);

        $otp = html_entity_decode($this->in['otp']);
        if (strlen($otp) === 10) {
            if ($usr = db::read('id,uid,acl,otp,otpttl', 'otp', $otp, '', 'one')) {
                if ($usr['otpttl'] && (($usr['otpttl'] + 3600) > time())) {
                    if ($usr['acl']) {
                        return $this->t->auth_newpw($usr['id'], $usr['uid']);
                    } else util::log($usr['uid'] . ' is not allowed access');
                } else util::log('Your one time password key has expired');
            } else util::log('Your one time password key no longer exists');
        } else util::log('Incorrect one time password key');
        return $this->t->auth_forgotpw(['uid' => '']);
    }

    public function resetpw() : string
    {
error_log(__METHOD__);

        if (count($_POST)) {
            $id = $this->g->in['i'];
            if ($usr = db::read('uid,acl,otpttl', 'id', $id, '', 'one')) {
                $p1 = html_entity_decode($this->in['passwd1']);
                $p2 = html_entity_decode($this->in['passwd2']);
                if (util::chkpw($p1, $p2)) {
                    if ($usr['otpttl'] && (($usr['otpttl'] + 3600) > time())) {
                        if ($usr['acl']) {
                            if (db::update([
                                    'webpw'   => password_hash($p1, PASSWORD_DEFAULT),
                                    'otp'     => '',
                                    'otpttl'  => '',
                                    'updated' => date('Y-m-d H:i:s'),
                                ], [['id', '=', $id]])) {
                                util::log('Password reset for '.$usr['uid'], 'success');
                                return $this->t->auth_signin(['uid' => $usr['uid']]);
                                return;
                            } else util::log('Problem updating database');
                        } else util::log($usr['uid'] . ' is not allowed access');
                    } else util::log('Your one time password key has expired');
                }
            } else util::log('User does not exist');
        }
        return $this->t->auth_newpw(['id' => $id, 'uid' => $usr['uid']]);
    }

    private function mail_forgotpw(string $email, string $newpass, string $headers = '') : bool
    {
error_log(__METHOD__);

        return mail(
            $email,
            'Reset password for '.$_SERVER['HTTP_HOST'],
'Here is your new one-time password key that is valid for one hour.

Please click on the link below and continue with reseting your password.

If you did not request this action then ignore this email message.

https://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'].'?p=auth&a=newpw&otp='.$newpass,
            $headers
        );
    }
}
