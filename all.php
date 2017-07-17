<?php declare(strict_types = 1);

// Copyright (C) 2015-2017 Mark Constable <markc@renta.net> (AGPL-3.0)

// lib/php/util.php 20150225 - 20170306

class Util
{
    public static function log(string $msg = '', string $lvl = 'danger') : array
    {
        if ($msg) {
            if (isset($_SESSION['l']) and $_SESSION['l']) {
                list(, $m) = explode(':', $_SESSION['l'], 2);
                $msg = $m . '<br>' . $msg;
            }
            $_SESSION['l'] = $lvl . ':' . $msg;
        } elseif (isset($_SESSION['l']) and $_SESSION['l']) {
            $l = $_SESSION['l']; $_SESSION['l'] = '';
            return explode(':', $l, 2);
        }
        return ['', ''];
    }

    public static function esc(array $in) : array
    {
        foreach ($in as $k => $v)
            $in[$k] = isset($_REQUEST[$k])
                ? htmlentities(trim($_REQUEST[$k]), ENT_QUOTES, 'UTF-8') : $v;
        return $in;
    }

    public static function ses(string $k, string $v = '', string $x = null) : string
    {
        return $_SESSION[$k] =
            (!is_null($x) && (!isset($_SESSION[$k]) || ($_SESSION[$k] != $x))) ? $x :
                (((isset($_REQUEST[$k]) && !isset($_SESSION[$k]))
                    || (isset($_REQUEST[$k]) && isset($_SESSION[$k])
                    && ($_REQUEST[$k] != $_SESSION[$k])))
                ? htmlentities(trim($_REQUEST[$k]), ENT_QUOTES, 'UTF-8')
                : ($_SESSION[$k] ?? $v));
    }

//    public static function cfg($g) : void  // php7.1 only
    public static function cfg($g)
    {
        if (file_exists($g->cfg['file'])) {
            foreach(include $g->cfg['file'] as $k => $v) {
               $g->$k = array_merge($g->$k, $v);
            }
        }
    }

    public static function now($date1, $date2 = null)
    {
        if (!is_numeric($date1)) $date1 = strtotime($date1);
        if ($date2 and !is_numeric($date2)) $date2 = strtotime($date2);
        $date2 = $date2 ?? time();
        $diff = abs($date1 - $date2);
        if ($diff < 10) return ' just now';

        $blocks = [
            ['k' => 'year', 'v' => 31536000],
            ['k' => 'month','v' => 2678400],
            ['k' => 'week', 'v' => 604800],
            ['k' => 'day',  'v' => 86400],
            ['k' => 'hour', 'v' => 3600],
            ['k' => 'min',  'v' => 60],
            ['k' => 'sec',  'v' => 1],
        ];
        $levels = 2;
        $current_level = 1;
        $result = [];

        foreach ($blocks as $block) {
            if ($current_level > $levels) {
                break;
            }
            if ($diff / $block['v'] >= 1) {
                $amount = floor($diff / $block['v']);
                $plural = ($amount > 1) ? 's' : '';
                $result[] = $amount . ' ' . $block['k'] . $plural;
                $diff -= $amount * $block['v'];
                ++$current_level;
            }
        }
        return implode(' ', $result) . ' ago';
    }

    public static function pager(int $curr, int $perp, int $total) : array
    {
        $start = ($curr - 1) * $perp;
        $start = $start < 0 ? 0 : $start;
        $last  = intval(ceil($total / $perp));
        $curr  = $curr < 1 ? 1 : ($curr > $last ? $last : $curr);
        $prev  = $curr < 2 ? 1 : $curr - 1;
        $next  = $curr > ($last - 1) ? $last : $curr + 1;

        return [
            'start' => $start,
            'prev'  => $prev,
            'curr'  => $curr,
            'next'  => $next,
            'last'  => $last,
            'perp'  => $perp,
            'total' => $total
        ];
    }

    public static function is_adm() : bool
    {
        return isset($_SESSION['adm']);
    }

    public static function is_usr(int $id = null) : bool
    {
        return (is_null($id))
            ? isset($_SESSION['usr'])
            : isset($_SESSION['usr']['id']) && $_SESSION['usr']['id'] == $id;
    }

    public static function is_acl(int $acl) : bool
    {
        return isset($_SESSION['usr']['acl']) && $_SESSION['usr']['acl'] == $acl;
    }

    // 09-Auth

    public static function genpw()
    {
        return str_replace('.', '_',
            substr(password_hash((string)time(), PASSWORD_DEFAULT),
                rand(10, 50), 10));
    }

    public static function get_nav(array $nav = []) : array
    {
        return isset($_SESSION['usr'])
            ? (isset($_SESSION['adm']) ? $nav['adm'] : $nav['usr'])
            : $nav['non'];
    }

    public static function get_cookie(string $name, string $default='') : string
    {
        return $_COOKIE[$name] ?? $default;
    }

    public static function put_cookie(string $name, string $value, int $expiry=604800) : string
    {
        return setcookie($name, $value, time() + $expiry, '/') ? $value : '';
    }

    public static function del_cookie(string $name) : string
    {
        return self::put_cookie($name, '', time() - 1);
    }

    public static function chkpw($pw, $pw2)
    {
        if (strlen($pw) > 9) {
            if (preg_match('/[0-9]+/', $pw)) {
                if (preg_match('/[A-Z]+/', $pw)) {
                    if (preg_match('/[a-z]+/', $pw)) {
                        if ($pw === $pw2) {
                            return true;
                        } else util::log('Passwords do not match, please try again');
                    } else util::log('Password must contains at least one lower case letter');
                } else util::log('Password must contains at least one captital letter');
            } else util::log('Password must contains at least one number');
        } else util::log('Passwords must be at least 10 characters');
        return false;
    }

    public static function remember($g)
    {
        if (!self::is_usr()) {
            if ($c = self::get_cookie('remember')) {
                if (is_null(db::$dbh)) db::$dbh = new db($g->db);
                db::$tbl = 'accounts';
                if ($usr = db::read('id,grp,acl,login,fname,lname,cookie', 'cookie', $c, '', 'one')) {
                    extract($usr);
                    $_SESSION['usr'] = $usr;
                    if ($acl == 0) $_SESSION['adm'] = $id;
                    self::log($login . ' is remembered and logged back in', 'success');
                    self::ses('o', '', $g->in['o']);
                    self::ses('m', '', $g->in['m']);
                }
            }
        }
    }

    // shell utilities

    public static function redirect(string $url, int $ttl = 5, string $msg = '')
    {
            header('refresh:' . $ttl . '; url=' . $url);
            echo '<!DOCTYPE html>
<title>Redirect...</title>
<h2 style="text-align:center">Redirecting in ' . $ttl . ' seconds...</h2>
<pre style="width:50em;margin:0 auto;">' . $msg . '</pre>';
    }

    public static function getcfg()
    {
        $ary = $cfg = [];
        $str = shell_exec("sudo rootcat ~/.sh/lib/defaults");
        $ary = explode("\n", $str);
        foreach($ary as $line) {
            if (empty($line)) continue;
            list($k, $v) = explode('=', $line);
            $cfg[$k] = trim($v, "'");
        }
        return $cfg;
    }

    // mail utilities

    public static function numfmt(float $size, int $precision = 2) : string
    {
        if ($size == 0) return "0";
        $base = log($size, 1024);
        $suffixes = [' Bytes', ' KiB', ' MiB', ' GiB', ' TiB'];
        return round(pow(1024, $base - floor($base)), $precision) . $suffixes[floor($base)];
    }

    public static function is_valid_domain_name($domainname)
    {
        $domainname = idn_to_ascii($domainname);
        return (preg_match("/^([a-z\d](-*[a-z\d])*)(\.([a-z\d](-*[a-z\d])*))*$/i", $domainname)
              && preg_match("/^.{1,253}$/", $domainname)
              && preg_match("/^[^\.]{1,63}(\.[^\.]{1,63})*$/", $domainname));
    }

    public static function mail_password($pw, $hash = 'SHA512-CRYPT')
    {
        $salt_str = bin2hex(openssl_random_pseudo_bytes(8));
        return $hash === 'SHA512-CRYPT'
            ? '{SHA512-CRYPT}' . crypt($pw, '$6$' . $salt_str . '$')
            : '{SSHA256}' . base64_encode(hash('sha256', $pw . $salt_str, true) . $salt_str);
    }

}

// lib/php/plugins/users.php 20150101 - 20170306

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
        if (util::is_adm() and !is_null($this->g->in['i'])) {
            $_SESSION['usr'] = db::read('id,acl,grp,login,fname,lname,webpw,cookie', 'id', $this->g->in['i'], '', 'one');
            util::log('Switch to user: ' . $_SESSION['usr']['login'], 'success');
        } else util::log('Not authorized to switch users');
        return $this->list();
    }
}

// plugins/mail/infosys.php 20170225 - 20170513

class Plugins_InfoSys extends Plugin
{
    public function list() : string
    {
        $mem = $dif = $cpu = [];
        $cpu_name = $procs = '';
        $cpu_num = 0;
        $os  = 'Unknown OS';

        $pmi = explode("\n", trim(file_get_contents('/proc/meminfo')));
        $upt = (int) (file_get_contents('/proc/uptime') / 60);
        $lav = join(', ', sys_getloadavg());
        $stat1 = file('/proc/stat');
        sleep(1);
        $stat2 = file('/proc/stat');

        if (is_readable('/proc/cpuinfo')) {
            $tmp = trim(file_get_contents('/proc/cpuinfo'));
            $ret = preg_match_all('/model name.+/', $tmp, $matches);
            $cpu_name = $ret ? explode(': ', $matches[0][0])[1] : 'Unknown CPU';
            $cpu_num = count($matches[0]);
        }

        if (is_readable('/etc/os-release')) {
            $tmp = explode("\n", trim(file_get_contents('/etc/os-release')));
            $osr = [];
            foreach ($tmp as $line) {
                list($k, $v) = explode('=', $line);
                $osr[$k] = trim($v, '" ');
            }
            $os = $osr['PRETTY_NAME'] ?? 'Unknown OS';
        }

        foreach ($pmi as $line) {
            list($k, $v) = explode(':', $line);
            list($mem[$k],) = explode(' ', trim($v));
        }

        $info1 = explode(" ", preg_replace("!cpu +!", "", $stat1[0]));
        $info2 = explode(" ", preg_replace("!cpu +!", "", $stat2[0]));
        $dif['user'] = $info2[0] - $info1[0];
        $dif['nice'] = $info2[1] - $info1[1];
        $dif['sys']  = $info2[2] - $info1[2];
        $dif['idle'] = $info2[3] - $info1[3];
        $total = array_sum($dif);
        foreach($dif as $x=>$y) $cpu[$x] = round($y / $total * 100, 2);
        $cpu_all = sprintf("User: %01.2f, System: %01.2f, Nice: %01.2f, Idle: %01.2f", $cpu['user'], $cpu['sys'], $cpu['nice'], $cpu['idle']);
        $cpu_pcnt = intval(round(100 - $cpu['idle']));

        $dt  = (float) disk_total_space('/');
        $df  = (float) disk_free_space('/');
        $du  = (float) $dt - $df;
        $dp  = floor(($du / $dt) * 100);

        $mt  = (float) $mem['MemTotal'] * 1024;
        $mf  = (float) ($mem['MemFree'] + $mem['Cached']) * 1024;
        $mu  = (float) $mt - $mf;
        $mp  = floor(($mu / $mt) * 100);

        $min = $upt % 60; $upt = (int) ($upt / 60);
        $hrs = $upt % 24; $upt = (int) ($upt / 24);
        $day = $upt;

        $hn  = is_readable('/proc/sys/kernel/hostname')
            ? trim(file_get_contents('/proc/sys/kernel/hostname'))
            : 'Unknown';
        $ip  = gethostbyname($hn);
        $knl = is_readable('/proc/version')
            ? explode(' ', trim(file_get_contents('/proc/version')))[2]
            : 'Unknown';
        $procs = shell_exec('sudo processes');

        $day_str = $day < 1 ? '' : $day . ($day === 1 ? ' day ' : ' days ');
        $hrs_str = $hrs < 1 ? '' : $hrs . ($hrs === 1 ? ' hour ' : ' hours ');
        $min_str = $min < 1 ? '' : $min . ($min === 1 ? ' minute ' : ' minutes ');

        return $this->t->list([
            'dsk_color' => $dp > 90 ? 'danger' : ($dp > 80 ? 'warning' : 'default'),
            'dsk_free'  => util::numfmt($df),
            'dsk_pcnt'  => $dp,
            'dsk_text'  => $dp > 5 ? $dp. '%' : '',
            'dsk_total' => util::numfmt($dt),
            'dsk_used'  => util::numfmt($du),
            'mem_color' => $mp > 90 ? 'danger' : ($mp > 80 ? 'warning' : 'default'),
            'mem_free'  => util::numfmt($mf),
            'mem_pcnt'  => $mp,
            'mem_text'  => $mp > 5 ? $mp . '%' : '',
            'mem_total' => util::numfmt($mt),
            'mem_used'  => util::numfmt($mu),
            'os_name'   => $os,
            'uptime'    => $day_str . $hrs_str . $min_str,
            'loadav'    => $lav,
            'hostname'  => $hn,
            'host_ip'   => $ip,
            'kernel'    => $knl,
            'cpu_all'   => $cpu_all,
            'cpu_name'  => $cpu_name,
            'cpu_num'   => $cpu_num,
            'cpu_color' => $cpu_pcnt > 90 ? 'danger' : ($cpu_pcnt > 80 ? 'warning' : 'default'),
            'cpu_pcnt'  => $cpu_pcnt,
            'cpu_text'  => $cpu_pcnt > 5 ? $cpu_pcnt. '%' : '',
            'proc_list' => $procs,
            'proc_num'  => count(explode("\n", $procs)) - 1,
        ]);
    }
}

// lib/php/plugins/auth.php 20150101 - 20170307

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

// lib/php/plugins/mailgraph.php 20170225 - 20170514

class Plugins_MailGraph extends Plugin
{
    public function list() : string
    {
        $return = '';
        $images = ['0-n', '1-n', '2-n', '3-n'];
        foreach ($images as $image) {
            $image = 'http://localhost:81/mailgraph.cgi?' . $image;
            $headers = get_headers($image);
            $return_code = substr($headers[0], 9, 3);
            if ($return_code >= 400) {
                $return .= '<img class="img-responsive" alt="not-yet-available" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACAAAAAgCAYAAABzenr0AAAG3UlEQVRYR8WXC2yT1xXH/+dcx0loRkMhULZuA2WxAwjStVsYiZ06IYUOClRbVaGJ0m0MCaEpqOs00anVgErtNq1ZH5PWVRUIOtZ2LevGUOkjbydMbKM8x+wQKKWsCYSUFvLAj++c6fvAxo4NDtKkWbIsfd+55/+79/7PudeE//OHblR/A8A11TNnwIiXVSYqMZPggsA6MTQy/vCSffuGbyTnmAFafZ56JXxfhb7JjOJsIgSJQrhTmV4eKYq/smh3TyQXTE6A5pryKkX8WaPma4lklkivIToiRGcYahF0ggJeAXsYcHI6MYYfD3SENxOg1wK5JkBrIODSWO+Tyvjx5aQyAOIXGLL9ro5j/24NzCpikanCljGWDPiDPeferSotyTeubwP0Q4XOtEUJeJdhragJ9vRnX7UsT1sD0wog+X+EYokCQkAjjGujZUW+ZGBWiyWL2XBZ6lAB+gnaBPDW2mDovXaf9yGL9GkGTQBwnAl339UR/mC0XMYK2Cbz+707GLgPis8Avd+QHIzBNDKwIteeOrNW/J3YWhOP531ijOxUYI4KPpCCvHn1zUfOpObIAGitKd8A1Z8RcMEC17oopgLzVyi+MBbxRIxjSOU16oq8CSu/DUCFQto6gsfmbwDkalxK1hafp4KU9gmDifReUTptFB0g3Hwj4mmxhB9ITN8G0wFmTCJQQyAYej4rQJvf+44CC4j0xZg7/hMzkrefGNNTE06uX4zB7qMYPpW+naagEJ9fthynX98KleQEISJxMFUzYTqUXhXo+XyhL/u6whevmPRy+ia/d44BDhIwzLCmCZnHVNGQKn7rPfeh/NGnEP3kHA6sewjDp044r23x2b94AcVfnYu+t/6E8C8fS4Mg4NDZW8N3TO717lFCJYCHa4PhZ9IAWn2eX4HoERG8xHmux8mKfqhgdwJgysJlmPHTnwN02TYORMNKRM72JsUTsVkhFA8oqQHoFRHaP78rdEcaQJu//F927Sq0HooKIno6kXDCnfNQ0bg5KZ54Hj0/gJGPTuLmOXdmWOTU9hdx4neNyecKvF0QH/pWhG86D0Z+1IpPWbjn+FlnOnZTgRW/CIGVL0Ofi5jCN0G8MDGa89yYtelZTKyuHZMXL/X9x1kd+zfxEcGlyHiruGDQNBHgg9KS2s7QLgfAcT/RAbth1AbDX2mqLvvYME9NVRsrxGXxB3Gp7+NMWMOz1dIGgq5O+MABaK4pq2HldruBBDrDc5ury2LM7BqdwYF44jlMrApkXYnrisNpqfNJtZ5AjxJoUyAYsvvNVQAA/6gNhiub/WVRBueNVkl1ezaC6EA/DqxbmVGiiVhi1KlFC0C6HkQbaztCGxyA9pry2aJ6SEROzu86Nr3Z5/2ICbeliuQSTxrzSnUkSjRtG0lmivAjIKxSxbq6zvBzDsCeebcVjrhuGrKPzVhh/nj3cPQ1kC5ODB6r+PUg7P5SNFxUfHHcYDuAeSS6KNDVvTt5FrT5vQftQ8N+ocylgCbbZfHtX0dF4xaQK90WttGGPzyOW+b6M3bk5JbfwP4mlx+y0yW8IsIyALBL3VRS3xwaSAK0+D1P2uZQ4GVx08OuqJ5SYFwiwSR/vVOKZIzzKFFq9r6PLtHTb2xDz/NPAXr1HkJKS5UwEdAtAO2tDYa+YedJArQGPOVi0VEDianRUrV4rQ2UOrVJNXdj1sZnEOnvSyu11BLNJm4LBoKhqna/d7+zysDaQDD82zQApyFVe/8MxjJS/OHiSNHqcYWD/2TCjFQIuwSHTnRn1LkNMWXBUvS+tSNt5nYDchmqFGglFC8RcNYdH5pW9bfTIxkATb5yj4usw84ZQLqcYd5XlU4FJo+pBY4KEkCJsJzYvE+WtU+B8apYVdcZ3nzVG6MGtfg8P7LPAQFGQHKPiukjsXaNvoLlAhKRQUO0Mp7v3kMjkaA9XqC76oLdS1MvqVkvpS1+7zYCHrQhGFiZJ3gnRvKEENZma1AZMITdYNNgWVG3gdkJoJRAR43m+fydh8+nxmcFsG/EFO/dqoTv2MEKbIlZ8fVuN7kh5rsWdDFZdDszCq68F1V0E+l7JLSt/1PXoZKJVgOpbrQrSVSOWMoLF3SFMw6Ia17LFaB2f/l6S6xN9rlgNxIBtgLYTmbq3v6SNi3p90ywjNtE3Zc+tf+EtFR7vUS4X4A1iU4qkDciUV21aG/PhWzblvOPiX1Tcgl+rYy6ZAKRITCFCHQGkLiAb2HAk2pWgfYQeH1dMLTjen7JCZAY3Oz3VDLoe6K4d/Q5keLoC0rUrMDvz00J/eWB12HlMuuYAVITtdeUftFS4yHCJAUbqPUZGTreX9J9bCyiOU2Yi/p/+f6/yPUmTii6UZAAAAAASUVORK5CYII=" /><p>Not enough data</p>';
            } else {
                $imageData = base64_encode(file_get_contents($image));
                $return .= '<img class="img-responsive" alt="' . $image . '" src="data:image/png;base64,' . $imageData . '" />';
            }
        }
        return $this->t->list(['mailgraph' => $return]);
    }
}

