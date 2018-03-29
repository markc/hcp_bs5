<?php
// lib/php/util.php 20150225 - 20170306
// Copyright (C) 2015-2017 Mark Constable <markc@renta.net> (AGPL-3.0)

class Util
{
    public static function log(string $msg = '', string $lvl = 'danger') : array
    {
error_log(__METHOD__);

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
error_log(__METHOD__);

        foreach ($in as $k => $v)
            $in[$k] = isset($_REQUEST[$k])
                ? htmlentities(trim($_REQUEST[$k]), ENT_QUOTES, 'UTF-8') : $v;
        return $in;
    }

    public static function ses(string $k, string $v = '', string $x = null) : string
    {
error_log(__METHOD__."($k, $v, $x)");

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
error_log(__METHOD__);

        if (file_exists($g->cfg['file'])) {
            foreach(include $g->cfg['file'] as $k => $v) {
               $g->$k = array_merge($g->$k, $v);
            }
        }
    }

    public static function now($date1, $date2 = null)
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


    //Not needed with Bootstrap4 DataTables, 20180303

    public static function pager(int $curr, int $perp, int $total) : array
    {
error_log(__METHOD__);

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

    // 09-Auth

    public static function genpw()
    {
error_log(__METHOD__);

        return str_replace('.', '_',
            substr(password_hash((string)time(), PASSWORD_DEFAULT),
                rand(10, 50), 10));
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

        return setcookie($name, $value, time() + $expiry, '/') ? $value : '';
    }

    public static function del_cookie(string $name) : string
    {
error_log(__METHOD__);

        return self::put_cookie($name, '', time() - 1);
    }

    public static function chkpw($pw, $pw2)
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

    public static function remember($g)
    {
error_log(__METHOD__);

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
error_log(__METHOD__);

            header('refresh:' . $ttl . '; url=' . $url);
            echo '<!DOCTYPE html>
<title>Redirect...</title>
<h2 style="text-align:center">Redirecting in ' . $ttl . ' seconds...</h2>
<pre style="width:50em;margin:0 auto;">' . $msg . '</pre>';
    }

    // not used 20180319
    public static function getcfg()
    {
error_log(__METHOD__);

        $ary = $cfg = [];
        $str = shell_exec("sudo rootcat ~/.vhosts/$(hostname -f)");
        $ary = explode("\n", $str);
        foreach($ary as $line) {
            if (empty($line)) continue;
            list($k, $v) = explode('=', $line);
            $cfg[$k] = trim($v, "'");
        }
        return $cfg;
    }

    // mail utilities

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

    public static function is_valid_domain_name($domainname)
    {
error_log(__METHOD__);

        $domainname = idn_to_ascii($domainname);
        return (preg_match("/^([a-z\d](-*[a-z\d])*)(\.([a-z\d](-*[a-z\d])*))*$/i", $domainname)
              && preg_match("/^.{1,253}$/", $domainname)
              && preg_match("/^[^\.]{1,63}(\.[^\.]{1,63})*$/", $domainname));
    }

    public static function mail_password($pw, $hash = 'SHA512-CRYPT')
    {
error_log(__METHOD__);

        $salt_str = bin2hex(openssl_random_pseudo_bytes(8));
        return $hash === 'SHA512-CRYPT'
            ? '{SHA512-CRYPT}' . crypt($pw, '$6$' . $salt_str . '$')
            : '{SSHA256}' . base64_encode(hash('sha256', $pw . $salt_str, true) . $salt_str);
    }

}

?>
