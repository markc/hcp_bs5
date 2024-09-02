<?php

declare(strict_types=1);

// lib/php/util.php 20150225 - 20230712
// Copyright (C) 2015-2023 Mark Constable <markc@renta.net> (AGPL-3.0)

class Util
{

    /**
     * Log a message to the session log array.
     *
     * If $message is given, it is appended to the log array with the given $level.
     * If $message is not given, the log array is returned and cleared.
     *
     * The log array is stored in the $_SESSION['log'] variable. It is an associative array
     * where each key is a log level (e.g. 'danger', 'success', etc.) and the value is an
     * array of messages for that log level.
     *
     * @param string $message The message to log. If empty, the log array is returned.
     * @param string $level The log level. Default is 'danger'.
     * @return array<string, array<string>> The log array if $message is empty.
     */
    public static function log(string $message = '', string $level = 'danger'): array
    {
        // If we're given a message, add it to the log array
        if (!empty($message)) {
            // If the log array doesn't exist, create it
            if (!isset($_SESSION['log'])) {
                $_SESSION['log'] = [];
            }
            // If the log array doesn't have an entry for the given log level, create it
            if (!isset($_SESSION['log'][$level])) {
                $_SESSION['log'][$level] = [];
            }
            // Add the message to the log array for the given log level
            $_SESSION['log'][$level][] = $message;
        } else {
            // If we weren't given a message, return the log array and clear it
            $log = $_SESSION['log'] ?? [];
            $_SESSION['log'] = [];
            return $log;
        }
        // If we were given a message, return an empty array
        return [];
    }


    /**
     * Encodes a string for safe output in HTML.
     *
     * This takes a string, trims it, and then runs it through htmlentities()
     * with the ENT_QUOTES and UTF-8 flags. This is good for preventing XSS
     * attacks when outputting user-supplied data in HTML.
     *
     * @param string $v The string to encode.
     * @return string The encoded string.
     */
    public static function enc(string $v): string
    {
        return \htmlentities(\trim($v), \ENT_QUOTES, 'UTF-8');
    }

    /**
     * Escapes values in an array using enc() if they are also in $_REQUEST.
     *
     * This takes an array of values, and for each key, checks if the same key
     * exists in $_REQUEST and is not an array. If so, it runs the value through
     * the enc() method, which trims it and encodes it for safe output in HTML.
     * If not, it leaves the value alone.
     *
     * This is useful for safely outputting user-supplied data in HTML.
     *
     * @param array<string, mixed> $in The array of values to escape.
     * @return array<string, mixed> The array of escaped values.
     */
    public static function esc(array $in): array
    {
        $request = &$_REQUEST;
        foreach ($in as $k => $v) {
            if (isset($request[$k]) && !is_array($request[$k])) {
                $in[$k] = self::enc((string)$request[$k]);
            }
        }
        return $in;
    }

    // TODO please document what $k, $v and $x are for?
    public static function ses(string $k, string $v = '', string $x = null): string
    {
        return $_SESSION[$k] =
            (!is_null($x) && (!isset($_SESSION[$k]) || ($_SESSION[$k] != $x))) ? $x : (((isset($_REQUEST[$k]) && !isset($_SESSION[$k]))
                || (isset($_REQUEST[$k], $_SESSION[$k])
                    && ($_REQUEST[$k] != $_SESSION[$k])))
                ? self::enc($_REQUEST[$k])
                : ($_SESSION[$k] ?? $v));
    }

    public static function cfg($g): void
    {
        if (file_exists($g->cfg['file'])) {
            foreach (include $g->cfg['file'] as $k => $v) {
                $g->{$k} = array_merge($g->{$k}, $v);
            }
        }
    }

    // util::run should be used instead of this one and move the
    // usage of 'sudo' to the calling method
    public static function exe(string $cmd): bool
    {
        elog(__METHOD__ . "({$cmd})");

        exec('sudo ' . escapeshellcmd($cmd) . ' 2>&1', $retArr, $retVal);
        // class="mb-0" appearance tweak for Bootstrap5 should not be here
        util::log('<pre class="mb-0">' . trim(implode("\n", $retArr)) . '</pre>', $retVal ? 'danger' : 'success');
        return (boolval($retVal) ? true : false);
    }