// lib/php/plugins/valias.php 20170225 - 20170704

class Plugins_Valias extends Plugin
{
    protected
    $tbl = 'valias',
    $in = [
        'aid'    => 1,
        'did'    => 1,
        'source' => '',
        'target' => '',
        'active' => 0,
    ];

// TODO recfactor common parts of create() and update() into private methods
// yep, as of 20170704 this is still a medium to high priority TODO

    protected function create() : string
    {
        if ($_POST) {
            extract($this->in);
            $active = $active ? 1 : 0;
            $sources = array_map('trim', preg_split("/( |,|;|\n)/", $source));
            $targets = array_map('trim', preg_split("/( |,|;|\n)/", $target));

            if (empty($source[0])) {
                util::log('Alias source address is empty');
                $_POST = []; return $this->t->create($this->in);
            }

            if (empty($targets[0])) {
                util::log('Alias target address is empty');
                $_POST = []; return $this->t->create($this->in);
            }

            foreach ($sources as $s) {
                if (empty($s)) continue;
                $lhs = ''; $rhs = '';
                if (strpos($s, '@') !== false)
                    list($lhs, $rhs) = explode('@', $s);
                else $rhs = $s;

                if (!$domain = idn_to_ascii($rhs)) {
                    util::log('Invalid source domain: ' . $rhs);
                    $_POST = []; return $this->t->create($this->in);
                }

                $sql = "
 SELECT `id` FROM `vhosts`
  WHERE `domain` = :domain";

                $did = db::qry($sql, ['domain' => $domain], 'col');

                if (!$did) {
                    util::log($domain . ' does not exist as a local domain');
                    $_POST = []; return $this->t->create($this->in);
                }

                if ((!filter_var($s, FILTER_VALIDATE_EMAIL)) && !empty($lhs)) {
                    util::log('Alias source address is invalid');
                    $_POST = []; return $this->t->create($this->in);
                }

                $sql = "
 SELECT 1 FROM `valias`
  WHERE `source` = :catchall";

                $catchall = db::qry($sql, ['catchall' => '@'.$domain], 'col');
//error_log("catchall=$catchall");

                if ($catchall !== 1) {
                    $sql = "
 SELECT `source` FROM `valias`
  WHERE `source` = :source";

                    $num_results = count(db::qry($sql, ['source' => $s]));

                    if ($num_results) {
                        util::log($s . ' already exists as an alias');
                        $_POST = []; return $this->t->create($this->in);
                    }
                }

                $sql = "
 SELECT `user` FROM `vmails`
  WHERE `user` = :source";

                $num_results = count(db::qry($sql, ['source' => $s]));

                if ($num_results) {
                    util::log($s . ' already exists as a regular mailbox');
                    $_POST = []; return $this->t->create($this->in);
                }

                foreach ($targets as $t) {
                    if (empty($t)) continue;
                    list($tlhs, $trhs) = explode('@', $t);

                    if (!$tdomain = idn_to_ascii($trhs)) {
                        util::log('Invalid target domain: ' . $tdomain);
                        $_POST = []; return $this->t->create($this->in);
                    }

                    if (!filter_var($t, FILTER_VALIDATE_EMAIL)) {
                        util::log('Alias target address is invalid');
                        $_POST = []; return $this->t->create($this->in);
                    }

                    if ($catchall !== 1) {
                        if ($t === $s) {
                            util::log('Alias source and target addresses must not be the same');
                            $_POST = []; return $this->t->create($this->in);
                        }
                    }
                }

                $target  = implode(',', $targets);

                $sql = "
 INSERT INTO `valias` (
        `active`,
        `did`,
        `source`,
        `target`,
        `updated`,
        `created`
) VALUES (
        :active,
        :did,
        :source,
        :target,
        :updated,
        :created
)";
                $s = filter_var($s, FILTER_VALIDATE_EMAIL)
                    ? $s
                    : '@' . $domain;

                $result = db::qry($sql, [
                    'active'  => $active ? 1 : 0,
                    'did'     => $did,
                    'source'  => $s,
                    'target'  => $target,
                    'updated' => date('Y-m-d H:i:s'),
                    'created' => date('Y-m-d H:i:s')
                ]);
                // test $result?
            }
            util::log('Alias added', 'success');
            util::ses('p', '', '1');
            return $this->list();
        } else return $this->t->create($this->in);
    }

    protected function read() : string
    {
        return $this->t->update(db::read('*', 'id', $this->g->in['i'], '', 'one'));
    }

    protected function update() : string
    {
        if ($_POST) {
            extract($this->in);
            $active = $active ? 1 : 0;
            $sources = array_map('trim', preg_split("/( |,|;|\n)/", $source));
            $targets = array_map('trim', preg_split("/( |,|;|\n)/", $target));

            if (empty($source[0])) {
                util::log('Alias source address is empty');
                $_POST = []; return $this->read();
            }

            if (empty($targets[0])) {
                util::log('Alias target address is empty');
                $_POST = []; return $this->read();
            }

            foreach ($sources as $s) {
                if (empty($s)) continue;
                $lhs = ''; $rhs = '';
                if (strpos($s, '@') !== false)
                    list($lhs, $rhs) = explode('@', $s);
                else $rhs = $s;

                if (!$domain = idn_to_ascii($rhs)) {
                    util::log('Invalid source domain: ' . $rhs);
                    $_POST = []; return $this->read();
                }

                $sql = "
 SELECT `id` FROM `vhosts`
  WHERE `domain` = :domain";

                $did = db::qry($sql, ['domain' => $domain], 'col');

                if (!$did) {
                    util::log($domain . ' does not exist as a local domain');
                    $_POST = []; return $this->read();
                }

                if ((!filter_var($s, FILTER_VALIDATE_EMAIL)) && !empty($lhs)) {
                    util::log('Alias source address is invalid');
                    $_POST = []; return $this->read();
                }

                $sql = "
 SELECT 1 FROM `valias`
  WHERE `source` = :catchall";

                $catchall = db::qry($sql, ['catchall' => '@'.$domain], 'col');
//error_log("catchall=$catchall");

                if ($catchall !== 1) {
                    $sql = "
 SELECT `user` FROM `vmails`
  WHERE `user` = :source";

                    $num_results = count(db::qry($sql, ['source' => $s]));

                    if ($num_results) {
                        util::log($s . ' already exists as a regular mailbox');
                        $_POST = []; return $this->read();
                    }
                }

                foreach ($targets as $t) {
                    if (empty($t)) continue;
                    list($tlhs, $trhs) = explode('@', $t);

                    if (!$tdomain = idn_to_ascii($trhs)) {
                        util::log('Invalid target domain: ' . $tdomain);
                        $_POST = []; return $this->read();
                    }

                    if (!filter_var($t, FILTER_VALIDATE_EMAIL)) {
                        util::log('Alias target address is invalid');
                        $_POST = []; return $this->read();
                    }

                    if ($catchall !== 1) {
                        if ($t === $s) {
                            util::log('Alias source and target addresses must not be the same');
                            $_POST = []; return $this->read();
                        }
                    }
                }

                $target  = implode(',', $targets);
                $s = filter_var($s, FILTER_VALIDATE_EMAIL)
                    ? $s
                    : '@' . $domain;

                $sql = "
 SELECT `source` FROM `valias`
  WHERE `source` = :source";

                $exists = count(db::qry($sql, ['source' => $s]));

                if ($exists or (count($sources) == 1)) {
                    $sql = "
 UPDATE `valias` SET
        `active`  = :active,
        `source`  = :source,
        `target`  = :target,
        `updated` = :updated
  WHERE `id` = :id";

                    $result = db::qry($sql, [
                        'id'      => $this->g->in['i'],
                        'active'  => $active,
                        'source'  => $s,
                        'target'  => $target,
                        'updated' => date('Y-m-d H:i:s'),
                    ]);
                } else {
                    $sql = "
 INSERT INTO `valias` (
        `active`,
        `did`,
        `source`,
        `target`,
        `updated`,
        `created`
) VALUES (
        :active,
        :did,
        :source,
        :target,
        :updated,
        :created
)";
                    $result = db::qry($sql, [
                        'active'  => $active ? 1 : 0,
                        'did'     => $did,
                        'source'  => $s,
                        'target'  => $target,
                        'updated' => date('Y-m-d H:i:s'),
                        'created' => date('Y-m-d H:i:s')
                    ]);
                }
            }
            util::log('Changes to alias have been saved', 'success');
            util::ses('p', '', '1');
            return $this->list();
        } elseif ($this->g->in['i']) {
            return $this->read();
        } else return 'Error updating item';
    }
}

// lib/php/plugins/news.php 20150101 - 20170317

class Plugins_News extends Plugin
{
    protected
    $tbl = 'news',
    $in = [
        'title'     => '',
        'author'    => 1,
        'content'   => '',
    ];

    protected function read() : string
    {
        $sql = "
 SELECT n.*, u.id as uid, u.login, u.fname, u.lname
   FROM `news` n
        JOIN `accounts` u
            ON n.author=u.id
  WHERE n.id=:nid";

        return $this->t->read(db::qry($sql, ['nid' => $this->g->in['i']], 'one'));
    }

    protected function update() : string
    {
        if ($_POST) return parent::update();

        $sql = "
 SELECT n.*, u.id as uid, u.login, u.fname, u.lname
   FROM `news` n
        JOIN `accounts` u
            ON n.author=u.id
  WHERE n.id=:nid";

        return $this->t->update(db::qry($sql, ['nid' => $this->g->in['i']], 'one'));
    }

    protected function delete() : string
    {
        if (!util::is_adm()) {
            $author = db::read('author', 'id', $this->g->in['i'], '', 'col');
            if ($_SESSION['usr']['id'] !== $author) {
                util::log('You do not have permissions to delete this post');
                return $this->list();
            }
        }

        return parent::delete();
    }

    protected function list() : string
    {
        $pager = util::pager(
            (int) util::ses('p'),
            (int) $this->g->cfg['perp'],
            (int) db::qry("SELECT count(*) FROM `news` n JOIN `accounts` u ON n.author=u.id", [], 'col')
        );

        $sql = "
 SELECT n.*, u.id as uid, u.login, u.fname, u.lname
   FROM `news` n
        JOIN `accounts` u
            ON n.author=u.id
  ORDER BY n.updated DESC LIMIT " . $pager['start'] . "," . $pager['perp'];

        return $this->t->list(array_merge(db::qry($sql), ['pager' => $pager]));
    }
}

// lib/php/plugins/records.php 20150101 - 20170423

class Plugins_Records extends Plugin
{
    protected
    $tbl = 'records',
    $in = [
        'auth'        => 1, // ?
        'change_date' => '', // ?
        'content'     => '',
        'disabled'    => 0, // ?
        'domain_id'   => null,
        'name'        => '',
        'ordername'   => '', // ?
        'prio'        => 0,
        'ttl'         => 300,
        'type'        => '',
    ];

    public function __construct(Theme $t)
    {
        if ($t->g->dns['db']['type'])
            $this->dbh = new db($t->g->dns['db']);
        parent::__construct($t);
    }

    protected function create() : string
    {
        if ($_POST) {
            $sql = "
 SELECT name FROM domains
  WHERE id = :did";

            $domain = db::qry($sql, ['did' => $this->in['domain_id']], 'col');
            $this->in['updated'] = date('Y-m-d H:i:s');
            $this->in['created'] = date('Y-m-d H:i:s');
            $this->in['name'] = $this->in['name']
                ? $this->in['name'] . '.' . $domain
                : $domain;
            $lid = db::create($this->in);
            util::log('Created DNS record ID: ' . $lid, 'success');
            $this->g->in['i'] = $this->in['domain_id'];
            return $this->list();
        }
        return $this->t->create($this->in);
    }

    protected function update() : string
    {
        if ($_POST) {
            $this->in['disabled'] = isset($_POST['active']) && $_POST['active'] ? 0 : 1;
            $res = db::update($this->in, [['id', '=', $this->g->in['i']]]);
            // TODO check $res ???
            util::log('Updated DNS record ID: ' . $this->g->in['i'], 'success');
            $this->g->in['i'] = $this->in['domain_id'];
            return $this->list();
        } elseif ($this->g->in['i']) {
            return $this->list();
        }
        return 'Error updating DNS record';
    }

    protected function delete() : string
    {
        if ($this->g->in['i']) {
            $res = db::delete([['id', '=', $this->g->in['i']]]);
            // TODO check $res ???
            util::log('Deleted DNS record ID: ' . $this->g->in['i'], 'success');
            util::ses('p', '', '1');
            $this->g->in['i'] = $this->in['domain_id'];
            return $this->list();
        }
        return 'Error deleting item';
    }

    protected function list() : string
    {
          $sql = "
 SELECT name FROM domains
  WHERE id = :did";

        $domain = db::qry($sql, ['did' => $this->g->in['i']], 'col');

        $sql = "
 SELECT id,name,type,content,ttl,disabled,prio AS priority
   FROM records
  WHERE (name LIKE '' OR 1) AND
        (content LIKE '' OR 1) AND
        (domain_id = :did) AND
        (type != 'SOA')";

        return $this->t->update(array_merge(
            ['domain' => $domain],
            ['domain_id' => $this->g->in['i']],
            db::qry($sql, ['did' => $this->g->in['i']])
        ));
    }
}

// lib/php/plugins/vmails.php 20170228

class Plugins_Vmails extends Plugin
{
    protected
    $tbl = 'vmails',
    $in = [
        'active'    => 0,
        'aid'       => 1,
        'did'       => 1,
        'gid'       => 1000,
        'home'      => '',
        'quota'     => 1073741824,
        'passwd1'   => '',
        'passwd2'   => '',
        'password'  => '',
        'uid'       => 1000,
        'user'      => '',
    ];

    function create() : string
    {
        if ($_POST) {
            $this->in['quota'] *= 1048576;
            extract($this->in);
            $password = 'changeme_N0W';
            list($lhs, $rhs) = explode('@', $user);

            if (!filter_var($user, FILTER_VALIDATE_EMAIL)) {
                util::log('Invalid email address');
                $_POST = []; return $this->t->create($this->in);
            }

            $sql = "
 SELECT `id`, `aid`, `uid`, `gid` FROM `vhosts`
  WHERE `domain` = :rhs";

            $d = db::qry($sql, ['rhs' => $rhs], 'one');

            if (empty($d['id'])) {
                util::log('Domain does not exist');
                $_POST = []; return $this->t->create($this->in);
            }

            if ($passwd1 && $passwd2) {
                if (!util::chkpw($passwd1, $passwd2)) {
                    $_POST = []; return $this->t->create($this->in);
                }
                $password = $passwd1;
            }
            $sql = "
 SELECT 1 FROM `valias`
  WHERE `source` = :catchall";

            $catchall = db::qry($sql, ['catchall' => '@'.$rhs], 'col');
//error_log("catchall=$catchall");

            if ($catchall === 1) {
                $sql = "
 INSERT INTO `valias` (
        `active`,
        `did`,
        `source`,
        `target`,
        `updated`,
        `created`
) VALUES (
        :active,
        :did,
        :source,
        :target,
        :updated,
        :created
)";
                $result = db::qry($sql, [
                    'active'  => $active ? 1 : 0,
                    'did'     => $d['id'],
                    'source'  => $user,
                    'target'  => $user,
                    'updated' => date('Y-m-d H:i:s'),
                    'created' => date('Y-m-d H:i:s')
                ]);
            }

            $sql = "
 INSERT INTO `vmails` (
        aid,
        created,
        did,
        gid,
        home,
        password,
        quota,
        uid,
        updated,
        user
) VALUES (
        :aid,
        :created,
        :did,
        :gid,
        :home,
        :password,
        :quota,
        :uid,
        :updated,
        :user
)";
            $res = db::qry($sql, [
                'aid'       => $d['aid'],
                'created'   => date('Y-m-d H:i:s'),
                'did'       => $d['id'],
                'gid'       => $d['gid'],
                'home'      => '/home/u/' . $rhs . '/home/' . $lhs,
                'password'  => util::mail_password($password),
                'quota'     => $quota,
                'uid'       => $d['uid'],
                'updated'   => date('Y-m-d H:i:s'),
                'user'      => $user,
            ]);
            // test $res ?

            util::log('Created mailbox for ' . $user, 'success');
            shell_exec("nohup sh -c 'sleep 1; sudo addvmail $user' > /tmp/addvmail.log 2>&1 &");
            util::ses('p', '', '1');
            return $this->list();
        }
        return $this->t->create($this->in);
    }

    protected function read() : string
    {
        return $this->t->update(db::read('*', 'id', $this->g->in['i'], '', 'one'));
    }

