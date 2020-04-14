<?php
// lib/php/util.php 20150225 - 20200413
// Copyright (C) 2015-2020 Mark Constable <markc@renta.net> (AGPL-3.0)

class Util
{
    public static function log(string $msg = '', string $lvl = 'danger') : array
    {
elog(__METHOD__);

        if ($msg) {
            $_SESSION['log'][$lvl] = empty($_SESSION['log'][$lvl]) ? $msg : $_SESSION['log'][$lvl] . '<br>' . $msg;
        } elseif (isset($_SESSION['log']) and $_SESSION['log']) {
            $l = $_SESSION['log']; $_SESSION['log'] = [];
            return $l;
        }
        return [];
    }

    public static function enc(string $v) : string
    {
elog(__METHOD__."($v)");

        return htmlentities(trim($v), ENT_QUOTES, 'UTF-8');
    }

    public static function esc(array $in) : array
    {
elog(__METHOD__);

        foreach ($in as $k => $v)
            $in[$k] = isset($_REQUEST[$k]) && !is_array($_REQUEST[$k])
                ?  self::enc($_REQUEST[$k]): $v;
        return $in;
    }

    // TODO please document what $k, $v and $x are for?
    public static function ses(string $k, string $v = '', string $x = null) : string
    {
elog(__METHOD__."($k, $v, $x)");

        return $_SESSION[$k] =
            (!is_null($x) && (!isset($_SESSION[$k]) || ($_SESSION[$k] != $x))) ? $x :
                (((isset($_REQUEST[$k]) && !isset($_SESSION[$k]))
                    || (isset($_REQUEST[$k]) && isset($_SESSION[$k])
                    && ($_REQUEST[$k] != $_SESSION[$k])))
                ? self::enc($_REQUEST[$k])
                : ($_SESSION[$k] ?? $v));
    }

    public static function cfg(object $g) : void
    {
elog(__METHOD__);

        if (file_exists($g->cfg['file'])) {
            foreach(include $g->cfg['file'] as $k => $v) {
               $g->$k = array_merge($g->$k, $v);
            }
        }
    }

    public static function exe(string $cmd, bool $ret = false) : bool
    {
elog(__METHOD__."($cmd)");

        exec('sudo ' . escapeshellcmd($cmd) . ' 2>&1', $retArr, $retVal);
        util::log('<pre>' . trim(implode("\n", $retArr)) . '</pre>', $retVal ? 'danger' : 'success');
        return $retVal;
    }

    public static function run(string $cmd) : string
    {
elog(__METHOD__."($cmd)");

        return exec('sudo ' . escapeshellcmd($cmd) . ' 2>&1');
    }

