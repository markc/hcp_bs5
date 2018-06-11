<?php
// lib/php/util.php 20150225 - 20180519
// Copyright (C) 2015-2018 Mark Constable <markc@renta.net> (AGPL-3.0)

class Util
{
    public static function log(string $msg = '', string $lvl = 'danger') : array
    {
error_log(__METHOD__);
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
error_log(__METHOD__."($v)");

        return htmlentities(trim($v), ENT_QUOTES, 'UTF-8');
    }

    public static function esc(array $in) : array
    {
error_log(__METHOD__);

        foreach ($in as $k => $v)
            $in[$k] = isset($_REQUEST[$k]) && !is_array($_REQUEST[$k])
                ?  self::enc($_REQUEST[$k]): $v;
        return $in;
    }

    // TODO please document what $k, $v and $x are for?
    public static function ses(string $k, string $v = '', string $x = null) : string
    {
error_log(__METHOD__."($k, $v, $x)");

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
error_log(__METHOD__);

        if (file_exists($g->cfg['file'])) {
            foreach(include $g->cfg['file'] as $k => $v) {
               $g->$k = array_merge($g->$k, $v);
            }
        }
    }

    public static function exe(string $cmd) : bool
    {
error_log(__METHOD__."($cmd)");

        exec('sudo ' . escapeshellcmd($cmd) . ' 2>&1', $retArr, $retVal);
        util::log('<pre>' . trim(implode("\n", $retArr)) . '</pre>', $retVal ? 'danger' : 'success');
        return $retVal;
    }

    public static function now(string $date1, string $date2 = null) : string
    {
error_log(__METHOD__);

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
error_log(__METHOD__);

        return isset($_SESSION['adm']);
    }

    public static function is_usr(int $id = null) : bool
    {
error_log(__METHOD__);

        return (is_null($id))
            ? isset($_SESSION['usr'])
            : isset($_SESSION['usr']['id']) && $_SESSION['usr']['id'] == $id;
    }

    public static function is_acl(int $acl) : bool
    {
error_log(__METHOD__);

        return isset($_SESSION['usr']['acl']) && $_SESSION['usr']['acl'] == $acl;
    }

    public static function genpw(int $length = 10) : string
    {
error_log(__METHOD__);

        return str_replace('.', '_',
            substr(password_hash((string)time(), PASSWORD_DEFAULT),
                rand(10, 50), $length));
    }

    public static function get_nav(array $nav = []) : array
    {
error_log(__METHOD__);

        return isset($_SESSION['usr'])
            ? (isset($_SESSION['adm']) ? $nav['adm'] : $nav['usr'])
            : $nav['non'];
    }

    public static function get_cookie(string $name, string $default='') : string
    {
error_log(__METHOD__);

        return $_COOKIE[$name] ?? $default;
    }

    public static function put_cookie(string $name, string $value, int $expiry=604800) : string
    {
error_log(__METHOD__);

        return setcookie($name, $value, time() + $expiry) ? $value : '';
    }

    public static function del_cookie(string $name) : string
    {
error_log(__METHOD__);

        return self::put_cookie($name, '', time() - 1);
    }