    function update() : string
    {
        if ($_POST) {
            extract($this->in);
            $quota *= 1048576;
            $active = $active ? 1 : 0;

            if (!filter_var($user, FILTER_VALIDATE_EMAIL)) {
                util::log('Email address is invalid');
                $_POST = []; return $this->read();
            }

            if ($passwd1 && $passwd2) {
                if (!util::chkpw($passwd1, $passwd2)) {
                    $_POST = []; return $this->read();
                }

                $sql = "
 UPDATE `vmails` SET
        `password`  = :password,
        `updated`   = :updated
  WHERE `id` = :id";

                $res = db::qry($sql, [
                    'id'        => $this->g->in['i'],
                    'updated'   => date('Y-m-d H:i:s'),
                    'password'  => util::mail_password($passwd1),
                ]);
            }

            $sql = "
 UPDATE `vmails` SET
        `active`    = :active,
        `quota`     = :quota,
        `updated`   = :updated
  WHERE `id` = :id";

            $res = db::qry($sql, [
                'id'      => $this->g->in['i'],
                'active'  => $active,
                'quota'   => $quota,
                'updated' => date('Y-m-d H:i:s'),
            ]);

            util::log('Mailbox details for ' . $user . ' have been saved', 'success');
            util::ses('p', '', '1');
            return $this->list();
        } elseif ($this->g->in['i']) {
            return $this->read();
        } else return 'Error updating item';
    }

    function delete() : string
    {
        if ($this->g->in['i']) {
            $user = db::read('user', 'id', $this->g->in['i'], '', 'col');

            db::qry("DELETE FROM `vmails` WHERE `id` = :id", ['id' => $this->g->in['i']]);
            db::qry("DELETE FROM `valias` WHERE `target` = :user", ['user' => $user]);
            db::qry("DELETE FROM `logging` WHERE `name` = :user", ['user' => $user]);

            util::log('Removed ' . $user, 'success');
            shell_exec("nohup sh -c 'sleep 1; sudo delvmail $user' > /tmp/delvmail.log 2>&1 &");
            util::ses('p', '', '1');
            return $this->list();
        }
        return 'Error deleting item';
    }
}

// lib/php/plugins/about.php 20150101 - 20170317

class Plugins_About extends Plugin
{
    public function list() : string
    {
        $buf = '
      <h2>About</h2>
      <p>
This is an example of a simple PHP7 "framework" to provide the core
structure for further experimental development with both the framework
design and some of the new features of PHP7.
      </p>
      <form method="post">
        <p class="text-center">
          <a class="btn btn-success" href="?o=about&l=success:Howdy, all is okay.">Success Message</a>
          <a class="btn btn-danger" href="?o=about&l=danger:Houston, we have a problem.">Danger Message</a>
          <a class="btn btn-secondary" href="#" onclick="ajax(\'1\')">JSON</a>
          <a class="btn btn-secondary" href="#" onclick="ajax(\'\')">HTML</a>
          <a class="btn btn-secondary" href="#" onclick="ajax(\'foot\')">FOOT</a>
        </p>
      </form>
      <pre id="dbg"></pre>
      <script>
function ajax(a) {
  if (window.XMLHttpRequest)  {
    var x = new XMLHttpRequest();
    x.open("POST", "", true);
    x.onreadystatechange = function() {
      if (x.readyState == 4 && x.status == 200) {
        document.getElementById("dbg").innerHTML = x.responseText
          .replace(/</g,"&lt;")
          .replace(/>/g,"&gt;")
          .replace(/\\\n/g,"\n")
          .replace(/\\\/g,"");
    }}
    x.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    x.send("o=about&x="+a);
    return false;
  }
}
      </script>';
        return $this->t->list(['buf' => $buf]);
    }
}

// lib/php/plugins/contact.php 20150101 - 20170317

class Plugins_Contact extends Plugin
{
    public function list() : string
    {
        $buf = '
      <h2>Email Contact Form</h2>
      <form id="contact-send" method="post" onsubmit="return mailform(this);">
        <p><input id="subject" required="" type="text" placeholder="Message Subject"></p>
        <p><textarea id="message" rows="9" required="" placeholder="Message Content"></textarea></p>
        <p class="text-right">
          <small>(Note: Doesn\'t seem to work with Firefox 50.1)</small>
          <input class="btn" type="submit" id="send" value="Send">
        </p>
      </form>';

        $js = '
      <script>
function mailform(form) {
    location.href = "mailto:' . $this->g->cfg['email'] . '"
        + "?subject=" + encodeURIComponent(form.subject.value)
        + "&body=" + encodeURIComponent(form.message.value);
    form.subject.value = "";
    form.message.value = "";
    alert("Thank you for your message. We will get back to you as soon as possible.");
    return false;
}
      </script>';
        return $this->t->list(['buf' => $buf, 'js' => $js]);
    }
}

// lib/php/plugins/home.php 20150101 - 20170317

class Plugins_Home extends Plugin
{
    public function list() : string
    {
        $buf = '
      <h3>
        <i class="fa fa-server fa-fw"></i> NetServa
        <small>(Hosting Control Panel)</small>
      </h3>
      <p>
This is an ultra simple web based Hosting Control Panel for a lightweight
DNS, Mail and Web server based on Ubuntu Server (minimum Zesty 17.04). It
uses PowerDNS for DNS, Postfix/Dovecot + Spamprobe for SMTP and spam filtered
IMAP email hosting along with nginx + PHP7 FPM + LetsEncrypt SSL for efficient
and secure websites. It can use either SQLite or MySQL as database backends
and the SQLite version only requires <b>60Mb</b> of ram on a fresh install so
is ideal for LXD containers or 256Mb VPS plans. Some of the features are...
      </p>
      <ul>
        <li> <b>NetServa</b> does not reqire Python or Ruby, just PHP and Bash</li>
        <li> Fully functional DNS, Mail and Web server with Spam filtering</li>
        <li> Built from the ground up using Bootstrap 4 and jQuery 3</li>
      </ul>
      <p>
Comments and pull requests are most welcome via the Issue Tracker link below.
      </p>
      <p class="text-center">
        <a class="btn btn-primary" href="https://github.com/netserva/www">Project Page</a>
        <a class="btn btn-primary" href="https://github.com/netserva/www/issues">Issue Tracker</a>
      </p>';
        return $this->t->list(['buf' => $buf]);
    }
}

// lib/php/plugins/vhosts.php 20170225

class Plugins_Vhosts extends Plugin
{
    protected
    $tbl = 'vhosts',
    $in = [
        'active'    => 0,
        'aid'       => 0,
        'aliases'   => 10,
        'diskquota' => 2147483648,
        'domain'    => '',
        'gid'       => 1000,
        'mailboxes' => 2,
        'mailquota' => 1073741824,
        'uid'       => 1000,
        'uname'     => '',
    ];

    protected function create() : string
    {
        if ($_POST) {
            $this->in['diskquota'] *= 1048576;
            $this->in['mailquota'] *= 1048576;
            extract($this->in);
            $active = $active ? 1 : 0;

            if (strpos($domain, '@'))
                list($uname, $domain) = explode('@', $domain);

            if (file_exists('/home/u/' . $domain)) {
                util::log('/home/u/' . $domain . ' already exists', 'warning');
//                $_POST = []; return $this->t->create($this->in);
            }

            if (!filter_var(gethostbyname($domain . '.'), FILTER_VALIDATE_IP)) {
                util::log('Invalid domain name');
                $_POST = []; return $this->t->create($this->in);
            }

            if ($mailquota > $diskquota) {
                util::log('Mailbox quota exceeds domain disk quota');
                $_POST = []; return $this->t->create($this->in);
            }

            $sql = "
 SELECT `domain` FROM `vhosts`
  WHERE `domain` = :domain";

            $num_results = db::qry($sql, ['domain' => $domain], 'one');

            if ($num_results != 0) {
                util::log('Domain already exists');
                $_POST = []; return $this->t->create($this->in);
            }

            $vhost = $uname ? "$uname@$domain" : $domain;
            $dtype = $this->g->db['type'];
            $pws = shell_exec("sudo newpw 3");
            $ret = shell_exec("sudo addvhost $vhost $dtype $pws");
            util::redirect($this->g->cfg['self'] . '?o=vhosts', 5, $ret);
            util::log('Created ' . $vhost, 'success');
            shell_exec("nohup sh -c 'sudo serva restart web' > /tmp/serva.log 2>&1 &");
            exit;
        }
        return $this->t->create($this->in);
    }

    protected function read() : string
    {
        return $this->t->update(db::read('*', 'id', $this->g->in['i'], '', 'one'));
    }

    protected function update() : string
    {
        if ($_POST) {
            extract($this->in);
            $diskquota *= 1048576;
            $mailquota *= 1048576;
            $active = $active ? 1 : 0;

            if ($mailquota > $diskquota) {
                util::log('Mailbox quota exceeds disk quota');
                $_POST = []; return $this->read();
            }

            $size_upath = db::qry("
 SELECT size_upath
   FROM logging
  WHERE name = :name", ['name' => $domain], 'col');

//            if ($mailquota < $size_upath) {
//                util::log('Mailbox quota must be greater than current used diskspace of ' . util::numfmt($size_upath));
//                $_POST = []; return $this->read();
//            }

            if (!filter_var(gethostbyname($domain . '.'), FILTER_VALIDATE_IP)) {
                util::log('Domain name is invalid');
                $_POST = []; return $this->read();
            }

            $sql = "
 UPDATE `vhosts` SET
        `active`    = :active,
        `aliases`   = :aliases,
        `diskquota` = :diskquota,
        `domain`    = :domain,
        `mailboxes` = :mailboxes,
        `mailquota` = :mailquota,
        `updated`   = :updated
  WHERE `id` = :id";

            $res = db::qry($sql, [
                'id'        => $this->g->in['i'],
                'active'    => $active,
                'aliases'   => $aliases,
                'diskquota' => $diskquota,
                'domain'    => $domain,
                'mailboxes' => $mailboxes,
                'mailquota' => $mailquota,
                'updated'   => date('Y-m-d H:i:s'),
            ]);
            // test $res ?

            util::log('Vhost ID ' . $this->g->in['i'] . ' updated', 'success');
            util::ses('p', '', '1');
            return $this->list();
        } elseif ($this->g->in['i']) {
            return $this->read();
        } else return 'Error updating item';
    }

    protected function delete() : string
    {
        if ($this->g->in['i']) {

            $vhost = db::read('domain', 'id', $this->g->in['i'], '', 'col');

            db::qry("DELETE FROM `vhosts` WHERE `id` = :did", ['did' => $this->g->in['i']]);
            db::qry("DELETE FROM `valias` WHERE `did` = :did", ['did' => $this->g->in['i']]);
            db::qry("DELETE FROM `vmails` WHERE `did` = :did", ['did' => $this->g->in['i']]);
            db::qry("DELETE FROM `logging` WHERE `did` = :did", ['did' => $this->g->in['i']]);

            $ret = shell_exec("sudo delvhost $vhost " . $this->g->db['type']);
            util::redirect($this->g->cfg['self'] . '?o=vhosts', 5, $ret);
            util::log('Removed ' . $vhost, 'success');
            shell_exec("nohup sh -c 'sudo serva restart web' > /tmp/serva.log 2>&1 &");
            exit;
        }
        return 'Error deleting item';
    }
}

// lib/php/plugins/domains.php 20150101 - 20170423

class Plugins_Domains extends Plugin
{
    protected
    $dbh = null,
    $tbl = 'domains', // dnszones ?
    $in = [
        'name'        => '',
        'master'      => '',
        'last_check'  => '',
        'disabled'    => 0,
        'type'        => '',
        'notified_serial' => '',
        'account'     => '',
    ];

    public function __construct(Theme $t)
    {
        if ($t->g->dns['db']['type'])
            $this->dbh = new db($t->g->dns['db']);
        parent::__construct($t);
    }

    protected function create() : string
    {
        if ($_POST) {
            extract($_POST);
            extract($this->g->dns);
            $created = date('Y-m-d H:i:s');
            $disable = 0;
            $soa_buf =
              $soa['primary'] . $domain . ' ' .
              $soa['email'] . $domain . '. ' .
              date('Ymd') . '00' . ' ' .
              $soa['refresh'] . ' ' .
              $soa['retry'] . ' ' .
              $soa['expire'] . ' ' .
              $soa['ttl'];
            $did = db::create([
                'name'    => $domain,
                'master'  => $type === 'SLAVE' ? $master : '',
                'type'    => $type ? $type : 'MASTER',
                'updated' => $created,
                'created' => $created,
            ]);

            if ($type === 'SLAVE') {
                util::log('Created DNS Zone: ' . $domain, 'success');
                return $this->list();
            }

            $sql = "
 INSERT INTO `records` (
        content, created, disabled, domain_id, name, prio, ttl, type, updated
) VALUES (
        :content, :created, :disabled, :did, :domain, :prio, :ttl, :type, :updated
)";
            db::qry($sql, [
                'content' => $soa_buf,
                'created' => $created,
                'did'     => $did,
                'disabled'=> $disable,
                'domain'  => $domain,
                'prio'    => $prio,
                'ttl'     => $ttl,
                'type'    => 'SOA',
                'updated' => $created,
            ]);
            db::qry($sql, [
                'content' => $ns1 . $domain,
                'created' => $created,
                'did'     => $did,
                'disabled'=> $disable,
                'domain'  => $domain,
                'prio'    => $prio,
                'ttl'     => $ttl,
                'type'    => 'NS',
                'updated' => $created,
            ]);
            db::qry($sql, [
                'content' => $ns2 . $domain,
                'created' => $created,
                'did'     => $did,
                'disabled'=> $disable,
                'domain'  => $domain,
                'prio'    => $prio,
                'ttl'     => $ttl,
                'type'    => 'NS',
                'updated' => $created,
            ]);
            db::qry($sql, [
                'content' => $a,
                'created' => $created,
                'did'     => $did,
                'disabled'=> $disable,
                'domain'  => $domain,
                'prio'    => $prio,
                'ttl'     => $ttl,
                'type'    => 'A',
                'updated' => $created,
            ]);
            db::qry($sql, [
                'content' => $a,
                'created' => $created,
                'did'     => $did,
                'disabled'=> $disable,
                'domain'  => '*.' . $domain,
                'prio'    => $prio,
                'ttl'     => $ttl,
                'type'    => 'A',
                'updated' => $created,
            ]);
            db::qry($sql, [
                'content' => $mx . $domain,
                'created' => $created,
                'did'     => $did,
                'disabled'=> $disable,
                'domain'  => $domain,
                'prio'    => $prio,
                'ttl'     => $ttl,
                'type'    => 'MX',
                'updated' => $created,
            ]);
            util::log('Created DNS Zone: ' . $domain, 'success');
            return $this->list();
        }
        return $this->t->create($this->in);
    }

    protected function update() : string
    {
        if ($_POST) {
            extract($_POST);

            $today = date('Ymd');
            $serial_day = substr($serial, 0, 8);
            $serial_rev = substr($serial, -2);

            $serial = ($serial_day == $today)
                ? "$today" . sprintf("%02d", $serial_rev + 1)
                : "$today" . "00";

            $soa =
              $primary . ' ' .
              $email . ' ' .
              $serial . ' ' .
              $refresh . ' ' .
              $retry . ' ' .
              $expire . ' ' .
              $ttl;

            $sql = "
 UPDATE records SET
        ttl     = :ttl,
        content = :soa,
        updated = :updated
  WHERE type = 'SOA'
    AND domain_id = :did";

            $res = db::qry($sql, [
                'did' => $this->g->in['i'],
                'soa' => $soa,
                'ttl' => $ttl,
                'updated' => date('Y-m-d H:i:s'),
            ]);
            // TODO check $res ???
            util::log('Updated DNS domain ID ' . $this->g->in['i'], 'success');
            return $this->list();
        } elseif ($this->g->in['i']) {
            $dom = db::read('name,type,master', 'id', $this->g->in['i'], '', 'one');
            if ($dom['type'] === 'SLAVE') {
                return $this->t->update($dom);
            } else {
                $sql = "
 SELECT content as soa
   FROM records
  WHERE type='SOA'
    AND domain_id=:did";

                $soa = db::qry($sql, ['did' => $this->g->in['i']], 'one');
                return $this->t->update(array_merge($dom, $soa));
            }
        }
        return 'Error updating item';
    }

    protected function delete() : string
    {
        if ($this->g->in['i']) {
            $sql = "
 DELETE FROM `records`
  WHERE  domain_id = :id";

            $res1 = db::qry($sql, ['id' => $this->g->in['i']]);
            $res2 = db::delete([['id', '=', $this->g->in['i']]]);
            // TODO check $res1 and $res2 ???
            util::log('Deleted DNS zone ID: ' . $this->g->in['i'], 'success');
            util::ses('p', '', '1');
            return $this->list();
        }
        return 'Error deleting item';
    }

    protected function list() : string
    {
        if ($this->g->in['x'] !== 'json')
          return $this->t->list([]);

        extract($this->t->g->in);

        $search = $search ? "
 HAVING (D.name LIKE '%$search%')
     OR (D.type LIKE '%$search%')" : '';

        if ($sort === 'name') $orderby = 'D.`name`';
        elseif ($sort === 'type') $orderby = 'D.`type`';
        elseif ($sort === 'records') $orderby = '`records`';
        else $orderby = 'D.`updated`';

        $sql = "
 SELECT D.id,D.name,D.type,count(R.domain_id) AS records
   FROM domains D
   LEFT OUTER JOIN records R ON D.id = R.domain_id
  GROUP BY D.name, D.type $search
  ORDER BY $orderby $order LIMIT $offset,$limit";

        return json_encode(array_merge(
            ['total' => db::read('count(id)', '', '', '', 'col')],
            ['rows' => db::qry($sql)]
        ));
    }
}

// lib/php/plugins/mail/alias_domains.php 20170225

class Plugins_Mail_DomainAlias extends Plugin
{
    protected
    $tbl = 'alias_domain',
    $in = [
        'alias_domain'      => '',
        'target_domain'     => '',
        'active'            => 0,
    ];

    protected function create() : string
    {
        if ($_POST) {
            extract($this->in);

            if (!util::is_valid_domain_name($alias_domain)) {
                util::log('Invalid alias domain name');
                $_POST = [];
                return $this->t->create($this->in);
            }

            if (!util::is_valid_domain_name($target_domain)) {
                util::log('Invalid target domain name');
                $_POST = [];
                return $this->t->create($this->in);
            }

            if ($alias_domain === $target_domain) {
                util::log('Alias domain must not be equal to target domain');
                $_POST = [];
                return $this->t->create($this->in);
            }

            $sql = "
 SELECT `domain` FROM `domain`
  WHERE `domain`= :target_domain";

            $num_results = db::qry($sql, ['target_domain' => $target_domain], 'one');

            if (!$num_results) {
                util::log('Target domain not found');
                $_POST = [];
                return $this->t->create($this->in);
            }

            $sql = "
 SELECT `alias_domain` FROM `alias_domain`
  WHERE `alias_domain`= :alias_domain";

            $num_results = db::qry($sql, [
                'alias_domain' => $alias_domain,
            ], 'one');

            if ($num_results) {
                util::log('Alias domain already exists');
                $_POST = [];
                return $this->t->create($this->in);
            }

            $sql = "
 INSERT INTO `alias_domain` (
        `alias_domain`,
        `target_domain`,
        `active`,
        `updated`,
        `created`
) VALUES (
        :alias_domain,
        :target_domain,
        :active,
        :updated,
        :created
)";
            $res = db::qry($sql, [
                'alias_domain'  => $alias_domain,
                'target_domain' => $target_domain,
                'active'        => $active ? 1 : 0,
                'updated'       => date('Y-m-d H:i:s'),
                'created'       => date('Y-m-d H:i:s'),
            ]);
//            $lid = db::$dbh->lastInsertId();
            util::log('Created domain alias from ' . $alias_domain . ' to ' . $target_domain, 'success');
            return $this->list();
        } else return $this->t->create($this->in);
    }
}

// lib/php/plugins/mail/infomail.php 20170225 - 20170514

class Plugins_InfoMail extends Plugin
{
    protected $pflog = '/tmp/pflogsumm.log';