    /**
     * # Introducing shell exit strategies to trigger Bootstrap5 alerts
     * #
     * # exit 0        - success, no alert and continue
     * # exit 1-250    - error, with 'danger' alert and continue
     * # exit 251      - success, with 'success' alert and continue
     * # exit 252      - info, with 'info' alert and continue
     * # exit 253      - warning, with 'warning' alert and continue
     * # exit 254      - warning, with 'warning' alert and empty content
     * # exit 255      - error, with 'danger' alert and empty content
     * #
     * # 251/252/253 strip the first line to be used in alert message
     */
    public static function run(string $cmd): array
    {
        $rem = $_SESSION['r'];
        $cmd = ($rem && $rem !== 'local') ? "LANG=posix sx $rem $cmd" : $cmd;

        exec(escapeshellcmd("sudo $cmd") . " 2>&1", $retArr, $retVal);

        if (empty($retArr)) {
            util::log("Info: empty result from '" . $cmd . "'", 'info');
        } elseif ($retVal === 255) {
            util::log(implode('\n', $retArr), 'danger');
        } elseif ($retVal === 254) {
            util::log(implode('\n', $retArr), 'warning');
        } elseif ($retVal === 253) {
            util::log(array_shift($retArr), 'warning');
        } elseif ($retVal === 252) {
            util::log(array_shift($retArr), 'info');
        } elseif ($retVal === 251) {
            util::log(array_shift($retArr), 'success');
        } elseif ($retVal > 0) {
            util::log(implode('\n', $retArr), 'danger');
        }
        elog(__METHOD__ . ' cmd=' . $cmd . ' retArr = ' . var_export($retArr, true) . ' retVal = ' . $retVal);

        return ['ary' => array_filter($retArr), 'err' => $retVal];
    }