    public static function chkpw(string $pw, string $pw2) : bool
    {
error_log(__METHOD__);

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

    public static function remember(object $g) : void
    {
error_log(__METHOD__);

        if (self::is_usr())
            return;

        if (!($c = self::get_cookie('remember')))
            return;

        if (is_null(db::$dbh))
            db::$dbh = new db($g->db);

        db::$tbl = 'cookies';
        if(!($cookie = db::read('id,accounts_id,token,expire', 'token', $c, '', 'one'))){
            self::del_cookie('remember');
            return;
        }

        if(strtotime($cookie['expire']) < time()){
            db::delete([['id', '=', $cookie['id']]]);
            self::del_cookie('remember');
            return;
        }

        $accounts_id = (int)$cookie['accounts_id'];
        db::$tbl = 'accounts';
        if ($usr = db::read('id,grp,acl,login,fname,lname', 'id', $accounts_id, '', 'one')) {
            extract($usr);
            $_SESSION['usr'] = $usr;
            if ($acl == 0) $_SESSION['adm'] = $id;
            self::log($login . ' is remembered and logged back in', 'success');
            self::ses('o', '', $g->in['o']);
            self::ses('m', '', $g->in['m']);
        }
    }

    public static function redirect(string $url, string $method = 'location', int $ttl = 5, string $msg = '') : void
    {
error_log(__METHOD__);
        if ($method == 'refresh'){
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

    public static function numfmt(float $size, int $precision = null) : string
    {
error_log(__METHOD__);

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
error_log(__METHOD__);

        if ($size == 0) return "0";
        $base = log($size, 1024);
        $suffixes = [' Bytes', ' KiB', ' MiB', ' GiB', ' TiB'];
        return round(pow(1024, $base - floor($base)), $precision) . $suffixes[floor($base)];
    }

    public static function is_valid_domain_name(string $domainname) : bool
    {
error_log(__METHOD__);

        $domainname = idn_to_ascii($domainname);
        return (preg_match("/^([a-z\d](-*[a-z\d])*)(\.([a-z\d](-*[a-z\d])*))*$/i", $domainname)
              && preg_match("/^.{1,253}$/", $domainname)
              && preg_match("/^[^\.]{1,63}(\.[^\.]{1,63})*$/", $domainname));
    }

    public static function mail_password(string $pw, string $hash = 'SHA512-CRYPT') : string
    {
error_log(__METHOD__);

        $salt_str = bin2hex(openssl_random_pseudo_bytes(8));
        return $hash === 'SHA512-CRYPT'
            ? '{SHA512-CRYPT}' . crypt($pw, '$6$' . $salt_str . '$')
            : '{SSHA256}' . base64_encode(hash('sha256', $pw . $salt_str, true) . $salt_str);
    }

    public static function sec2time(int $seconds) : string
    {
error_log(__METHOD__);

        $dtF = new \DateTime('@0');
        $dtT = new \DateTime("@$seconds");
        return $dtF->diff($dtT)->format('%a days, %h hours, %i mins and %s secs');
    }

    public static function is_post() : bool
    {
error_log(__METHOD__);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!isset($_POST['c']) || $_SESSION['c'] !== $_POST['c']) {
                self::log('Possible CSRF attack');
                self::redirect('?o=' . $_SESSION['o'] . '&m=list');
            }
            return true;
        }
        return false;
    }

    public static function random_token(int $length = 32) : string
    {
error_log(__METHOD__);

        $random_base64 = base64_encode(random_bytes($length));
        $random_base64 = str_replace(['+', '/', '='], '', $random_base64);

        if (strlen($random_base64) < $length) {
            /**
             * It happens sometimes that there are many +=/, so if
             * the length of $random_base64 after suppressing thoses
             * characters, is less than the $length, then start over again.
             */
            return self::random_token($length);
        }

        return substr($random_base64, 0, $length);
    }

    public static function session_start(array $cfg) : bool
    {
error_log(__METHOD__);

        /**
         * Default session cookie paramters
         * http://php.net/manual/en/session.configuration.php
         */
        $_sess_cookie_params = session_get_cookie_params();

        $name     = $cfg['name'] ?? session_name();
        $lifetime = $cfg['lifetime'] ?? $_sess_cookie_params['lifetime'];
        $path     = $cfg['path'] ?? $_sess_cookie_params['path'];
        $domain   = $cfg['domain'] ?? $_sess_cookie_params['domain'];
        $secure   = $cfg['secure'] ?? $_sess_cookie_params['secure'];
        $httponly = $cfg['httponly'] ?? $_sess_cookie_params['httponly'];

        session_name($name);
        session_set_cookie_params($lifetime, $path, $domain, $secure, $httponly);
        return session_start();
    }

    public static function inc_soa(string $soa) : string
    {
error_log(__METHOD__);

        $ary = explode(' ', $soa);
        $ymd = date('Ymd');
        $day = substr($ary[2], 0, 8);
        $rev = substr($ary[2], -2);
        $ary[2] = ($day == $ymd)
            ? "$ymd" . sprintf("%02d", $rev + 1)
            : "$ymd" . "00";
        return implode(' ', $ary);
    }

    public static function is_valid_plan(string $plan) : bool
    {
        // See themes/bootstrap/vhosts.php:83
        $valid_plans = ['personal', 'soho', 'business', 'enterprise'];
        return in_array($plan, $valid_plans);
    }
}

?>