    public function list() : string
    {
        return $this->t->list([
            'mailq' => shell_exec('mailq'),
            'pflogs' => is_readable($this->pflog)
                ? file_get_contents($this->pflog)
                : 'none',
            'pflog_time' => is_readable($this->pflog)
                ? round(abs(date('U') - filemtime($this->pflog)) / 60, 0) . ' min.'
                : '0 min.',
        ]);
    }

    public function pflog_renew()
    {
        $this->pflogs = shell_exec('sudo pflogs');
        return $this->list();
    }
}

// lib/php/plugin.php 20150101 - 20170316

class Plugin
{
    protected
    $buf = '',
    $dbh = null,
    $tbl = '',
    $in  = [];

    public function __construct(Theme $t)
    {
        $o = $t->g->in['o'];
        $m = $t->g->in['m'];

        if (!util::is_usr() && $o !== 'auth' && $m !== 'list' && $m !== 'read') {
            util::log('You must <a href="?o=auth">Sign in</a> to create, update or delete items');
            header('Location: ' . $t->g->cfg['self'] . '?o=auth');
            exit();
        }

        $this->t  = $t;
        $this->g  = $t->g;
        $this->in = util::esc($this->in);
        if ($this->tbl) {
            if (!is_null($this->dbh))
                db::$dbh = $this->dbh;
            elseif (is_null(db::$dbh))
                db::$dbh = new db($t->g->db);
            db::$tbl = $this->tbl;
        }
        $this->buf .= $this->{$t->g->in['m']}();
    }

    public function __toString() : string
    {
        return $this->buf;
    }

    protected function create() : string
    {
        if ($_POST) {
            $this->in['updated'] = date('Y-m-d H:i:s');
            $this->in['created'] = date('Y-m-d H:i:s');
            $lid = db::create($this->in);
            util::log('Item number ' . $lid . ' created', 'success');
            util::ses('p', '', '1');
            return $this->list();
        } else return $this->t->create($this->in);
    }

    protected function read() : string
    {
        return $this->t->read(db::read('*', 'id', $this->g->in['i'], '', 'one'));
    }

    protected function update() : string
    {
        if ($_POST) {
            $this->in['updated'] = date('Y-m-d H:i:s');
            db::update($this->in, [['id', '=', $this->g->in['i']]]);
            util::log('Item number ' . $this->g->in['i'] . ' updated', 'success');
            util::ses('p', '', '1');
            return $this->list();
        } elseif ($this->g->in['i']) {
            return $this->t->update(db::read('*', 'id', $this->g->in['i'], '', 'one'));
        } else return 'Error updating item';
    }

    protected function delete() : string
    {
        if ($this->g->in['i']) {
            $res = db::delete([['id', '=', $this->g->in['i']]]);
            util::log('Item number ' . $this->g->in['i'] . ' removed', 'success');
            util::ses('p', '', '1');
            return $this->list();
        } else return 'Error deleting item';
    }

    protected function list() : string
    {
        $pager = util::pager(
            (int) util::ses('p'),
            (int) $this->g->cfg['perp'],
            (int) db::read('count(id)', '', '', '', 'col')
        );

        return $this->t->list(array_merge(
            db::read('*', '', '', 'ORDER BY `updated` DESC LIMIT ' . $pager['start'] . ',' . $pager['perp']),
            ['pager' => $pager]
        ));
    }

    public function __call(string $name, array $args) : string
    {
        return 'Plugin::' . $name . '() not implemented';
    }
}

// lib/php/db.php 20150225 - 20170316

class Db extends \PDO
{
    public static $dbh = null;
    public static $tbl = null;

    public function __construct(array $dbcfg)
    {
        if (is_null(self::$dbh)) {
            extract($dbcfg);
            $dsn = $type === 'mysql'
                ? 'mysql:' . ($sock ? 'unix_socket='. $sock : 'host=' . $host . ';port=' . $port) . ';dbname=' . $name
                : 'sqlite:' . $path;
            $pass = file_exists($pass) ? trim(file_get_contents($pass)) : $pass;
            try {
                parent::__construct($dsn, $user, $pass, [
                    \PDO::ATTR_EMULATE_PREPARES => false,
                    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                    \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                ]);
            } catch(\PDOException $e) {
                die(__FILE__ . ' ' . __LINE__ . "<br>\n" . $e->getMessage());
            }
        }
    }

    public static function create(array $ary)
    {
        $fields = $values = '';
        foreach($ary as $k=>$v) {
            $fields .= "
                $k,";
            $values .= "
                :$k,";
        }
        $fields = rtrim($fields, ',');
        $values = rtrim($values, ',');

        $sql = "
 INSERT INTO `" . self::$tbl . "` ($fields)
 VALUES ($values)";

        try {
            $stm = self::$dbh->prepare($sql);
            self::bvs($stm, $ary);
            $res = $stm->execute();
            return self::$dbh->lastInsertId();
        } catch(\PDOException $e) {
            die(__FILE__ . ' ' . __LINE__ . "<br>\n" . $e->getMessage());
        }
    }

    public static function read(
        string $field,
        string $where = '',
        string $wval  = '',
        string $extra = '',
        string $type  = 'all')
    {
        $w = $where ? "
    WHERE $where = :wval" : '';

        $a = ($wval || $wval == '0') ? ['wval' => $wval] : [];

        $sql = "
 SELECT $field
   FROM `" . self::$tbl . "`$w $extra";

        return self::qry($sql, $a, $type);
    }

    public static function update(array $set, array $where)
    {
        $set_str = '';
        foreach($set as $k=>$v) $set_str .= "
        $k = :$k,";
        $set_str = rtrim($set_str, ',');

        $where_str = '';
        $where_ary = [];
        foreach($where as $k=>$v) {
            $where_str .= " " . $v[0] . " " . $v[1] . " :" . $v[0];
            $where_ary[$v[0]] = $v[2] ;
        }
        $ary = array_merge($set, $where_ary);

        $sql = "
 UPDATE `" . self::$tbl . "` SET$set_str
  WHERE$where_str";

        try {
            $stm = self::$dbh->prepare($sql);
            self::bvs($stm, $ary);
            return $stm->execute();
        } catch(\PDOException $e) {
            die(__FILE__ . ' ' . __LINE__ . "<br>\n" . $e->getMessage());
        }
    }

    public static function delete(array $where)
    {
        $where_str = '';
        $where_ary = [];
        foreach($where as $k=>$v) {
            $where_str .= " " . $v[0] . " " . $v[1] . " :" . $v[0];
            $where_ary[$v[0]] = $v[2] ;
        }

        $sql = "
 DELETE FROM `" . self::$tbl . "`
  WHERE $where_str";

        try {
            $stm = self::$dbh->prepare($sql);
            self::bvs($stm, $where_ary);
            return $stm->execute();
        } catch(\PDOException $e) {
            die(__FILE__ . ' ' . __LINE__ . "<br>\n" . $e->getMessage());
        }
    }

    public static function qry(string $sql, array $ary = [], string $type = 'all')
    {
        try {
            if ($type !== 'all') $sql .= ' LIMIT 1';
            $stm = self::$dbh->prepare($sql);
            if ($ary) self::bvs($stm, $ary);
            if ($stm->execute()) {
                $res = null;
                if ($type === 'all') $res = $stm->fetchAll();
                elseif ($type === 'one') $res = $stm->fetch();
                elseif ($type === 'col') $res = $stm->fetchColumn();
                $stm->closeCursor();
                return $res;
            } else return false;
        } catch(\PDOException $e) {
            die(__FILE__ . ' ' . __LINE__ . "<br>\n" . $e->getMessage());
        }
    }

    // bind value statement
    public static function bvs($stm, array $ary)
    {
        if (is_object($stm) && ($stm instanceof \PDOStatement)) {
            foreach($ary as $k => $v) {
                if (is_numeric($v))     $p = \PDO::PARAM_INT;
                elseif (is_bool($v))    $p = \PDO::PARAM_BOOL;
                elseif (is_null($v))    $p = \PDO::PARAM_NULL;
                elseif (is_string($v))  $p = \PDO::PARAM_STR;
                else $p = false;
                if ($p !== false) $stm->bindValue(":$k", $v, $p);
            }
        }
    }
}

// lib/php/init.php 20150101 - 20170305

class Init
{
    private $t = null;

    public function __construct($g)
    {
        session_start();
        //$_SESSION = []; // to reset session for testing
        util::cfg($g);
        $g->in = util::esc($g->in);
        $g->cfg['self'] = str_replace('index.php', '', $_SERVER['PHP_SELF']);
        util::ses('l');
        $t = util::ses('t', '', $g->in['t']);
        $t1 = 'themes_' . $t . '_' . $g->in['o'];
        $t2 = 'themes_' . $t . '_theme';

        $this->t = $thm = class_exists($t1) ? new $t1($g)
            : (class_exists($t2) ? new $t2($g) : new Theme($g));

        $p  = 'plugins_' . $g->in['o'];
        if (class_exists($p)) {
            util::remember($g);
            $g->out['main'] = (string) new $p($thm);
        } else $g->out['main'] = "Error: no plugin object!";

//        $g->out['end'] = var_export($_SESSION['usr'], true); // debug

        if (empty($g->in['x']) || ($g->in['x'] !== 'json'))
            foreach ($g->out as $k => $v)
                $g->out[$k] = method_exists($thm, $k) ? $thm->$k() : $v;
    }

    public function __toString() : string
    {

        $g = $this->t->g;
//            $xhr = $g->out[$g->in['x']] ?? '';
            header('Content-Type: application/json');
            return $g->out['main'];
//            exit;
//            if ($xhr) {
//                if ($xhr == 'json') return $g->out['main'];
//                else return $xhr;
//            }
//            return json_encode($g->out, JSON_PRETTY_PRINT);
        }
        return $this->t->html();
    }

    public function __destruct()
    {
//dbg($this->t->g);
        error_log($_SERVER['REMOTE_ADDR'].' '.round((microtime(true)-$_SERVER['REQUEST_TIME_FLOAT']), 4));
    }
}

function dbg($var = null)
{
    if (is_object($var))
        error_log(ReflectionObject::export($var, true));
    ob_start();
    print_r($var);
    $ob = ob_get_contents();
    ob_end_clean();
    error_log($ob);
}
// lib/php/themes/bootstrap/accounts.php 20170225 - 20170317

class Themes_Bootstrap_Accounts extends Themes_Bootstrap_Theme
{
    public function create(array $in) : string
    {
        return $this->editor($in);
    }

    public function read(array $in) : string
    {
        return $this->editor($in);
    }

    public function update(array $in) : string
    {
        return $this->editor($in);
    }

    public function list(array $in) : string
    {
        $buf = $pgr_top = $pgr_end = '';
        $pgr = $in['pager']; unset($in['pager']);
        $num = count($in);

        if ($pgr['last'] > 1) {
            $pgr_top ='
          <div class="col-md-6">' . $this->pager($pgr) . '
          </div>';
            $pgr_end = '
          <div class="row">
            <div class="col-12">' . $this->pager($pgr) . '
            </div>
          </div>';
        }

        foreach ($in as $a) {
            extract($a);
            $buf .= '
        <tr>
          <td>
            <a href="?o=accounts&m=read&i=' . $id . '" title="Show account: ' . $id . '">
              <strong>' . $login . '</strong>
            </a>
          </td>
          <td>' . $fname . '</td>
          <td>' . $lname . '</td>
          <td>' . $altemail . '</td>
          <td>' . $this->g->acl[$acl] . '</td>
          <td>' . $grp . '</td>
        </tr>';
        }

        return '
        <div class="row">
          <div class="col-md-6">
            <h3 class="min60">
              <a href="?o=accounts&m=create" title="Add new account">
                <i class="fa fa-users fa-fw"></i> Accounts
                <small><i class="fa fa-plus-circle fa-fw"></i></small>
              </a>
            </h3>
          </div>' . $pgr_top . '
        </div>
        <div class="table-responsive">
          <table class="table table-sm min600">
            <thead class="nowrap">
              <tr>
                <th>User ID</th>
                <th>First Name</th>
                <th>Last Name</th>
                <th>Alt Email</th>
                <th>ACL</th>
                <th>Grp</th>
              </tr>
            </thead>
            <tbody>' . $buf . '
            </tbody>
          </table>
        </div>' . $pgr_end;
    }

    private function editor(array $in) : string
    {
        extract($in);

        if ($this->g->in['m'] === 'create') {
            $header = 'Add Account';
            $switch = '';
            $submit = '
                <a class="btn btn-outline-primary" href="?o=accounts&m=list">&laquo; Back</a>
                <button type="submit" name="m" value="create" class="btn btn-primary">Add This Account</button>';
        } else {
            $header = 'Update Account';
            $switch = !util::is_usr($id) && (util::is_acl(0) || util::is_acl(1)) ? '
                  <a class="btn btn-outline-primary pull-left" href="?o=accounts&m=switch_user&i=' . $id . '">Switch to ' . $login . '</a>' : '';
            $submit = '
                <a class="btn btn-outline-primary" href="?o=accounts&m=list">&laquo; Back</a>
                <a class="btn btn-danger" href="?o=accounts&m=delete&i=' . $id . '" title="Remove this account" onClick="javascript: return confirm(\'Are you sure you want to remove ' . $login . '?\')">Remove</a>
                <button type="submit" name="m" value="update" class="btn btn-primary">Update</button>';
        }

        if (util::is_adm()) {
            $acl_ary = $grp_ary = [];
            foreach($this->g->acl as $k => $v) $acl_ary[] = [$v, $k];
            $acl_buf = $this->dropdown($acl_ary, 'acl', $acl, '', 'custom-select');
            $res = db::qry("
 SELECT login,id FROM `accounts`
  WHERE acl = :0 OR acl = :1", ['0' => 0, "1" => 1]);

            foreach($res as $k => $v) $grp_ary[] = [$v['login'], $v['id']];
            $grp_buf = $this->dropdown($grp_ary, 'grp', $grp, '', 'custom-select');
            $aclgrp_buf = '
                <div class="form-group">
                  <label for="acl">ACL</label><br>' . $acl_buf . '
                </div>
                <div class="form-group">
                  <label for="grp">Group</label><br>' . $grp_buf . '
                </div>';
        } else {
            $aclgrp_buf = '';
            $anotes_buf = '';
        }

        return '
          <h3 class="min600">
            <a href="?o=accounts&m=list">
              <i class="fa fa-user fa-fw"></i> ' . $header . '
            </a>
          </h3>
          <form method="post" action="' . $this->g->cfg['self'] . '">
            <input type="hidden" name="o" value="' . $this->g->in['o'] . '">
            <input type="hidden" name="i" value="' . $id . '">
            <div class="row">
              <div class="col-md-4">
                <div class="form-group">
                  <label for="login">UserID</label>
                  <input type="email" class="form-control" id="login" name="login" value="' . $login . '" required>
                </div>
                <div class="form-group">
                  <label for="altemail">Alt Email</label>
                  <input type="text" class="form-control" id="altemail" name="altemail" value="' . $altemail . '">
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label for="fname">First Name</label>
                  <input type="text" class="form-control" id="fname" name="fname" value="' . $fname . '" required>
                </div>
                <div class="form-group">
                  <label for="lname">Last Name</label>
                  <input type="text" class="form-control" id="lname" name="lname" value="' . $lname . '" required>
                </div>
              </div>
              <div class="col-md-4">' . $aclgrp_buf . '
              </div>
            </div>
            <div class="row">
              <div class="col-md-12">' . $switch . '
                <div class="btn-group pull-right">' . $submit . '
                </div>
              </div>
            </div>
          </form>';
    }
}

// lib/php/themes/bootstrap/valias.php 20170225

class Themes_Bootstrap_Valias extends Themes_Bootstrap_Theme
{
    public function create(array $in) : string
    {
        return $this->editor($in);
    }

    public function update(array $in) : string
    {
        return $this->editor($in);
    }

    public function list(array $in) : string
    {
        $buf = $pgr_top = $pgr_end = '';
        $pgr = $in['pager']; unset($in['pager']);

        if ($pgr['last'] > 1) {
            $pgr_top ='
          <div class="col-md-6">' . $this->pager($pgr) . '
          </div>';
            $pgr_end = '
          <div class="row">
            <div class="col-12">' . $this->pager($pgr) . '
            </div>
          </div>';
        }

        foreach($in as $row) {
            extract($row);
            $active = $active ? 1 : 0;
            list($lhs, $rhs) = explode('@', $source);
            $target_buf = '';
            $source_buf = (filter_var($source, FILTER_VALIDATE_EMAIL))
                ? $source
                : 'Catch-all ' . $source;

            foreach (explode(',', $target) as $t) {
                $target_buf .= nl2br(htmlspecialchars($t . PHP_EOL));
            }

            $active_buf = $active
                ? '<i class="fa fa-check text-success"></i>'
                : '<i class="fa fa-times text-danger"></i>';

            $buf .= '
            <tr>
              <td><a href="?o=valias&m=update&i=' . $id . '"><strong>' . $source_buf . '<strong></a></td>
              <td>' . $target_buf . ' </td>
              <td>' . $rhs . '</td>
              <td class="text-right">' . $active_buf . '</td>
            </tr>';
        }

        return '
        <div class="row">
          <div class="col-md-6">
          <h3 class="min600">
            <a href="?o=valias&m=create" title="Add Alias">
              <i class="fa fa-globe fa-fw"></i> Aliases
              <small><i class="fa fa-plus-circle fa-fw"></i></small>
            </a>
          </h3>
          </div>' . $pgr_top . '
        </div>
          <div class="table-responsive">
            <table class="table table-sm min600">
              <thead class="nowrap">
                <tr>
                  <th class="min100">Alias</th>
                  <th class="min150">Target Address</th>
                  <th class="min100">Domain</th>
                  <th class="min50"></th>
                </tr>
              </thead>
              <tbody>' . $buf . '
              </tbody>
            </table>
          </div>' . $pgr_end;

    }