    public static function now(string $date1, string $date2 = null) : string
    {
elog(__METHOD__);

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

    public static function is_adm() : bool
    {
elog(__METHOD__);

        return isset($_SESSION['adm']);
    }

    public static function is_usr(int $id = null) : bool
    {
elog(__METHOD__);

        return (is_null($id))
            ? isset($_SESSION['usr'])
            : isset($_SESSION['usr']['id']) && $_SESSION['usr']['id'] == $id;
    }

    public static function is_acl(int $acl) : bool
    {
elog(__METHOD__);

        return isset($_SESSION['usr']['acl']) && $_SESSION['usr']['acl'] == $acl;
    }

    public static function genpw(int $length = 10) : string
    {
elog(__METHOD__);

        return str_replace('.', '_',
            substr(password_hash((string)time(), PASSWORD_DEFAULT),
                rand(10, 50), $length));
    }

    public static function get_nav(array $nav = []) : array
    {
elog(__METHOD__);

        return isset($_SESSION['usr'])
            ? (isset($_SESSION['adm']) ? $nav['adm'] : $nav['usr'])
            : $nav['non'];
    }

    public static function get_cookie(string $name, string $default='') : string
    {
elog(__METHOD__);

        return $_COOKIE[$name] ?? $default;
    }

    public static function put_cookie(string $name, string $value, int $expiry=604800) : string
    {
elog(__METHOD__);
        return setcookie($name, $value, time() + $expiry) ? $value : '';
    }

    public static function del_cookie(string $name) : string
    {
elog(__METHOD__);

        return self::put_cookie($name, '', -1);
    }

    public static function chkpw(string $pw, string $pw2 = '') : bool
    {
elog(__METHOD__);

        if (strlen($pw) > 11) {
            if (preg_match('/[0-9]+/', $pw)) {
                if (preg_match('/[A-Z]+/', $pw)) {
                    if (preg_match('/[a-z]+/', $pw)) {
                        if ($pw2) {
                            if ($pw === $pw2) {
                                return true;
                            } else util::log('Passwords do not match, please try again');
                        } else return true;
                    } else util::log('Password must contains at least one lower case letter');
                } else util::log('Password must contains at least one captital letter');
            } else util::log('Password must contains at least one number');
        } else util::log('Passwords must be at least 12 characters');
        return false;
    }

    public static function chkapi(object $g) : void
    {
elog(__METHOD__);

        [$apiusr, $apikey] = explode(':', $g->in['a'], 2);

        if (!self::is_usr($apiusr)) { // if this user has already logged in then avoid extra DB lookup
            if (is_null(db::$dbh)) db::$dbh = new db($g->db);
            db::$tbl = 'accounts';

            if ($usr = db::read('id,grp,acl,login,fname,lname,webpw', 'id', $apiusr, '', 'one')) {
                if ($usr['acl'] !== 9) {
                    if (password_verify(html_entity_decode($apikey, ENT_QUOTES, 'UTF-8'), $usr['webpw'])) {
elog("API login for id=$apiusr");
                        $_SESSION['usr'] = $usr;
                        if ($usr['acl'] == 0) $_SESSION['adm'] = $apiusr;
                    } else die('Invalid Email Or Password');
                } else die('Account is disabled, contact your System Administrator');
            } else die('Invalid Email Or Password');
        } else {
elog("API id=$apiusr is already logged in");
        }
    }

    public static function remember(object $g) : void
    {
elog(__METHOD__);

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

    public static function redirect(string $url, string $method = 'location', int $ttl = 5, string $msg = '') : void
    {
elog(__METHOD__."($url)");

        if ($method == 'refresh') {
            header('refresh:' . $ttl . '; url=' . $url);
            echo '<!DOCTYPE html>
<title>Redirect...</title>
<h2 style="text-align:center">Redirecting in ' . $ttl . ' seconds...</h2>
<pre style="width:50em;margin:0 auto;">' . $msg . '</pre>';
        }else{
            header('Location:' . $url);
        }
        exit;
    }

    public static function relist() : void
    {
elog(__METHOD__);

        self::redirect('?o=' . $_SESSION['o'] . '&m=list');
    }

    public static function numfmt(float $size, int $precision = null) : string
    {
elog(__METHOD__);

        if ($size == 0) return '0';
        if ($size >= 1000000000000) return round(($size / 1000000000000), $precision??3) . ' TB';
        else if ($size >= 1000000000) return round(($size / 1000000000), $precision??2) . ' GB';
        else if ($size >= 1000000) return round(($size / 1000000), $precision??1) . ' MB';
        else if ($size >= 1000) return round(($size / 1000), $precision??0) . ' KB';
        else return $size . ' Bytes';
    }

    // numfmt() was wrong, we want MB not MiB
    public static function numfmtsi(float $size, int $precision = 2) : string
    {
elog(__METHOD__);

        if ($size == 0) return "0";
        $base = log($size, 1024);
        $suffixes = [' Bytes', ' KiB', ' MiB', ' GiB', ' TiB'];
        return round(pow(1024, $base - floor($base)), $precision) . $suffixes[floor($base)];
    }

    public static function is_valid_domain_name(string $domainname) : bool
    {
elog(__METHOD__);

        $domainname = idn_to_ascii($domainname);
        return (preg_match("/^([a-z\d](-*[a-z\d])*)(\.([a-z\d](-*[a-z\d])*))*$/i", $domainname)
              && preg_match("/^.{1,253}$/", $domainname)
              && preg_match("/^[^\.]{1,63}(\.[^\.]{1,63})*$/", $domainname));
    }

    public static function mail_password(string $pw, string $hash = 'SHA512-CRYPT') : string
    {
elog(__METHOD__);

        $salt_str = bin2hex(openssl_random_pseudo_bytes(8));
        return $hash === 'SHA512-CRYPT'
            ? '{SHA512-CRYPT}' . crypt($pw, '$6$' . $salt_str . '$')
            : '{SSHA256}' . base64_encode(hash('sha256', $pw . $salt_str, true) . $salt_str);
    }

    public static function sec2time(int $seconds) : string
    {
elog(__METHOD__);

        $dtF = new \DateTime('@0');
        $dtT = new \DateTime("@$seconds");
        return $dtF->diff($dtT)->format('%a days, %h hours, %i mins and %s secs');
    }

    public static function is_post() : bool
    {
elog(__METHOD__);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!isset($_POST['c']) || $_SESSION['c'] !== $_POST['c']) {
                self::log('Possible CSRF attack');
                self::redirect('?o=' . $_SESSION['o'] . '&m=list');
            }
            return true;
        }
        return false;
    }

    public static function inc_soa(string $soa) : string
    {
elog(__METHOD__);

        $ary = explode(' ', $soa);
        $ymd = date('Ymd');
        $day = substr($ary[2], 0, 8);
        $rev = substr($ary[2], -2);
        $ary[2] = ($day == $ymd)
            ? "$ymd" . sprintf("%02d", $rev + 1)
            : "$ymd" . "00";
        return implode(' ', $ary);
    }

    public static function random_token(int $length = 32) : string
    {
elog(__METHOD__);

        $random_base64 = base64_encode(random_bytes($length));
        $random_base64 = str_replace(['+', '/', '='], '', $random_base64);

        if (strlen($random_base64) < $length) {
            return self::random_token($length);
        }

        return substr($random_base64, 0, $length);
    }
}

?>