    public static function now(string $date1, string $date2 = null): string
    {
        if (!is_numeric($date1)) {
            $date1 = strtotime($date1);
        }
        if ($date2 and !is_numeric($date2)) {
            $date2 = strtotime($date2);
        }
        $date2 ??= time();
        $diff = abs($date1 - $date2);
        if ($diff < 10) {
            return ' just now';
        }

        $blocks = [
            ['k' => 'year', 'v' => 31536000],
            ['k' => 'month', 'v' => 2678400],
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

    public static function is_adm(): bool
    {
        return isset($_SESSION['adm']);
    }

    public static function is_usr(int|string $id = null): bool
    {
        return (is_null($id))
            ? isset($_SESSION['usr'])
            : isset($_SESSION['usr']['id']) && $_SESSION['usr']['id'] == $id;
    }

    public static function is_acl(int $acl): bool
    {
        return isset($_SESSION['usr']['acl']) && $_SESSION['usr']['acl'] == $acl;
    }

    public static function genpw(int $length = 10): string
    {
        return str_replace(
            '.',
            '_',
            substr(
                password_hash((string) time(), PASSWORD_DEFAULT),
                random_int(10, 50),
                $length
            )
        );
    }

    public static function get_nav(array $nav = []): array
    {
        return isset($_SESSION['usr'])
            ? (isset($_SESSION['adm']) ? $nav['adm'] : $nav['usr'])
            : $nav['non'];
    }

    public static function get_cookie(string $name, string $default = ''): string
    {
        return $_COOKIE[$name] ?? $default;
    }

    public static function put_cookie(string $name, string $value, int $expiry = 604800): string
    {
        return setcookie($name, $value, time() + $expiry) ? $value : '';
    }

    public static function del_cookie(string $name): string
    {
        return self::put_cookie($name, '', -1);
    }

    public static function chkpw(string $pw, string $pw2 = ''): bool
    {
        if (strlen($pw) > 11) {
            if (preg_match('/[0-9]+/', $pw)) {
                if (preg_match('/[A-Z]+/', $pw)) {
                    if (preg_match('/[a-z]+/', $pw)) {
                        if ($pw2) {
                            if ($pw === $pw2) {
                                return true;
                            }
                            util::log('Passwords do not match, please try again');
                        } else {
                            return true;
                        }
                    } else {
                        util::log('Password must contains at least one lower case letter');
                    }
                } else {
                    util::log('Password must contains at least one captital letter');
                }
            } else {
                util::log('Password must contains at least one number');
            }
        } else {
            util::log('Passwords must be at least 12 characters');
        }
        return false;
    }

    public static function chkapi(object $g): void
    {
        [$apiusr, $apikey] = explode(':', $g->in['a'], 2);

        if (!self::is_usr($apiusr)) { // if this user has already logged in then avoid extra DB lookup
            if (is_null(db::$dbh)) {
                db::$dbh = new db($g->db);
            }
            db::$tbl = 'accounts';

            if ($usr = db::read('id,grp,acl,login,fname,lname,webpw', 'id', $apiusr, '', 'one')) {
                if (9 !== $usr['acl']) {
                    if (password_verify(html_entity_decode($apikey, ENT_QUOTES, 'UTF-8'), $usr['webpw'])) {
                        elog("API login for id={$apiusr}");
                        $_SESSION['usr'] = $usr;
                        if (0 == $usr['acl']) {
                            $_SESSION['adm'] = $apiusr;
                        }
                    } else {
                        exit('Invalid Email Or Password');
                    }
                } else {
                    exit('Account is disabled, contact your System Administrator');
                }
            } else {
                exit('Invalid Email Or Password');
            }
        } else {
            elog("API id={$apiusr} is already logged in");
        }
    }

    public static function remember($g): void
    {
        if (!self::is_usr()) {
            if ($c = self::get_cookie('remember')) {
                if (is_null(db::$dbh)) {
                    db::$dbh = new db($g->db);
                }
                db::$tbl = 'accounts';
                if ($usr = db::read('id,grp,acl,login,fname,lname,cookie', 'cookie', $c, '', 'one')) {
                    extract($usr);
                    $_SESSION['usr'] = $usr;
                    if (0 == $acl) {
                        $_SESSION['adm'] = $id;
                    }
                    self::log($login . ' is remembered and logged back in', 'success');
                    self::ses('o', '', $g->in['o']);
                    self::ses('m', '', $g->in['m']);
                }
            }
        }
    }

    public static function redirect(string $url, string $method = 'location', int $ttl = 5, string $msg = ''): void
    {
        elog(__METHOD__ . "({$url})");

        if ('refresh' == $method) {
            header('refresh:' . $ttl . '; url=' . $url);
            echo '<!DOCTYPE html>
<title>Redirect...</title>
<h2 style="text-align:center">Redirecting in ' . $ttl . ' seconds...</h2>
<pre style="width:50em;margin:0 auto;">' . $msg . '</pre>';
        } else {
            header('Location:' . $url);
        }
        exit;
    }

    public static function relist(string $m = ''): void
    {
        $m = empty($m) ? 'list' : $m;
        self::redirect('?o=' . $_SESSION['o'] . '&m=' . $m);
    }

    public static function numfmt(float $size, int $precision = null): string
    {
        if (0 == $size) {
            return '0';
        }
        if ($size >= 1000000000000) {
            return round(($size / 1000000000000), $precision ?? 3) . ' TB';
        }
        if ($size >= 1000000000) {
            return round(($size / 1000000000), $precision ?? 2) . ' GB';
        }
        if ($size >= 1000000) {
            return round(($size / 1000000), $precision ?? 1) . ' MB';
        }
        if ($size >= 1000) {
            return round(($size / 1000), $precision ?? 0) . ' KB';
        }
        return $size . ' Bytes';
    }

    // numfmt() was wrong, we want MiB, not MB
    public static function numfmtsi(float $size, int $precision = 2): string
    {
        if (0 == $size) {
            return '0';
        }
        $base = log($size, 1024);
        $suffixes = [' Bytes', ' KiB', ' MiB', ' GiB', ' TiB'];
        return round(1024 ** ($base - floor($base)), $precision) . $suffixes[floor($base)];
    }

    public static function is_valid_domain_name(string $domainname): bool
    {
        $domainname = idn_to_ascii($domainname);
        return preg_match('/^([a-z\\d](-*[a-z\\d])*)(\\.([a-z\\d](-*[a-z\\d])*))*$/i', $domainname)
            && preg_match('/^.{1,253}$/', $domainname)
            && preg_match('/^[^\\.]{1,63}(\\.[^\\.]{1,63})*$/', $domainname);
    }

    public static function mail_password(string $pw, string $hash = 'SHA512-CRYPT'): string
    {
        $salt_str = bin2hex(openssl_random_pseudo_bytes(8));
        return 'SHA512-CRYPT' === $hash
            ? '{SHA512-CRYPT}' . crypt($pw, '$6$' . $salt_str . '$')
            : '{SSHA256}' . base64_encode(hash('sha256', $pw . $salt_str, true) . $salt_str);
    }

    public static function sec2time(int $seconds): string
    {
        $dtF = new \DateTime('@0');
        $dtT = new \DateTime("@{$seconds}");
        return $dtF->diff($dtT)->format('%a days, %h hours, %i mins and %s secs');
    }

    public static function is_post(): bool
    {
        if ('POST' === $_SERVER['REQUEST_METHOD']) {
            if (!isset($_POST['c']) || $_SESSION['c'] !== $_POST['c']) {
                self::log('Possible CSRF attack');
                self::redirect('?o=' . $_SESSION['o'] . '&m=list');
            }
            return true;
        }
        return false;
    }

    public static function inc_soa(string $soa): string
    {
        $ary = explode(' ', $soa);
        $ymd = date('Ymd');
        $day = substr($ary[2], 0, 8);
        $rev = substr($ary[2], -2);
        $ary[2] = ($day == $ymd)
            ? "{$ymd}" . sprintf('%02d', $rev + 1)
            : "{$ymd}" . '00';
        return implode(' ', $ary);
    }

    public static function random_token(int $length = 32): string
    {
        $random_base64 = base64_encode(random_bytes($length));
        $random_base64 = str_replace(['+', '/', '='], '', $random_base64);

        if (strlen($random_base64) < $length) {
            return self::random_token($length);
        }
        return substr($random_base64, 0, $length);
    }
}