    private function editor(array $in) : string
    {
        extract($in);

        $active = $active ? 1 : 0;
        $header = $this->g->in['m'] === 'create' ? 'Add Alias' : 'Update Alias';
        $submit = $this->g->in['m'] === 'create' ? '
                <a class="btn btn-secondary" href="?o=valias&m=list">&laquo; Back</a>
                <button type="submit" name="m" value="create" class="btn btn-primary">Add Alias</button>' : '
                <a class="btn btn-secondary" href="?o=valias&m=list">&laquo; Back</a>
                <a class="btn btn-danger" href="?o=valias&m=delete&i=' . $id . '" title="Remove alias" onClick="javascript: return confirm(\'Are you sure you want to remove ' . $source . '?\')">Remove</a>
                <button type="submit" name="m" value="update" class="btn btn-primary">Update</button>';

        $checked = $active ? ' checked' : '';

        return '
          <h3 class="min600">
            <a href="?o=valias&m=list">
              <i class="fa fa-globe fa-fw"></i> ' . $header . '
            </a>
          </h3>
          <p><b>Note:</b> If your chosen destination address is an external mailbox, the <b>receiving mailserver</b> may reject your message due to an SPF failure.</p>
          <form method="post" action="' . $this->g->cfg['self'] . '">
            <input type="hidden" name="o" value="' . $this->g->in['o'] . '">
            <input type="hidden" name="i" value="' . $this->g->in['i'] . '">
            <div class="row">
              <div class="form-group col-md-6">
                <label class="control-label" for="source">Alias Address(es)</label>
                <textarea autocorrect="off" autocapitalize="none" class="form-control" rows="4" name="source" id="source">' . $source . '</textarea>
                <p>Full email address/es or @example.com, to catch all messages for a domain (comma-separated). <b>Locally hosted domains only</b>.</p>
              </div>
              <div class="form-group col-md-6">
                <label class="control-label" for="target">Target Address(es)</label>
                <textarea autocorrect="off" autocapitalize="none" class="form-control" rows="4" id="target" name="target">' . $target . '</textarea>
                <p>Full email address/es (comma-separated).</p>
              </div>
            </div>
            <div class="row">
              <div class="col-md-2 offset-md-6">
                <div class="form-group">
                  <label class="custom-control custom-checkbox">
                    <input type="checkbox" class="custom-control-input" name="active" id="active"' . $checked . '>
                    <span class="custom-control-indicator"></span>
                    <span class="custom-control-description">Active</span>
                  </label>
                </div>
              </div>
              <div class="col-md-4 text-right">
                <div class="btn-group">' . $submit . '
                </div>
              </div>
            </div>
          </form>';
    }
}

// lib/php/themes/bootstrap/mailgraph.php 20170225 - 20170514

class Themes_Bootstrap_MailGraph extends Themes_Bootstrap_Theme
{
    public function list(array $in) : string
    {
        return '
        <h3><i class="fa fa-envelope fa-fw" aria-hidden="true"></i> MailServer Graph</h3>
        <div class="row">
          <div class="col-md-12 text-center">' . $in['mailgraph'] . '
          </div>
        </div>';
    }
}

// lib/php/themes/bootstrap/records.php 20170225

class Themes_Bootstrap_Records extends Themes_Bootstrap_Theme
{
    public function create(array $in) : string
    {
        return $this->editor($in);
    }

    public function list(array $in) : string
    {
        $buf = '';
        $domain = $in['domain']; unset($in['domain']);
        $active_buf = (isset($disabled) && $disabled == 0)
            ? '<i class="fa fa-check text-success"></i>'
            : '<i class="fa fa-times text-danger"></i>';

        foreach ($in as $row) {
            extract($row);
            $buf .= '
                <tr>
                  <td class="nowrap">
                    <a href="?o=records&m=read&i=' . $id . '" title="Show record ' . $id . '">
                      <strong>' . $name . '</strong>
                    </a>
                  </td>
                  <td>' . $type . '
                  </td>
                  <td class="nowrap ellide max200">' . $content . '
                  </td>
                  <td>' . $priority . '
                  </td>
                  <td>' . $ttl . '
                  </td>
                </tr>';
        }

        return '
          <h3 class="w30">
            <a href="?o=records&m=create&domain=' . $domain . '" title="Add new DNS record">
              <i class="fa fa-globe fa-fw"></i> ' . $domain . '
              <small><i class="fa fa-plus-circle fa-fw"></i></small>
            </a>
          </h3>
          <div class="table-responsive">
            <table class="table table-sm w30">
              <thead>
                <tr class="bg-primary text-white">
                  <th>Name</th>
                  <th>Type</th>
                  <th>Content</th>
                  <th>Priority</th>
                  <th>TTL</th>
                </tr>
              </thead>
              <tbody>' . $buf . '
              </tbody>
            </table>
          </div>
          <div class="row">
            <div class="col-12 text-right">
              <div class="btn-group">
                <a class="btn btn-secondary" href="?o=domains&m=list">&laquo; Back</a>
                <a class="btn btn-danger" href="?o=domains&m=delete&i=' . $this->g->in['i'] . '" title="Remove this item" onClick="javascript: return confirm(\'Are you sure you want to remove ' . $domain . '?\')">Remove</a>
                <a class="btn btn-primary" href="?o=domains&m=update&i=' . $this->g->in['i'] . '">Update</a>
              </div>
            </div>
          </div>';
    }

    public function update(array $in) : string
    {
        return $this->editor($in);
    }

    private function editor(array $in) : string
    {

        $buf = '';
        $types = [
            ['A',          'A'],
            ['AAAA',       'AAAA'],
            ['AFSDB',      'AFSDB'],
            ['CERT',       'CERT'],
            ['CNAME',      'CNAME'],
            ['DHCID',      'DHCID'],
            ['DLV',        'DLV'],
            ['DNSKEY',     'DNSKEY'],
            ['DS',         'DS'],
            ['EUI48',      'EUI48'],
            ['EUI64',      'EUI64'],
            ['HINFO',      'HINFO'],
            ['IPSECKEY',   'IPSECKEY'],
            ['KEY',        'KEY'],
            ['KX',         'KX'],
            ['LOC',        'LOC'],
            ['MINFO',      'MINFO'],
            ['MR',         'MR'],
            ['MX',         'MX'],
            ['NAPTR',      'NAPTR'],
            ['NS',         'NS'],
            ['NSEC',       'NSEC'],
            ['NSEC3',      'NSEC3'],
            ['NSEC3PARAM', 'NSEC3PARAM'],
            ['OPT',        'OPT'],
            ['PTR',        'PTR'],
            ['RKEY',       'RKEY'],
            ['RP',         'RP'],
            ['RRSIG',      'RRSIG'],
            ['SPF',        'SPF'],
            ['SRV',        'SRV'],
            ['SSHFP',      'SSHFP'],
            ['TLSA',       'TLSA'],
            ['TSIG',       'TSIG'],
            ['TXT',        'TXT'],
            ['WKS',        'WKS'],
        ];
        $domain = $in['domain']; unset($in['domain']);
        $domain_id = $in['domain_id']; unset($in['domain_id']);
//        $checked = $disabled == 0 ? ' checked' : '';
//        $checked = $active ? ' checked' : '';
        $options = $this->dropdown(
            $types,
            'type',
            'A',
            '',
            'custom-select'
        );

        foreach ($in as $row) {
            extract($row);
            $active = $disabled == 0 ? 1 : 0;
            $active_buf = $active
                ? '<i class="fa fa-check fa-fw text-success active_icon"></i>'
                : '<i class="fa fa-times fa-fw text-danger active_icon"></i>';
            $buf .= '
                <tr class="editrow" data-rowid="' . $id . '" data-active="' . $active . '">
                  <td class="min300"><b title="DNS record ID: ' . $id . '">' . $name . '</b></td>
                  <td class="min50">' . $type . '</td>
                  <td class="max300 ellide nowrap">' . $content . '</td>
                  <td class="min100">' . $priority . '</td>
                  <td class="min100">' . $ttl . '</td>
                  <td class="min100 text-right">' . $active_buf . '
                    <a class="editlink" href="#" title="Update DNS record ID: ' . $id . '">
                      <i class="fa fa-pencil fa-fw cursor-pointer"></i>
                    </a>
                    <a href="?o=records&m=delete&i=' . $id . '&domain_id=' . $domain_id . '" title="Remove DNS record ID: ' . $id . '" onClick="javascript: return confirm(\'Are you sure you want to remove record ID: ' . $id . '?\')">
                      <i class="fa fa-trash fa-fw cursor-pointer text-danger"></i>
                    </a>
                  </td>
                </tr>';
        }
        $checked = '';
        return '
              <div class="row">
                <div class="col-md-6">
                  <h3 class="min600">
                    <a href="?o=domains&m=list">
                      <i class="fa fa-chevron-left fa-fw"></i> ' . $domain . '
                    </a>
                  </h3>
                </div>
                <div class="col-md-6 text-right">
                  <a href="?o=records&m=update&i=' . $this->g->in['i'] . '">
                    <i class="fa fa-refresh fa-fw"></i>
                  </a>
                </div>
              </div>
              <div class="table-responsive">
                <table class="table table-sm min900">
                  <thead>
                    <tr>
                      <th>Name</th>
                      <th>Type</th>
                      <th>Content</th>
                      <th>Priority</th>
                      <th>TTL</th>
                      <th></th>
                    </tr>
                  </thead>
                  <tbody>' . $buf . '
              </table>
            </div>
              <form method="post" action="' . $this->g->cfg['self'] . '">
            <div class="row">
                <input type="hidden" id="o" name="o" value="' . $this->g->in['o'] . '">
                <input type="hidden" id="i" name="i" value="0">
                <input type="hidden" id="domain_id" name="domain_id" value="' . $this->g->in['i'] . '">
                <div class="col-md-3">
                  <div class="form-group">
                  <input type="text" class="form-control" id="name" name="name" data-regex="^([^.]+\.)*[^.]*$" value="">
                  </div>
                </div>
                <div class="col-md-2">' .  $options. '
                </div>
                <div class="col-md-4">
                  <input type="text" class="form-control" id="content" name="content" data-regex="^.+$" value="">
                </div>
                <div class="col-md-1">
                  <input type="text" class="form-control" id="prio" name="prio" data-regex="^[0-9]*$" value="0">
                </div>
                <div class="col-md-2">
                  <input type="text" class="form-control" id="ttl" name="ttl" data-regex="^[0-9]*$" value="300">
                </div>
            </div>
            <div class="row">
              <div class="col-md-2 offset-md-6">
                <div class="form-group">
                  <label class="custom-control custom-checkbox">
                    <input type="checkbox" class="custom-control-input" name="active" id="active"' . $checked . '>
                    <span class="custom-control-indicator"></span>
                    <span class="custom-control-description">Active</span>
                  </label>
                </div>
              </div>
              <div class="col-md-4 text-right">
                <div class="btn-group">
                  <button id="editor" name="m" value="create" class="btn btn-primary">Add</button>
                </div>
              </div>
            </div>
              </form>
            <script>
$(".editlink").on("click", function() {
  var row = $(this).closest("tr");
  $("#i").val(row.attr("data-rowid"));
  $("#name").val(row.find("td:eq(0)").text());
  $("#type").val(row.find("td:eq(1)").text());
  $("#content").val(row.find("td:eq(2)").text());
  $("#prio").val(row.find("td:eq(3)").text());
  $("#ttl").val(row.find("td:eq(4)").text());

  if (row.data("active"))
    $("#active").attr("checked", "on");
  else $("#active").removeAttr("checked");

  $("#editor").val("update");
  $("#editor").text("Save");
  $("#editor").attr("class","btn btn-success");

  return false;
});
            </script>';
    }
}

// lib/php/themes/bootstrap/home.php 20150101 - 20170317

class Themes_Bootstrap_Home extends Themes_Bootstrap_Theme
{
    public function list(array $in) : string
    {
        return $in['buf'];
    }
}

// lib/php/themes/bootstrap/vmails.php 20170225

class Themes_Bootstrap_Vmails extends Themes_Bootstrap_Theme
{
    public function create(array $in) : string
    {
        return $this->editor($in);
    }

    public function update(array $in) : string
    {
        return $this->editor($in);
    }

    public function list(array $in) : string
    {
        $buf = $pgr_top = $pgr_end = '';
        $pgr = $in['pager']; unset($in['pager']);
        $adm = util::is_adm();

        if ($pgr['last'] > 1) {
            $pgr_top ='
          <div class="col-md-6">' . $this->pager($pgr) . '
          </div>';
            $pgr_end = '
          <div class="row">
            <div class="col-12">' . $this->pager($pgr) . '
            </div>
          </div>';
        }

        foreach($in as $row) {
            extract($row);
            $active = $active ? 1 : 0;
            list($lhs, $rhs) = explode('@', $user);
            $sql = "
 SELECT mailquota
   FROM vhosts
  WHERE domain = :rhs";

            $maxquota = db::qry($sql, ['rhs' => $rhs], 'col');

            $sql = "
 SELECT user_mail,num_total
   FROM logging
  WHERE name = :user";

            $quota          = db::qry($sql, ['user' => $user], 'one');
            $mailquota      = $quota['user_mail'];
            $messages       = $quota['num_total'] ? $quota['num_total'] : 0;
            $percent        = round((intval($mailquota) / intval($maxquota)) * 100);
            $percent_buf    = $percent > 9 ? $percent.'%' : '';
            $mailquota_buf  = util::numfmt(intval($mailquota), 2);
            $maxquota_buf   = util::numfmt(intval($maxquota), 2);
            $pbar           = $percent >= 90
                ? 'bg-danger'
                : ($percent >= 75 ? 'bg-warning' : '');
            $active_buf     = $active
                ? '<i class="fa fa-check text-success"></i>'
                : '<i class="fa fa-times text-danger"></i>';

            $url = $adm ? '
              <a href="?o=vmails&m=update&i=' . $id . '">' . $user . '</a>' : $user;

            $buf .= '
                  <tr>
                    <td><strong>' . $url . '</strong></td>
                    <td>' . $rhs . '</td>
                    <td class="align-middle">
                      <div class="progress">
                        <div class="progress-bar ' . $pbar . '" role="progressbar" aria-valuenow="' . $percent . '" aria-valuemin="0" aria-valuemax="100" style="width: ' . $percent . '%;">
                          ' . $percent_buf . '
                        </div>
                      </div>
                    </td>
                    <td>' . $mailquota_buf . ' / ' . $maxquota_buf . '</td>
                    <td class="text-right">' . $messages . '</td>
                    <td class="text-right">' . $active_buf . '</td>
                  </tr>';
        }

        if (empty($buf)) $buf .= '
                <tr><td colspan="6" class="text-center">No Records</td></tr>';

//?                <tr class="bg-primary text-white">
        return '
        <div class="row">
          <div class="col-md-6">
            <h3 class="min600">
              <a href="?o=vmails&m=create" title="Add Mailbox">
                <i class="fa fa-envelope fa-fw"></i> Vmails
                <small><i class="fa fa-plus-circle fa-fw"></i></small>
              </a>
            </h3>
          </div>' . $pgr_top . '
        </div>
        <div class="table-responsive">
          <table class="table table-sm min600">
            <thead class="nowrap">
              <tr>
                <th class="min100">UserID</th>
                <th class="min100">Domain</th>
                <th class="min200">Mailbox Quota</th>
                <th class="min150"></th>
                <th class="min50">Msg #</th>
                <th class="min50"></th>
              </tr>
            </thead>
            <tbody>' . $buf . '
            </tbody>
          </table>
        </div>' . $pgr_end;
    }

