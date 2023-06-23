<?php

declare(strict_types=1);
// lib/php/plugins/auth.php 20150101 - 20230622
// Copyright (C) 2015-2023 Mark Constable <markc@renta.net> (AGPL-3.0)

class Plugins_Auth extends Plugin
{
    public const OTP_LENGTH = 10;
    public const REMEMBER_ME_EXP = 604800; // 7 days;

    protected string $tbl = 'accounts';

    public array $inp = [
        'acl'       => null,
        'grp'       => null,
        'id'        => null,
        'login'     => '',
        'otp'       => '',
        'passwd1'   => '',
        'passwd2'   => '',
        'remember'  => '',
        'webpw'     => '',
    ];

    // alias for forgotpw
    public function create(): string
    {
        $u = $this->inp['login']; // user login email ID

        if (util::is_post()) {
            if (filter_var($u, FILTER_VALIDATE_EMAIL)) {
                if ($usr = db::read('id,acl', 'login', $u, '', 'one')) {
                    if (9 != $usr['acl']) {
                        $newpass = util::genpw(self::OTP_LENGTH);
                        if ($this->mail_forgotpw($u, $newpass, 'From: ' . $this->g->cfg['email'])) {
                            db::update([
                                'otp' => $newpass,
                                'otpttl' => time(),
                            ], [['id', '=', $usr['id']]]);
                            util::log('Sent reset password key for "' . $u . '" so please check your mailbox and click on the supplied link.', 'success');
                        } else {
                            util::log('Problem sending message to ' . $u, 'danger');
                        }
                        util::redirect($this->g->cfg['self'] . '?o=' . $this->g->in['o'] . '&m=list');
                    } else {
                        util::log('Account is disabled, contact your System Administrator');
                    }
                } else {
                    util::log('User does not exist');
                }
            } else {
                util::log('You must provide a valid email address');
            }
        }

        return $this->g->t->create(['login' => $u]);
    }

    // alias for login
    public function list(): string
    {
        $u = $this->inp['login'];
        $p = $this->inp['webpw'];

        if ($u) {
            if ($usr = db::read('id,grp,acl,login,fname,lname,webpw,cookie', 'login', $u, '', 'one')) {
                extract($usr);
                if (9 !== $acl) {
                    if (password_verify(html_entity_decode($p, ENT_QUOTES, 'UTF-8'), $webpw)) {
                        if ($this->inp['remember']) {
                            $uniq = util::random_token(32);
                            db::update(['cookie' => $uniq], [['id', '=', $id]]);
                            util::put_cookie('remember', $uniq, self::REMEMBER_ME_EXP);
                        }
                        $_SESSION['usr'] = $usr;
                        util::log($login . ' is now logged in', 'success');
                        if (0 === (int) $acl) {
                            $_SESSION['adm'] = $id;
                        }
                        $_SESSION['m'] = 'list';
                        util::redirect($this->g->cfg['self']);
                    } else {
                        util::log('Invalid Email Or Password');
                    }
                } else {
                    util::log('Account is disabled, contact your System Administrator');
                }
            } else {
                util::log('Invalid Email Or Password');
            }
        }

        return $this->g->t->list(['login' => $u]);
    }

    // alias for resetpw
    public function update(): string
    {
        if (!(util::is_usr() || isset($_SESSION['resetpw']))) {
            util::log('Session expired! Please login and try again.');
            util::relist();
        }

        $i = (util::is_usr()) ? $_SESSION['usr']['id'] : $_SESSION['resetpw']['usr']['id'];
        $u = (util::is_usr()) ? $_SESSION['usr']['login'] : $_SESSION['resetpw']['usr']['login'];

        if (util::is_post()) {
            if ($usr = db::read('login,acl,otpttl', 'id', $i, '', 'one')) {
                $p1 = html_entity_decode($this->inp['passwd1'], ENT_QUOTES, 'UTF-8');
                $p2 = html_entity_decode($this->inp['passwd2'], ENT_QUOTES, 'UTF-8');
                if (util::chkpw($p1, $p2)) {
                    if (util::is_usr() or ($usr['otpttl'] && (($usr['otpttl'] + 3600) > time()))) {
                        if (!is_null($usr['acl'])) {
                            if (db::update([
                                'webpw' => password_hash($p1, PASSWORD_DEFAULT),
                                'otp' => '',
                                'otpttl' => 0,
                                'updated' => date('Y-m-d H:i:s'),
                            ], [['id', '=', $i]])) {
                                util::log('Password reset for ' . $usr['login'], 'success');
                                if (util::is_usr()) {
                                    util::redirect($this->g->cfg['self']);
                                } else {
                                    unset($_SESSION['resetpw']);
                                    util::relist();
                                }
                            } else {
                                util::log('Problem updating database');
                            }
                        } else {
                            util::log($usr['login'] . ' is not allowed access');
                        }
                    } else {
                        util::log('Your one time password key has expired');
                    }
                }
            } else {
                util::log('User does not exist');
            }
        }

        return $this->g->t->update(['id' => $i, 'login' => $u]);
    }

    public function delete(): ?string
    {
        if (util::is_usr()) {
            $u = $_SESSION['usr']['login'];
            $id = $_SESSION['usr']['id'];
            if (isset($_SESSION['adm']) and $_SESSION['usr']['id'] === $_SESSION['adm']) {
                unset($_SESSION['adm']);
            }
            unset($_SESSION['usr']);
            if (isset($_COOKIE['remember'])) {
                db::update(['cookie' => ''], [['id', '=', $id]]);
                $this->setcookie('remember', '', strtotime('-1 hour', 0));
            }
            util::log($u . ' is now logged out', 'success');
        }
        util::redirect($this->g->cfg['self']);
        return ''; // unused, just to satisfy ?string
    }

    // Utilities
    public function resetpw(): ?string
    {
        $otp = html_entity_decode($this->inp['otp']);
        if (self::OTP_LENGTH === strlen($otp)) {
            if ($usr = db::read('id,acl,login,otp,otpttl', 'otp', $otp, '', 'one')) {
                extract($usr);
                if ($otpttl && (($otpttl + 3600) > time())) {
                    if (3 != $acl) { // suspended
                        $_SESSION['resetpw'] = ['usr' => $usr];

                        return $this->g->t->update(['id' => $id, 'login' => $login]);
                    }
                    util::log($login . ' is not allowed access');
                } else {
                    util::log('Your one time password key has expired');
                }
            } else {
                util::log('Your one time password key no longer exists');
            }
        } else {
            util::log('Incorrect one time password key');
        }
        util::redirect($this->g->cfg['self']);
    }

    private function mail_forgotpw(string $email, string $newpass, string $headers = ''): bool
    {
        $host = $_SERVER['REQUEST_SCHEME'] . '://'
            . $this->g->cfg['host']
            . $this->g->cfg['self'];

        return mail(
            "{$email}",
            'Reset password for ' . $this->g->cfg['host'],
            'Here is your new OTP (one time password) key that is valid for one hour.

Please click on the link below and continue with reseting your password.

If you did not request this action then please ignore this message.

' . $host . '?o=auth&m=resetpw&otp=' . $newpass,
            $headers
        );
    }
}