    function editor(array $in) : string
    {
        extract($in);

        $active = $active ? 1 : 0;
        $checked = $active ? ' checked' : '';
        $passwd1 = $passwd1 ?? '';
        $passwd2 = $passwd2 ?? '';

        $header = $this->g->in['m'] === 'create' ? 'Add Vmail' : 'Update Vmail';
        $submit = $this->g->in['m'] === 'create' ? '
                      <a class="btn btn-secondary" href="?o=vmails&m=list">&laquo; Back</a>
                      <button type="submit" name="m" value="create" class="btn btn-primary">Add Mailbox</button>' : '
                      <a class="btn btn-secondary" href="?o=vmails&m=list">&laquo; Back</a>
                      <a class="btn btn-danger" href="?o=vmails&m=delete&i=' . $this->g->in['i'] . '" title="Remove mailbox" onClick="javascript: return confirm(\'Are you sure you want to remove ' . $user . '?\')">Remove</a>
                      <button type="submit" name="m" value="update" class="btn btn-primary">Update</button>';
        $enable = $this->g->in['m'] === 'create' ? '
                <input type="text" autocorrect="off" autocapitalize="none" class="form-control" name="user" id="user" value="' . $user . '">' : '
                <input type="text" class="form-control" value="' . $user . '" disabled>
                <input type="hidden" name="user" id="user" value="' . $user . '">';

        return '
          <h3 class="min600">
            <a href="?o=vmails&m=list">
              <i class="fa fa-envelope fa-fw"></i> ' . $header . '
            </a>
          </h3>
          <form method="post" action="' . $this->g->cfg['self'] . '">
            <input type="hidden" name="o" value="' . $this->g->in['o'] . '">
            <input type="hidden" name="i" value="' . $this->g->in['i'] . '">
            <div class="row">
              <div class="form-group col-md-4">
                <label for="domain">Email Address</label>' . $enable . '
              </div>
              <div class="form-group col-md-2">
                <label for="quota">Mailbox Quota</label>
                <input type="number" class="form-control" name="quota" id="quota" value="' . intval($quota / 1048576) . '">
              </div>
              <div class="col-md-3">
                <div class="form-group">
                  <label for="passwd1">Password</label>
                  <input type="password" class="form-control" name="passwd1" id="passwd1" value="' . $passwd1 . '">
                </div>
              </div>
              <div class="col-md-3">
                <div class="form-group">
                  <label for="passwd2">Confirm Password</label>
                  <input type="password" class="form-control" name="passwd2" id="passwd2" value="' . $passwd2 . '">
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-2 offset-md-6">
                <div class="form-group">
                  <label class="custom-control custom-checkbox">
                    <input type="checkbox" class="custom-control-input" name="active" id="active"' . $checked . '>
                    <span class="custom-control-indicator"></span>
                    <span class="custom-control-description">Active</span>
                  </label>
                </div>
              </div>
              <div class="col-md-4 text-right">
                <div class="btn-group">' . $submit . '
                </div>
              </div>
            </div>
          </form>';
    }
}

// lib/php/themes/bootstrap/theme.php 20150101 - 20170317

class Themes_Bootstrap_Theme extends Theme
{
    public function css() : string
    {
        return '
    <meta name="msapplication-TileColor" content="#ffffff">
    <meta name="msapplication-TileImage" content="/lib/img/ms-icon-144x144.png">
    <meta name="theme-color" content="#ffffff">
    <link rel="apple-touch-icon" sizes="57x57" href="/lib/img/apple-icon-57x57.png">
    <link rel="apple-touch-icon" sizes="60x60" href="/lib/img/apple-icon-60x60.png">
    <link rel="apple-touch-icon" sizes="72x72" href="/lib/img/apple-icon-72x72.png">
    <link rel="apple-touch-icon" sizes="76x76" href="/lib/img/apple-icon-76x76.png">
    <link rel="apple-touch-icon" sizes="114x114" href="/lib/img/apple-icon-114x114.png">
    <link rel="apple-touch-icon" sizes="120x120" href="/lib/img/apple-icon-120x120.png">
    <link rel="apple-touch-icon" sizes="144x144" href="/lib/img/apple-icon-144x144.png">
    <link rel="apple-touch-icon" sizes="152x152" href="/lib/img/apple-icon-152x152.png">
    <link rel="apple-touch-icon" sizes="180x180" href="/lib/img/apple-icon-180x180.png">
    <link rel="icon" type="image/png" sizes="192x192"  href="/lib/img/android-icon-192x192.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/lib/img/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="96x96" href="/lib/img/favicon-96x96.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/lib/img/favicon-16x16.png">
    <link rel="manifest" href="/lib/img/manifest.json">
    <link href="//fonts.googleapis.com/css?family=Roboto:100,300,400,500,300italic" rel="stylesheet" type="text/css">
    <link href="//maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.6/css/bootstrap.min.css" integrity="sha384-rwoIResjU2yc3z8GV/NPeZWAv56rSmLldC3R/AZzGRnGxQQKnKkoFVhFQhNUwEyJ" rel="stylesheet" crossorigin="anonymous">
    <link href="//maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet" integrity="sha384-wvfXpqpZZVQGK6TAh5PVlGOfQNHSoD2xbE+QkPxCAFlNEevoEH3Sl0sibVcOQVnN" crossorigin="anonymous">
    <script src="//code.jquery.com/jquery-3.2.1.min.js" crossorigin="anonymous"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/tether/1.4.0/js/tether.min.js" integrity="sha384-DztdAPBWPRXSA/3eYEEUWrWCy7G5KFbe8fFjk5JAIxUYHKkDx6Qin1DkWx51bBrb" crossorigin="anonymous"></script>
    <script src="//maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.6/js/bootstrap.min.js" integrity="sha384-vBWWzlZJ8ea9aCX4pEW3rVHjgjt7zpkNpZk+02D9phzyeVkE+jo0ieGizqPLForn" crossorigin="anonymous"></script>
    <script src="/lib/js/bootstrap-table.js"></script>
<!--
    <link href="/lib/css/bootstrap-table.css" rel="stylesheet">
    <script src="/lib/js/bootstrap-table-editable.js"></script>
    <script src="/lib/js/bootstrap-table-export.js""></script>
    <script src="/lib/js/tableExport.js""></script>
    <script src="/lib/js/bootstrap-editable.js"></script>
-->
    <style>
* { transition: 0.2s linear; }
body { font-family: "Roboto", sans-serif; font-size: 17px; font-weight: 300; padding-top: 5rem; }
.nowrap { white-space: nowrap; }
.ellide { overflow: hidden; text-overflow: ellipsis; }
.w100 { width: 100px; }
.w200 { width: 200px; }
.w300 { width: 300px; }
.max100 { max-width:  50px; }
.max100 { max-width: 100px; }
.max100 { max-width: 150px; }
.max200 { max-width: 200px; }
.max300 { max-width: 300px; }
.max600 { max-width: 600px; }
.min50  { min-width:  50px; }
.min100 { min-width: 100px; }
.min150 { min-width: 150px; }
.min200 { min-width: 200px; }
.min300 { min-width: 300px; }
.min600 { min-width: 600px; }
.min900 { min-width: 900px; }
.table-toolbar .search, .fixed-table-toolbar .columns { margin-bottom: 10px; }
.fixed-table-container thead th .sortable {
    cursor: pointer;
    background-position: right;
    background-repeat: no-repeat;
    padding-right: 30px;
}
.fixed-table-container thead th .both {
    background-image: url("data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABMAAAATCAQAAADYWf5HAAAAkElEQVQoz7X QMQ5AQBCF4dWQSJxC5wwax1Cq1e7BAdxD5SL+Tq/QCM1oNiJidwox0355mXnG/DrEtIQ6azioNZQxI0ykPhTQIwhCR+BmBYtlK7kLJYwWCcJA9M4qdrZrd8pPjZWPtOqdRQy320YSV17OatFC4euts6z39GYMKRPCTKY9UnPQ6P+GtMRfGtPnBCiqhAeJPmkqAAAAAElFTkSuQmCC");
}
.fixed-table-container thead th .asc {
    background-image: url("data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABMAAAATCAYAAAByUDbMAAAAZ0lEQVQ4y2NgGLKgquEuFxBPAGI2ahhWCsS/gDibUoO0gPgxEP8H4ttArEyuQYxAPBdqEAxPBImTY5gjEL9DM+wTENuQahAvEO9DMwiGdwAxOymGJQLxTyD+jgWDxCMZRsEoGAVoAADeemwtPcZI2wAAAABJRU5ErkJggg==");
}
.fixed-table-container thead th .desc {
    background-image: url("data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABMAAAATCAYAAAByUDbMAAAAZUlEQVQ4y2NgGAWjYBSggaqGu5FA/BOIv2PBIPFEUgxjB+IdQPwfC94HxLykus4GiD+hGfQOiB3J8SojEE9EM2wuSJzcsFMG4ttQgx4DsRalkZENxL+AuJQaMcsGxBOAmGvopk8AVz1sLZgg0bsAAAAASUVORK5CYII= ");
}
    </style>';
    }

    public function log() : string
    {
        list($lvl, $msg) = util::log();
        return $msg ? '
      <div class="alert alert-' . $lvl . ' alert-dismissible fade show" role="alert">
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>' . $msg . '
      </div>' : '';
    }

    public function head() : string
    {
        return '
    <nav class="navbar navbar-toggleable-md navbar-inverse bg-inverse fixed-top">
      <button class="navbar-toggler navbar-toggler-right" type="button" data-toggle="collapse" data-target="#navbarsExampleDefault" aria-controls="navbarsExampleDefault" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <a class="navbar-brand" href="' . $this->g->cfg['self'] . '" title="Home Page">
        <b><i class="fa fa-server fa-fw"></i> ' . $this->g->out['head'] . '</b>
      </a>
      <div class="collapse navbar-collapse" id="navbarsExampleDefault">
        <ul class="navbar-nav mr-auto">' . $this->g->out['nav1'] . '
        </ul>
        <ul class="navbar-nav">
          <li class="nav-item pull-right">' . $this->g->out['nav3'] . '
          </li>
        </ul>
      </div>
    </nav>';
    }

    public function nav1(array $a = []) : string
    {
        $a = isset($a[0]) ? $a : util::get_nav($this->g->nav1);
        $o = '?o=' . $this->g->in['o'];
        $t = '?t=' . util::ses('t');
        return join('', array_map(function ($n) use ($o, $t) {
            if (is_array($n[1])) return $this->nav_dropdown($n);
            $c = $o === $n[1] || $t === $n[1] ? ' active' : '';
            $i = isset($n[2]) ? '<i class="' . $n[2] . '"></i> ' : '';
            return '
          <li class="nav-item' . $c . '"><a class="nav-link" href="' . $n[1] . '">' . $i . $n[0] . '</a></li>';
        }, $a));
    }

    public function nav2() : string
    {
        return $this->nav_dropdown(['Theme', $this->g->nav2, 'fa fa-th fa-fw']);
    }

    public function nav3() : string
    {
        if (util::is_usr()) {
            $usr[] = ['Change Profile', '?o=accounts&m=update&i=' . $_SESSION['usr']['id'], 'fa fa-user fa-fw'];
            $usr[] = ['Change Password', '?o=auth&m=update&i=' . $_SESSION['usr']['id'], 'fa fa-key fa-fw'];
            $usr[] = ['Sign out', '?o=auth&m=delete', 'fa fa-sign-out fa-fw'];

            if (util::is_adm() && !util::is_acl(0)) $usr[] =
                ['Switch to sysadm', '?o=users&m=switch_user&i=' . $_SESSION['adm'], 'fa fa-user fa-fw'];

            return $this->nav_dropdown([$_SESSION['usr']['login'], $usr, 'fa fa-user fa-fw']);
        } else return '';
    }

    public function nav_dropdown(array $a = []) : string
    {
        $o = '?o=' . $this->g->in['o'];
        $i = isset($a[2]) ? '<i class="' . $a[2] . '"></i> ' : '';
        return '
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" id="dropdown01" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">' . $i . $a[0] . '</a>
            <div class="dropdown-menu" aria-labelledby="dropdown01">'.join('', array_map(function ($n) use ($o) {
            $c = $o === $n[1] ? ' active' : '';
            $i = isset($n[2]) ? '<i class="' . $n[2] . '"></i> ' : '';
            return '
              <a class="dropdown-item" href="' . $n[1] . '">' . $i . $n[0] . '</a>';
        }, $a[1])).'
            </div>
          </li>';
    }

    public function main() : string
    {
        return '
    <main class="container">
      <div class="row">
        <div class="col-12">' . $this->g->out['log'] . $this->g->out['main'] . '
        </div>
      </div>
    </main>';
    }

    protected function pager(array $ary) : string
    {
        extract($ary);

        $b = '';
        $o = util::ses('o');

        for($i = 1; $i <= $last; $i++) $b .= '
              <li class="page-item' . ($i === $curr ? ' active' : '') . '">
                <a class="page-link" href="?o=' . $o . '&m=list&p=' . $i . '">' . $i . '</a>
              </li>';

        return '
          <nav aria-label="Page navigation">
            <ul class="pagination pagination-sm pull-right">
              <li class="page-item' . ($curr === 1 ? ' disabled' : '') . '">
                <a class="page-link" href="?o=' . $o . '&m=list&p=' . $prev . '" aria-label="Previous">
                  <span aria-hidden="true">&laquo;</span>
                  <span class="sr-only">Previous</span>
                </a>
              </li>' . $b . '
              <li class="page-item' . ($curr === $last ? ' disabled' : '') . '">
                <a class="page-link" href="?o=' . $o . '&m=list&p=' . $next . '" aria-label="Next">
                  <span aria-hidden="true">&raquo;</span>
                  <span class="sr-only">Next</span>
                </a>
              </li>
            </ul>
          </nav>';
    }
}

// lib/php/themes/bootstrap/infosys.php 20170225 - 20170513

class Themes_Bootstrap_InfoSys extends Themes_Bootstrap_Theme
{
    public function list(array $in) : string
    {
        extract($in);

        return '
        <div class="row">
          <div class="col-6">
            <h3><i class="fa fa-server fa-fw"></i> System Info</h3>
          </div>
          <div class="col-6">
            <form method="post" class="form-inline pull-right">
              <input type="hidden" id="o" name="o" value="infosys">
              <div class="form-group">
                <button type="submit" class="btn btn-primary"><i class="fa fa-refresh fa-fw" aria-hidden="true"></i> Refresh</button>
              </div>
            </form>
          </div>
        </div>
        <div class="row">
          <div class="col-md-6">
            <br>
            <h5>RAM <small>' . $mem_used . ' / ' . $mem_total . ', ' . $mem_free . ' free</small></h5>
            <div class="progress">
              <div class="progress-bar bg-' . $mem_color . '" role="progressbar" aria-valuenow="' . $mem_pcnt . '"
              aria-valuemin="0" aria-valuemax="100" style="width:' . $mem_pcnt . '%" title="Used Memory">' . $mem_text . '
              </div>
            </div>
            <br>
            <h5>Disk <small>' . $dsk_used . ' / ' . $dsk_total . ', ' . $dsk_free . ' free</small></h5>
            <div class="progress">
              <div class="progress-bar bg-' . $dsk_color . '" role="progressbar" aria-valuenow="' . $dsk_pcnt . '"
              aria-valuemin="0" aria-valuemax="100" style="width:' . $dsk_pcnt . '%" title="Used Disk Space">' . $dsk_text . '
              </div>
            </div>
            <br>
            <h5>CPU <small>' .$cpu_all  . '</small></h5>
            <div class="progress">
              <div class="progress-bar bg-' . $cpu_color . '" role="progressbar" aria-valuenow="' . $cpu_pcnt . '"
              aria-valuemin="0" aria-valuemax="100" style="width:' . $cpu_pcnt . '%" title="Used Disk Space">' . $cpu_text . '
              </div>
            </div>
            <br>
          </div>
          <div class="col-md-6">
            <div class="table-responsive">
              <table class="table table-sm table-bordered">
                <tbody>
                  <tr>
                    <td><b>Hostname</b></td>
                    <td>' .$hostname  . '</td>
                  </tr>
                  <tr>
                    <td><b>Host IP</b></td>
                    <td>' . $host_ip . '</td>
                  </tr>
                  <tr>
                    <td><b>Distro</b></td>
                    <td>' . $os_name . '</td>
                  </tr>
                  <tr>
                    <td><b>Uptime</b></td>
                    <td>' . $uptime . '</td>
                  </tr>
                  <tr>
                    <td><b>CPU Load</b></td>
                    <td>' . $loadav . ' (' . $cpu_num . ' cpus)</td>
                  </tr>
                  <tr>
                    <td><b>CPU Model</b></td>
                    <td>' . $cpu_name . '</td>
                  </tr>
                  <tr>
                    <td><b>Kernel Version</b></td>
                    <td>' . $kernel . '</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-md-12">
            <h5>Process List <small>(' . $proc_num . ')</small></h5>
            <pre>' . $proc_list . '
            </pre>
          </div>
        </div>';
    }
}

// lib/php/themes/bootstrap/domains.php 20170225 - 20170423

class Themes_Bootstrap_Domains extends Themes_Bootstrap_Theme
{
    protected $mns = [
        ['MASTER', 'MASTER'],
        ['NATIVE', 'NATIVE'],
        ['SLAVE',  'SLAVE']
    ];

    public function create(array $in) : string
    {
        return $this->editor($in);
    }

    public function update(array $in) : string
    {
        if ($in['type'] === 'SLAVE') {
            return '
              <form method="post" action="' . $this->g->cfg['self'] . '">
                <div class="col-12">
                  <input type="hidden" name="o" value="' . $this->g->in['o'] . '">
                  <input type="hidden" name="i" value="' . $this->g->in['i'] . '">
                  <input type="hidden" name="m" value="create">
                  <div class="form-group">
                    <label for="domain" class="form-control-label">Domain</label>
                    <input type="text" class="form-control" id="domain" name="domain" value="' . $in['name'] . '">
                  </div>
                  <div class="row">
                    <div class="col-6">
                      <div class="form-group">
                        <label for="type" class="form-control-label">Domain Type</label>
                        <div>
                        ' . $this->dropdown($this->mns, 'type', $in['type'], '', 'custom-select') . '
                        </div>
                      </div>
                    </div>
                    <div class="col-6">
                      <div class="form-group" id="mip-control">
                        <label for="master" class="form-control-label">Master IP</label>
                        <input type="text" class="form-control" id="master" name="master" value="' . $in['master'] . '">
                      </div>
                    </div>
                  </div>
                </div>
                <div class="col-12">
                  <button type="submit" class="btn btn-primary">Update Slave Domain</button>
                </div>
              </form>';
        }

        return $this->editor($in);
    }

    public function list(array $in) : string
    {
      return '
        <div class="row">
          <div class="col-md-6">
            <h3 class="min600">
              <a href="#" title="Add new domain" data-toggle="modal" data-target="#createmodal">
                <i class="fa fa-globe fa-fw"></i> Domains
                <small><i class="fa fa-plus-circle fa-fw"></i></small>
              </a>
            </h3>
          </div>
          <div class="col-md-6">
            <div id="toolbar"></div>
          </div>
        </div>
        <div class="table-responsive">
          <table class="table table-sm min600"
            data-click-to-select="true"
            data-mode="inline"
            data-page-list="[2, 5, 10, 20, 50, 100]"
            data-pagination="true"
            data-search-align="left"
            data-search="true"
            data-show-columns="true"
            data-show-pagination-switch="true"
            data-show-refresh="true"
            data-show-toggle="true"
            data-side-pagination="server"
            data-toggle="table"
            data-toolbar="#toolbar"
            data-url="?o=domains&m=list&x=json"
            >
            <thead>
              <tr>
                <th data-field="name" data-sortable="true" data-formatter="nameFormatter">Name</th>
                <th data-field="type" data-sortable="true" data-align="center">Type</th>
                <th data-field="records" data-sortable="true" data-align="right">Records</th>
                <th data-field="action" data-align="right" data-formatter="actionFormatter">Action</th>
              </tr>
            </thead>
          </table>
        </div>

        <div class="modal fade" id="createmodal" tabindex="-1" role="dialog" aria-labelledby="createmodal" aria-hidden="true">
          <div class="modal-dialog" role="document">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title"">Domain</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>
              <form method="post" action="' . $this->g->cfg['self'] . '">
                <div class="modal-body">
                  <input type="hidden" name="o" value="' . $this->g->in['o'] . '">
                  <input type="hidden" name="i" value="' . $this->g->in['i'] . '">
                  <input type="hidden" name="m" value="create">
                  <div class="form-group">
                    <label for="domain" class="form-control-label">Domain</label>
                    <input type="text" class="form-control" id="domain" name="domain">
                  </div>
                  <div class="row">
                    <div class="col-6">
                      <div class="form-group">
                        <label for="type" class="form-control-label">Domain Type</label>
                        <div>
                        ' . $this->dropdown($this->mns, 'type', '', '', 'custom-select') . '
                        </div>
                      </div>
                    </div>
                    <div class="col-6">
                      <div class="form-group invisible" id="mip-control">
                        <label for="master" class="form-control-label">Master IP</label>
                        <input type="text" class="form-control" id="master" name="master">
                      </div>
                    </div>
                  </div>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                  <button type="submit" class="btn btn-primary">Add New Domain</button>
                </div>
              </form>
            </div>
          </div>
        </div>

        <script>
$("#type").change(function () {
  if (this.value == "SLAVE")
    $("#mip-control").removeClass("invisible");
  else
    $("#mip-control").addClass("invisible");
});

function nameFormatter(value, row, index) {
    return [
        "<a href=\"?o=records&m=update&i=" + row.id + "\" title=\"Update records for " + row.name + "\">",
          "<strong>" + row.name + "</strong>",
       "</a>",
    ].join("");
}
function actionFormatter(value, row, index) {
    return [
        "<a href=\"?o=domains&m=update&i=" + row.id + "\" title=\"Update DNS record ID: " + row.id + "\">",
          "<i class=\"fa fa-pencil fa-fw\"></i>",
       "</a>",
        "<a href=\"?o=domains&m=delete&i=" + row.id + "\" title=\"Remove DNS record ID: " + row.id + "\" onClick=\"javascript: return confirm(\'Are you sure you want to remove record ID: " + row.id + "?\')\">",
          "<i class=\"fa fa-trash fa-fw text-danger\"></i>",
       "</a>",
    ].join("");
}
        </script>

        ';
    }

    public function list_orig(array $in) : string
    {
        $buf = $pgr_top = $pgr_end = '';
        $pgr = $in['pager']; unset($in['pager']);
        $adm = util::is_adm();

        if ($pgr['last'] > 1) {
            $pgr_top ='
          <div class="col-md-6">' . $this->pager($pgr) . '
          </div>';
            $pgr_end = '
          <div class="row">
            <div class="col-12">' . $this->pager($pgr) . '
            </div>
          </div>';
        }

        foreach ($in as $row) {
            extract($row);
            $buf .= '
                <tr>
                  <td class="nowrap">
                    <a href="?o=records&m=update&i=' . $id . '" title="Show item ' . $id . '">
                      <strong>' . $name . '</strong>
                    </a>
                  </td>
                  <td>' . $type . '
                  </td>
                  <td>' . $records . '
                  </td>
                  <td class="text-right">
                    <a href="?o=domains&m=update&i=' . $id . '" title="Edit SOA: ' . $id . '">
                      <i class="fa fa-pencil fa-fw cursor-pointer"></i>
                    </a>
                    <a href="?o=domains&m=delete&i=' . $id . '" title="Remove DNS record" onClick="javascript: return confirm(\'Are you sure you want to remove: ' . $name . '?\')">
                      <i class="fa fa-trash fa-fw cursor-pointer"></i>
                    </a>
                  </td>
                </tr>';
        }
        if (empty($buf)) $buf .= '
                <tr><td colspan="4" class="text-center">No Domains</td></tr>';

        return '
        <div class="row">
          <div class="col-md-6">
            <h3 class="min600">
              <a href="#" title="Add new domain" data-toggle="modal" data-target="#createmodal">
                <i class="fa fa-globe fa-fw"></i> Domains
                <small><i class="fa fa-plus-circle fa-fw"></i></small>
              </a>
            </h3>
          </div>' . $pgr_top . '
        </div>
        <div class="table-responsive">
          <table class="table table-sm min600">
            <thead>
              <tr>
                <th>Name</th>
                <th>Type</th>
                <th>Records</th>
                <th class="text-right">SOA</th>
              </tr>
            </thead>
            <tbody>' . $buf . '
            </tbody>
          </table>
        </div>' . $pgr_end . '
        <div class="modal fade" id="createmodal" tabindex="-1" role="dialog" aria-labelledby="createmodal" aria-hidden="true">
          <div class="modal-dialog" role="document">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title"">Domain</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>
                <form method="post" action="' . $this->g->cfg['self'] . '">
              <div class="modal-body">
                  <input type="hidden" name="o" value="' . $this->g->in['o'] . '">
                  <input type="hidden" name="i" value="' . $this->g->in['i'] . '">
                  <input type="hidden" name="m" value="create">
                  <div class="form-group">
                    <label for="domain class="form-control-label">Name</label>
                    <input type="text" class="form-control" id="domain" name="domain">
                  </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary">Add New Domain</button>
              </div>
                </form>
            </div>
          </div>
        </div>';
    }

    private function editor(array $in) : string
    {
        $domain = $in['name'];
        $soa = isset($in['soa'])
            ? explode(' ', $in['soa'])
            : ['', '', '', 7200, 540, 604800, 300];

        if ($this->g->in['m'] === 'create') {
            $serial = $hidden = '';
            $header = 'Add Domain';
            $submit = '
                <a class="btn btn-secondary" href="?o=domains&m=list">&laquo; Back</a>
                <button type="submit" id="m" name="m" value="create" class="btn btn-primary">Add Domain</button>';
        } else {
            $serial = '&nbsp;&nbsp;<small>Serial: ' . $soa[2] . '</small>';
            $header = $domain;
            $submit = '
                <a class="btn btn-secondary" href="?o=domains&m=list">&laquo; Back</a>
                <button type="submit" id="m" name="m" value="update" class="btn btn-primary">Update</button>';
            $hidden = '
            <input type="hidden" name="serial" value="' . $soa[2] . '">';
        }

        return '
          <h3 class="min600">
            <a href="?o=domains&m=list">
              <i class="fa fa-chevron-left fa-fw"></i> ' . $header . '
            </a>' . $serial . '
          </h3>
          <form method="post" action="' . $this->g->cfg['self'] . '">
            <input type="hidden" name="o" value="' . $this->g->in['o'] . '">
            <input type="hidden" name="i" value="' . $this->g->in['i'] . '">' . $hidden . '
            <div class="row">
              <div class="col-md-3">
                <div class="form-group">
                  <label for="primary">Primary</label>
                  <input type="text" class="form-control" id="primary" name="primary" value="' . $soa[0] . '" required>
                </div>
              </div>
              <div class="col-md-3">
                <div class="form-group">
                  <label for="email">Email</label>
                  <input type="text" class="form-control" id="email" name="email" value="' . $soa[1] . '" required>
                </div>
              </div>
              <div class="col-md-1">
                <div class="form-group">
                  <label for="refresh">Refresh</label>
                  <input type="text" class="form-control" id="refresh" name="refresh" value="' . $soa[3] . '" required>
                </div>
              </div>
              <div class="col-md-1">
                <div class="form-group">
                  <label for="retry">Retry</label>
                  <input type="text" class="form-control" id="retry" name="retry" value="' . $soa[4] . '" required>
                </div>
              </div>
              <div class="col-md-2">
                <div class="form-group">
                  <label for="expire">Expire</label>
                  <input type="text" class="form-control" id="expire" name="expire" value="' . $soa[5] . '" required>
                </div>
              </div>
              <div class="col-md-2">
                <div class="form-group">
                  <label for="ttl">TTL</label>
                  <input type="text" class="form-control" id="ttl" name="ttl" value="' . $soa[6] . '" required>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-12 text-right">
                <div class="btn-group">' . $this->dropdown($this->mns, 'type', '', '', 'custom-select') . $submit . '
                </div>
              </div>
            </div>
          </form>';
    }
}

// lib/php/themes/bootstrap/contact.php 20150101 - 20170317

class Themes_Bootstrap_Contact extends Themes_Bootstrap_Theme
{
    public function list(array $in) : string
    {
        return '
        <div class="col-md-4 offset-md-4">
          <h2><i class="fa fa-envelope"></i> Contact us</h2>
          <form action="' . $this->g->cfg['self'] . '" method="post" onsubmit="return mailform(this)">
            <input type="hidden" name="o" value="auth">
            <div class="form-group">
              <label for="subject">Subject</label>
              <input type="text" class="form-control" id="subject" placeholder="Your Subject" required>
            </div>
            <div class="form-group">
              <label for="message">Message</label>
              <textarea class="form-control" id="message" rows="9" placeholder="Your Message" required></textarea>
            </div>
            <div class="form-group">
              <a tabindex="0" role="button" data-toggle="popover" data-trigger="hover" title="Please Note" data-content="Submitting this form will attempt to start your local mail program. If it does not work then you may have to configure your browser to recognize mailto: links."> <i class="fa fa-question-circle fa-fw"></i></a>
              <div class="btn-group pull-right">
                <button class="btn btn-primary" type="submit">Send</button>
              </div>
            </div>
          </form>
        </div>
        <script> $(function() { $("[data-toggle=popover]").popover(); }); </script>' . $in['js'];
    }
}

// lib/php/themes/bootstrap/about.php 20150101 - 20170317

class Themes_Bootstrap_About extends Themes_Bootstrap_Theme
{
    public function list(array $in) : string
    {
        return $in['buf'];
    }
}

// lib/php/themes/bootstrap/auth.php 20150101

class Themes_Bootstrap_Auth extends Themes_Bootstrap_Theme
{
    // forgotpw (create new pw)
    public function create(array $in) : string
    {
        extract($in);

        return '
        <div class="col-md-4 offset-md-4">
          <h2><i class="fa fa-key"></i> Forgot password</h2>
          <form action="' . $this->g->cfg['self'] . '" method="post">
            <input type="hidden" name="o" value="' . $this->g->in['o'] . '">
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-envelope"></i></span>
                <input type="email" name="login" id="login" class="form-control" placeholder="Your Login Email Address" value="' . $login . '" autofocus required>
              </div>
            </div>
            <div class="form-group">
              <a tabindex="0" role="button" data-toggle="popover" data-trigger="hover" title="Please Note" data-content="You will receive an email with further instructions and please note that this only resets the password for this website interface."> <i class="fa fa-question-circle fa-fw"></i></a>
              <div class="btn-group pull-right">
                <a class="btn btn-outline-primary" href="?o=auth">&laquo; Back</a>
                <button class="btn btn-primary" type="submit" name="m" value="create">Send</button>
              </div>
            </div>
          </form>
        </div>
        <script>$(function() { $("[data-toggle=popover]").popover(); });</script>';
    }

    // signin (read current pw)
    public function list(array $in) : string
    {

        extract($in);

        return '
        <div class="col-md-4 offset-md-4">
          <h2><i class="fa fa-sign-in"></i> Sign in</h2>
          <form action="' . $this->g->cfg['self'] . '" method="post">
            <input type="hidden" name="o" value="auth">
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-user fa-fw"></i></span>
                <input type="email" name="login" id="login" class="form-control" placeholder="Your Email Address" value="' . $login . '" required autofocus>
              </div>
            </div>
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-key fa-fw"></i></span>
                <input type="password" name="webpw" id="webpw" class="form-control" placeholder="Your Password" required>
              </div>
            </div>
            <div class="form-group">
              <label class="custom-control custom-checkbox">
                <input type="checkbox" class="custom-control-input" name="remember" id="remember" value="yes">
                <span class="custom-control-indicator"></span>
                <span class="custom-control-description">Remember me on this computer</span>
              </label>
            </div>
            <div class="btn-group pull-right">
              <a class="btn btn-outline-primary" href="?o=auth&m=create">Forgot password</a>
              <button class="btn btn-primary" type="submit" name="m" value="list">Sign in</button>
            </div>
          </form>
        </div>';
    }

    // resetpw (update pw)
    public function update(array $in) : string
    {

        extract($in);

        return '
        <div class="col-md-4 offset-md-4">
          <h2><i class="fa fa-key"></i> Update Password</h2>
          <form action="' . $this->g->cfg['self'] . '" method="post">
            <input type="hidden" name="o" value="auth">
            <input type="hidden" name="id" value="' . $id . '">
            <input type="hidden" name="login" value="' . $login . '">
            <p class="text-center"><b>For ' . $login . '</b></p>
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon"><span class="fa fa-key fa-fw"></span></span>
                <input type="password" name="passwd1" id="passwd1" class="form-control" placeholder="New Password" value="" required autofocus>
              </div>
            </div>
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon"><span class="fa fa-key fa-fw"></span></span>
                <input type="password" name="passwd2" id="passwd2" class="form-control" placeholder="Confirm Password" value="" required>
              </div>
            </div>
            <div class="btn-group pull-right">
              <button class="btn btn-primary" type="submit" name="m" value="update">Update my password</button>
            </div>
          </form>
        </div>';
    }
}

// lib/php/themes/bootstrap/news.php 20170225 - 20170317

class Themes_Bootstrap_News extends Themes_Bootstrap_Theme
{
    public function create(array $in) : string
    {
        return $this->editor($in);
    }

    public function read(array $in) : string
    {
        extract($in);

        $author_buf = $fname && $lname
            ? $fname . ' ' . $lname
            : ($fname && empty($lname) ? $fname : $login);

        return '
          <h3 class="min600">
            <a href="?o=news&m=list" title="Go back to list">
              <i class="fa fa-newspaper-o fa-fw"></i> ' . $title . '
            </a>
          </h3>
          <div class="table-responsive">
            <table class="table min600">
              <tbody>
                <tr>
                  <td>' . nl2br($content) . '</td>
                  <td class="text-center nowrap w200">
                    <small>
                      by <b><a href="?o=accounts&m=update&i=' . $uid . '">' . $author_buf . '</a></b><br>
                      <i>' . util::now($updated) . '</i>
                    </small>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          <div class="row">
            <div class="col-12 text-right">
              <div class="btn-group">
                <a class="btn btn-secondary" href="?o=news&m=list">&laquo; Back</a>
                <a class="btn btn-danger" href="?o=news&m=delete&i=' . $id . '" title="Remove this item" onClick="javascript: return confirm(\'Are you sure you want to remove ' . $title . '?\')">Remove</a>
                <a class="btn btn-primary" href="?o=news&m=update&i=' . $id . '">Update</a>
              </div>
            </div>
          </div>';
    }

    public function update(array $in) : string
    {
        if (!util::is_adm() && ($_SESSION['usr']['id'] !== $in['author'])) {
            util::log('You do not have permissions to update this post');
            return $this->read($in);
        }

        return $this->editor($in);
    }

    public function list(array $in) : string
    {
        $buf = $pgr_top = $pgr_end = '';
        $pgr = $in['pager']; unset($in['pager']);

        if ($pgr['last'] > 1) {
            $pgr_top ='
          <div class="col-md-6">' . $this->pager($pgr) . '
          </div>';
            $pgr_end = '
          <div class="row">
            <div class="col-12">' . $this->pager($pgr) . '
            </div>
          </div>';
        }

        foreach($in as $row) {
            extract($row);
            $author_buf = $fname && $lname
                ? $fname . ' ' . $lname
                : ($fname && empty($lname) ? $fname : $login);
            $buf .= '
                <tr>
                  <td class="nowrap">
                    <a href="?o=news&m=read&i=' . $id . '" title="Show item ' . $id . '">
                      <strong>' . $title . '</strong>
                    </a>
                  </td>
                  <td class="text-center nowrap bg-primary text-white w200" rowspan="2">
                    <small>
                      by <b><a class="text-white" href="?o=accounts&m=update&i=' . $uid . '">' . $author_buf . '</a></b><br>
                      <i>' . util::now($updated) . '</i>
                    </small>
                  </td>
                </tr>
                <tr>
                  <td><p>' . nl2br($content) . '</p></td>
                </tr>';
        }

        return '
        <div class="row">
          <div class="col-md-6">
            <h3 class="min600">
              <a href="?o=news&m=create" title="Add news item">
                <i class="fa fa-newspaper-o fa-fw"></i> News
                <small><i class="fa fa-plus-circle fa-fw"></i></small>
              </a>
            </h3>
          </div>' . $pgr_top . '
        </div>
        <div class="table-responsive">
          <table class="table table-bordered min600">
            <tbody>' . $buf . '
            </tbody>
          </table>
        </div>' . $pgr_end;
    }

    private function editor(array $in) : string
    {
        if ($this->g->in['m'] === 'create') {
            extract($_SESSION['usr']);
            $author = $uid = $id;
            $header = 'Add News';
            $submit = '
                <a class="btn btn-secondary" href="?o=news&m=list">&laquo; Back</a>
                <button type="submit" class="btn btn-primary">Add This Item</button>';
        } else {
            extract($in);
            $header = 'Update News';
            $submit = '
                <a class="btn btn-secondary" href="?o=news&m=read&i=' . $id . '">&laquo; Back</a>
                <a class="btn btn-danger" href="?o=news&m=delete&i=' . $id . '" title="Remove this item" onClick="javascript: return confirm(\'Are you sure you want to remove ' . $title . '?\')">Remove</a>
                <button type="submit" name="i" value="' . $id . '" class="btn btn-primary">Update</button>';
        }

        $author_label = $fname && $lname
            ? $fname . ' ' . $lname
            : ($fname && empty($lname) ? $fname : $login);

        $author_buf = '
                  <p class="form-control-static"><b><a href="?o=accounts&m=update&i=' . $uid . '">' . $author_label . '</a></b></p>';

        return '
          <h3 class="min600">
            <a href="?o=news&m=list">
              <i class="fa fa-newspaper-o fa-fw"></i> ' . $header . '
            </a>
          </h3>
          <form method="post" action="' . $this->g->cfg['self'] . '">
            <input type="hidden" name="o" value="' . $this->g->in['o'] . '">
            <input type="hidden" name="m" value="' . $this->g->in['m'] . '">
            <input type="hidden" name="author" value="' . $uid . '">
            <div class="row">
              <div class="col-md-4">
                <div class="form-group">
                  <label for="title">Title</label>
                  <input type="text" class="form-control" id="title" name="title" value="' . $title . '" required>
                </div>
                <div class="form-group">
                  <label for="author">Author</label>' . $author_buf . '
                </div>
              </div>
              <div class="col-md-8">
                <div class="form-group">
                  <label for="content">Content</label>
                  <textarea class="form-control" id="content" name="content" rows="12" required>' . $content . '</textarea>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-12 text-right">
                <div class="btn-group">' . $submit . '
                </div>
              </div>
            </div>
          </form>';
    }
}

// lib/php/themes/bootstrap/vhosts.php 20170225

class Themes_Bootstrap_Vhosts extends Themes_Bootstrap_Theme
{
    public function create(array $in) : string
    {
        return $this->editor($in);
    }

    public function update(array $in) : string
    {
        return $this->editor($in);
    }

    public function list(array $in) : string
    {
        $buf = $pgr_top = $pgr_end = '';
        $pgr = $in['pager']; unset($in['pager']);
        $adm = util::is_adm();

        if ($pgr['last'] > 1) {
            $pgr_top ='
          <div class="col-md-6">' . $this->pager($pgr) . '
          </div>';
            $pgr_end = '
          <div class="row">
            <div class="col-12">' . $this->pager($pgr) . '
            </div>
          </div>';
        }

        foreach($in as $row) {
            extract($row);

            $sql = "
 SELECT size_mpath, size_wpath, size_upath
   FROM `logging`
  WHERE `name`= :domain AND month = :month";

            $logging = db::qry($sql, ['domain' => $domain, 'month' => date('Ym')], 'one');
            if (is_array($logging)) extract($logging);
            else $size_mpath = $size_wpath = $size_upath = 0;

            $sql = "
 SELECT COUNT(*) FROM `valias`
  WHERE `did`= :did";

            $num_aliases = db::qry($sql, ['did' => $id], 'col');

            $sql = "
 SELECT COUNT(*) FROM `vmails`
  WHERE `did`= :did";

            $num_mailboxes = db::qry($sql, ['did' => $id], 'col');

            $active_icon = (isset($active) && $active)
                ? '<i class="fa fa-check text-success"></i>'
                : '<i class="fa fa-times text-danger"></i>';

            $url = $adm ? '
                  <a href="?o=vhosts&m=update&i=' . $id . '" title="Vhost ID: ' . $id . '">' . $domain . '</a>' : $domain;

            $mail_quota = util::numfmt($mailquota);
            $disk_quota = util::numfmt($diskquota);
            $size_mpath = util::numfmt($size_mpath);
            $size_upath = util::numfmt($size_upath);

            $buf .= '
              <tr id="data">
                <td><strong>' . $url . '</strong></td>
                <td>' . $uname . '</td>
                <td>' . $uid . ':' . $gid . '</td>
                <td>' . $num_aliases . ' / ' . $aliases . '</td>
                <td>' . $num_mailboxes . ' / ' . $mailboxes . '</td>
                <td>' . $size_mpath . ' / ' . $mail_quota . '</td>
                <td>' . $size_upath . ' / ' . $disk_quota . '</td>
                <td>' . $active_icon . '</td>
              </tr>';
        }

        if (empty($buf)) $buf .= '
                <tr><td colspan="8" class="text-center">No Records</td></tr>';

        return '
        <div class="row">
          <div class="col-md-6">
            <h3 class="min600">
              <a href="?o=vhosts&m=create" title="Add Vhost">
                <i class="fa fa-globe fa-fw"></i> Vhosts
                <small><i class="fa fa-plus-circle fa-fw"></i></small>
              </a>
            </h3>
          </div>' . $pgr_top . '
        </div>
        <div class="table-responsive">
          <table class="table table-sm min600">
            <thead class="nowrap">
              <tr>
                <th class="min200">Domain</th>
                <th class="min100">Uname</th>
                <th class="min100">UID:GID</th>
                <th class="min100">Aliases</th>
                <th class="min100">Mailboxes</th>
                <th class="min200">Mail Quota</th>
                <th class="min200">Disk Quota</th>
                <th></th>
              </tr>
            </thead>
            <tbody>' . $buf . '
            </tbody>
          </table>
        </div>' . $pgr_end;
    }

    private function editor(array $in) : string
    {
        extract($in);

        $active = $active ? 1 : 0;
        $header = $this->g->in['m'] === 'create' ? 'Add Vhost' : $domain;
        $submit = $this->g->in['m'] === 'create' ? '
                <a class="btn btn-secondary" href="?o=vhosts&m=list">&laquo; Back</a>
                <button type="submit" name="m" value="create" class="btn btn-primary">Add</button>' : '
                <a class="btn btn-secondary" href="?o=vhosts&m=list">&laquo; Back</a>
                <a class="btn btn-danger" href="?o=vhosts&m=delete&i=' . $this->g->in['i'] . '" title="Remove Vhost" onClick="javascript: return confirm(\'Are you sure you want to remove ' . $domain . '?\')">Remove</a>
                <button type="submit" name="m" value="update" class="btn btn-primary">Update</button>';
        $enable = $this->g->in['m'] === 'create' ? '
                <input type="text" autocorrect="off" autocapitalize="none" class="form-control" name="domain" id="domain" value="' . $domain . '">' : '
                <input type="text" class="form-control" value="' . $domain . '" disabled>
                <input type="hidden" name="domain" id="domain" value="' . $domain . '">';

        $checked = $active ? ' checked' : '';

        return '
          <h3 class="min600">
            <a href="?o=vhosts&m=list">
              <i class="fa fa-globe fa-fw"></i> ' . $header . '
            </a>
          </h3>
          <form method="post" action="' . $this->g->cfg['self'] . '">
            <input type="hidden" name="o" value="' . $this->g->in['o'] . '">
            <input type="hidden" name="i" value="' . $this->g->in['i'] . '">
            <div class="row">
              <div class="form-group col-md-4">
                <label for="domain">Domain</label>' . $enable . '
              </div>
              <div class="form-group col-md-2">
                <label for="aliases">Max Aliases</label>
                <input type="number" class="form-control" name="aliases" id="aliases" value="' . $aliases . '">
              </div>
              <div class="form-group col-md-2">
                <label for="mailboxes">Max Mailboxes</label>
                <input type="number" class="form-control" name="mailboxes" id="mailboxes" value="' . $mailboxes . '">
              </div>
              <div class="form-group col-md-2">
                <label for="mailquota">Mail Quota (MB)</label>
                <input type="number" class="form-control" name="mailquota" id="mailquota" value="' . intval($mailquota / 1048576) . '">
              </div>
              <div class="form-group col-md-2">
                <label for="diskquota">Disk Quota (MB)</label>
                <input type="number" class="form-control" name="diskquota" id="diskquota" value="' . intval($diskquota / 1048576) . '">
              </div>
            </div>
            <div class="row">
              <div class="col-md-2 offset-md-6">
                <div class="form-group">
                  <label class="custom-control custom-checkbox">
                    <input type="checkbox" class="custom-control-input" name="active" id="active"' . $checked . '>
                    <span class="custom-control-indicator"></span>
                    <span class="custom-control-description">Active</span>
                  </label>
                </div>
              </div>
              <div class="col-md-4 text-right">
                <div class="btn-group">' . $submit . '
                </div>
              </div>
            </div>
          </form>';
    }
}

// lib/php/themes/bootstrap/mail/domainaliases.php 20170225

class Themes_Bootstrap_Mail_DomainAlias extends Themes_Bootstrap_Theme
{
    public function create(array $in) : string
    {
        return $this->editor($in);
    }

    public function update(array $in) : string
    {
        return $this->editor($in);
    }

    public function list(array $in) : string
    {
        $buf = $pgr_top = $pgr_end = '';
        $pgr = $in['pager']; unset($in['pager']);

        if ($pgr['last'] > 1) {
            $pgr_top ='
          <div class="col-md-6">' . $this->pager($pgr) . '
          </div>';
            $pgr_end = '
          <div class="row">
            <div class="col-12">' . $this->pager($pgr) . '
            </div>
          </div>';
        }

        foreach($in as $row) {
            extract($row);
            $active_buf = $active
                ? '<i class="fa fa-check text-success"></i>'
                : '<i class="fa fa-times text-danger"></i>';

            $buf .= '
                <tr id="data">
                  <td><a href="?o=mail_domainalias&m=update&i=' . $id . '"><strong>' . $alias_domain . '<strong></a></td>
                  <td>' . $target_domain . '</td>
                  <td>' . $active_buf . '</td>
                </tr>';
        }

        if (empty($buf)) $buf .= '
                <tr>
                  <td colspan="3" class="text-center">No Records</td>
                </tr>';

        return '
        <div class="row">
          <div class="col-md-6">
            <h3 class="min600">
              <a href="?o=mail_domainalias&m=create" title="Add DomainAlias">
                <i class="fa fa-globe fa-fw"></i> Domain Aliases
                <small><i class="fa fa-plus-circle fa-fw"></i></small>
              </a>
            </h3>
          </div>' . $pgr_top . '
        </div>
        <div class="table-responsive">
          <table class="table table-sm min600">
            <thead class="nowrap">
              <tr class="bg-primary text-white">
                <th class="min100">Alias</th>
                <th class="min150">Target Domain</th>
                <th class="min50">Active</th>
              </tr>
            </thead>
            <tbody>' . $buf . '
            </tbody>
          </table>
        </div>' . $pgr_end;
    }

    private function editor(array $in) : string
    {

        extract($in);

        $options = '';
        $checked = $active ? ' checked' : '';
        $tmp =  db::qry("SELECT `domain` FROM `domain`");
        $rows = [];
        foreach($tmp as $r) $rows[] = [$r['domain'], $r['domain']];
        $options = $this->dropdown(
            $rows,
            'target_domain',
            $target_domain,
            'Please select...',
            'custom-select'
        );

        $header = $this->g->in['m'] === 'create' ? 'Add Domain Alias' : 'Update Domain Alias';
        $submit = $this->g->in['m'] === 'create' ? '
                      <a class="btn btn-secondary" href="?o=mail_domainalias&m=list">&laquo; Back</a>
                      <button type="submit" name="m" value="create" class="btn btn-primary">Add Domain Alias</button>' : '
                      <a class="btn btn-secondary" href="?o=mail_domainalias&m=list">&laquo; Back</a>
                      <a class="btn btn-danger" href="?o=mail_domainalias&m=delete&i=' . $id . '" title="Remove domain" onClick="javascript: return confirm(\'Are you sure you want to remove ' . $alias_domain . '?\')">Remove</a>
                      <button type="submit" name="m" value="update" class="btn btn-primary">Update</button>';

        return '
          <h3 class="w30">
            <a href="?o=mail_domainalias&m=list">
              <i class="fa fa-globe fa-fw"></i> ' . $header . '
            </a>
          </h3>
          <form method="post" action="' . $this->g->cfg['self'] . '">
            <input type="hidden" name="o" value="' . $this->g->in['o'] . '">
            <input type="hidden" name="i" value="' . $this->g->in['i'] . '">
            <div class="row">
              <div class="form-group col-md-6">
                <label for="alias_domain">Alias domain</label>
                <textarea class="form-control" rows="4" name="alias_domain" id="alias_domain">' . $alias_domain . '</textarea>
                <p>Valid domain names only (comma-separated)</p>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label class="control-label" for="target_domain">Target domain</label>
                  <br>' . $options . '
                </div>
                <div class="row">
                  <div class="form-group col-md-6">
                    <label class="custom-control custom-checkbox">
                      <input type="checkbox" class="custom-control-input" name="active" id="active"' . $checked . '>
                      <span class="custom-control-indicator"></span>
                      <span class="custom-control-description">Active</span>
                    </label>
                  </div>
                  <div class="col-md-6 text-right">
                    <div class="btn-group">' . $submit . '
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </form>';
    }
}

// lib/php/themes/bootstrap/infomail.php 20170225 - 20170513

class Themes_Bootstrap_InfoMail extends Themes_Bootstrap_Theme
{
    public function list(array $in) : string
    {
        extract($in);

        return '
        <div class="row">
          <div class="col-md-6">
            <h3 class="min300">
              <i class="fa fa-envelope-o fa-fw"></i> MailServer Info
            </h3>
          </div>
          <div class="col-md-6">
            <form method="post" class="form-inline pull-right">
              <label class="mr-sm-2" for="m">
                <i class="fa fa-clock-o fa-fw" aria-hidden="true"></i> ' . $pflog_time . '
              </label>
              <div class="form-group">
                <input type="hidden" id="m" name="m" value="pflog_renew">
                <button type="submit" class="btn btn-primary"><i class="fa fa-refresh fa-fw" aria-hidden="true"></i> Refresh</button>
              </div>
            </form>
          </div>
        </div>
        <div class="row">
          <div class="col-md-12">
            <h5>Mail Queue</h5>
            <pre>' . $mailq . '</pre>
          </div>
        </div>
        <div class="row">
          <div class="col-md-12">
            <pre>' . $pflogs . '
            </pre>
          </div>
        </div>';
    }
}
//            <textarea rows="20" style="font-family:monospace;font-size:9pt;width:100%;">' . $pflogs . '</textarea>

// lib/php/theme.php 20150101 - 20170305

class Theme
{
    private
    $buf = '',
    $in  = [];

    public function __construct($g)
    {
        $this->g = $g;
    }

    public function __toString() : string
    {
        return $this->buf;
    }

    public function log() : string
    {
        list($lvl, $msg) = util::log();
        return $msg ? '
      <p class="alert ' . $lvl . '">' . $msg . '</p>' : '';
    }

    public function nav1() : string
    {
        $o = '?o='.$this->g->in['o'];
        return '
      <nav>' . join('', array_map(function ($n) use ($o) {
            $c = $o === $n[1] ? ' class="active"' : '';
            return '
        <a' . $c . ' href="' . $n[1] . '">' . $n[0] . '</a>';
        }, $this->g->nav1)) . '
      </nav>';
    }

    public function head() : string
    {
        return '
    <header>
      <h1>
        <a href="' . $this->g->cfg['self'] . '">' . $this->g->out['head'] . '</a>
      </h1>' . $this->g->out['nav1'] . '
    </header>';
    }

    public function main() : string
    {
        return '
    <main>' . $this->g->out['log'] . $this->g->out['main'] . '
    </main>';
    }

    public function foot() : string
    {
        return '
    <footer class="text-center">
      <br>
      <p><em><small>' . $this->g->out['foot'] . '</small></em></p>
    </footer>';
    }

    public function end() : string
    {
        return '
    <pre>' . $this->g->out['end'] . '
    </pre>';
    }

    public function html() : string
    {
        extract($this->g->out, EXTR_SKIP);
        return '<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>' . $doc . '</title>' . $css . '
  </head>
  <body>' . $head . $main . $foot . $end . '
  </body>
</html>
';
    }

    public static function dropdown(
        array $ary,
        string $name,
        string $sel = '',
        string $label = '',
        string $class = '',
        string $extra = '') : string
    {
        $opt = $label ? '
          <option value="">' . ucfirst($label) . '</option>' : '';
        $buf = '';
        $c = $class ? ' class="' . $class . '"' : '';
        foreach($ary as $k => $v) {
            $t = str_replace('?t=', '', $v[1]);
            $s = $sel === $t ? ' selected' : '';
            $buf .= '
          <option value="' . $t . '"' . $s . '>' . $v[0] . '</option>';
        }
        return '
        <select' . $c . ' name="' . $name . '" id="' . $name . '"' . $extra . '>' . $opt . $buf . '
        </select>';
    }

    public function __call(string $name, array $args) : string
    {
        return 'Theme::' . $name . '() not implemented';
    }
}

// index.php 20150101 - 20170305

const DS  = DIRECTORY_SEPARATOR;
const INC = __DIR__ . DS . 'lib' . DS . 'php' . DS;

spl_autoload_register(function ($c) {
    $f = INC . str_replace(['\\', '_'], [DS, DS], strtolower($c)) . '.php';
    if (file_exists($f)) include $f;
    else error_log("!!! $f does not exist");
});

echo new Init(new class
{
    public
    $cfg = [
        'email' => 'markc@renta.net',
        'file'  => 'lib' . DS . '.ht_conf.php', // settings override
        'hash'  => 'SHA512-CRYPT',
        'perp'  => 25,
        'self'  => '',
    ],
    $in = [
        'd'     => '',          // Domain (current)
        'g'     => null,        // Group/Category
        'i'     => null,        // Item or ID
        'l'     => '',          // Log (message)
        'm'     => 'list',      // Method (action)
        'o'     => 'home',      // Object (content)
        't'     => 'bootstrap', // Theme
        'x'     => '',          // XHR (request)
        'search'=> '',
        'sort'  => '',
        'order' => 'desc',
        'offset'=> '0',
        'limit' => '10',
    ],
    $out = [
        'doc'   => 'NetServa',
        'css'   => '',
        'log'   => '',
        'nav1'  => '',
        'nav2'  => '',
        'nav3'  => '',
        'head'  => 'NetServa',
        'main'  => 'Error: missing page!',
        'foot'  => 'Copyright (C) 2015-2017 Mark Constable (AGPL-3.0)',
        'end'   => '',
    ],
    $db = [
        'host'  => '127.0.0.1', // DB site
        'name'  => 'sysadm',    // DB name
        'pass'  => 'lib' . DS . '.ht_pw', // MySQL password override
        'path'  => '/var/lib/sqlite/sysadm/sysadm.db', // SQLite DB
        'port'  => '3306',      // DB port
        'sock'  => '',          // '/run/mysqld/mysqld.sock',
        'type'  => 'mysql',     // mysql | sqlite
        'user'  => 'sysadm',    // DB user
    ],
    $nav1 = [
        'non' => [
            ['About',       '?o=about', 'fa fa-info-circle fa-fw'],
            ['Contact',     '?o=contact', 'fa fa-envelope fa-fw'],
            ['News',        '?o=news&p=1', 'fa fa-file-text fa-fw'],
            ['Sign in',     '?o=auth', 'fa fa-sign-in fa-fw'],
        ],
        'usr' => [
            ['News',        '?o=news&p=1', 'fa fa-file-text fa-fw'],
        ],
        'adm' => [
            ['News',        '?o=news&p=1', 'fa fa-file-text fa-fw'],
            ['Admin',       [
                ['Accounts',    '?o=accounts&p=1', 'fa fa-vcard fa-fw'],
                ['Vhosts',      '?o=vhosts&p=1', 'fa fa-globe fa-fw'],
                ['Vmails',      '?o=vmails&p=1', 'fa fa-envelope fa-fw'],
                ['Aliases',     '?o=valias&p=1', 'fa fa-envelope-square fa-fw'],
                ['Domains',     '?o=domains&p=1', 'fa fa-server fa-fw'],
            ], 'fa fa-users fa-fw'],
            ['Stats',       [
                ['Sys Info',    '?o=infosys&p=1', 'fa fa-dashboard fa-fw'],
                ['Mail Info',    '?o=infomail&p=1', 'fa fa-envelope-o fa-fw'],
                ['Mail Graph',    '?o=mailgraph&p=1', 'fa fa-envelope fa-fw'],
            ], 'fa fa-info-circle fa-fw'],
        ],
    ],
    $nav2 = [
    ],
    $dns = [
        'a'     => '127.0.0.1',
        'mx'    => '',
        'ns1'   => 'ns1.',
        'ns2'   => 'ns2.',
        'prio'  => 0,
        'ttl'   => 300,
        'soa'   => [
            'primary' => 'ns1.',
            'email'   => 'admin.',
            'refresh' => 7200,
            'retry'   => 540,
            'expire'  => 604800,
            'ttl'     => 3600,
        ],
        'db' => [
            'host'  => '127.0.0.1', // Alt DNS DB site
            'name'  => 'pdns',      // Alt DNS DB name
            'pass'  => 'lib' . DS . '.ht_dns_pw', // MySQL DNS password override
            'path'  => '/var/lib/sqlite/sysadm/pdns.db', // DNS SQLite DB
            'port'  => '3306',      // Alt DNS DB port
            'sock'  => '',          // '/run/mysqld/mysqld.sock',
            'type'  => '',          // mysql | sqlite | '' to disable
            'user'  => 'pdns',      // Alt DNS DB user
        ],
    ],
    $acl = [
        0 => 'SuperAdmin',
        1 => 'Administrator',
        2 => 'User',
        3 => 'Suspended',
        9 => 'Anonymous',
    ];
});

