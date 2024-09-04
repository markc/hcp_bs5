<?php

declare(strict_types=1);

// lib/php/util.php 20150225 - 20240904
// Copyright (C) 2015-2023 Mark Constable <markc@renta.net> (AGPL-3.0)

class Util
{
    public static function log(string $message = '', string $level = 'danger'): array
    {
        if (!empty($message)) {
            $_SESSION['log'] ??= [];
            $_SESSION['log'][$level] ??= [];
            $_SESSION['log'][$level][] = $message;
            return [];
        }
        
        $log = $_SESSION['log'] ?? [];
        $_SESSION['log'] = [];
        return $log;
    }

    public static function enc(string $v): string
    {
        return \htmlentities(\trim($v), \ENT_QUOTES, 'UTF-8');
    }

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

    public static function ses(string $k, string $v = '', ?string $x = null): string
    {
        return $_SESSION[$k] = match (true) {
            !is_null($x) && (!isset($_SESSION[$k]) || ($_SESSION[$k] != $x)) => $x,
            isset($_REQUEST[$k]) && (!isset($_SESSION[$k]) || $_REQUEST[$k] != $_SESSION[$k]) => self::enc($_REQUEST[$k]),
            default => $_SESSION[$k] ?? $v
        };
    }

    public static function cfg(object $g): void
    {
        if (file_exists($g->cfg['file'])) {
            foreach (include $g->cfg['file'] as $k => $v) {
                $g->{$k} = array_merge($g->{$k}, $v);
            }
        }
    }

    public static function exe(string $cmd): bool
    {
        exec('sudo ' . escapeshellcmd($cmd) . ' 2>&1', $retArr, $retVal);
        self::log('<pre class="mb-0">' . trim(implode("\n", $retArr)) . '</pre>', $retVal ? 'danger' : 'success');
        return $retVal === 0;
    }

    public static function run(string $cmd): array
    {
        $rem = $_SESSION['r'];
        $cmd = ($rem && $rem !== 'local') ? "LANG=posix sx $rem $cmd" : $cmd;

        exec(escapeshellcmd("sudo $cmd") . " 2>&1", $retArr, $retVal);

        match ($retVal) {
            0 => empty($retArr) ? self::log("Info: empty result from '$cmd'", 'info') : null,
            255 => self::log(implode('\n', $retArr), 'danger'),
            254 => self::log(implode('\n', $retArr), 'warning'),
            253, 252, 251 => self::log(array_shift($retArr), match ($retVal) {
                253 => 'warning',
                252 => 'info',
                251 => 'success'
            }),
            default => $retVal > 0 ? self::log(implode('\n', $retArr), 'danger') : null
        };

        return ['ary' => array_filter($retArr), 'err' => $retVal];
    }

    public static function now(string $date1, ?string $date2 = null): string
    {
        $date1 = is_numeric($date1) ? (int)$date1 : strtotime($date1);
        $date2 = $date2 ? (is_numeric($date2) ? (int)$date2 : strtotime($date2)) : time();
        $diff = abs($date1 - $date2);
        
        if ($diff < 10) return ' just now';

        $blocks = [
            ['k' => 'year', 'v' => 31536000],
            ['k' => 'month', 'v' => 2678400],
            ['k' => 'week', 'v' => 604800],
            ['k' => 'day',  'v' => 86400],
            ['k' => 'hour', 'v' => 3600],
            ['k' => 'min',  'v' => 60],
            ['k' => 'sec',  'v' => 1],
        ];
        
        $result = [];
        $current_level = 1;
        
        foreach ($blocks as $block) {
            if ($current_level > 2) break;
            if ($diff / $block['v'] >= 1) {
                $amount = floor($diff / $block['v']);
                $result[] = "$amount {$block['k']}" . ($amount > 1 ? 's' : '');
                $diff -= $amount * $block['v'];
                $current_level++;
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
        return is_null($id) ? isset($_SESSION['usr']) : ($_SESSION['usr']['id'] ?? null) == $id;
    }

    public static function is_acl(int $acl): bool
    {
        return ($_SESSION['usr']['acl'] ?? null) == $acl;
    }

    public static function genpw(int $length = 10): string
    {
        return str_replace('.', '_', substr(password_hash((string) time(), PASSWORD_DEFAULT), random_int(10, 50), $length));
    }

    public static function get_nav(array $nav = []): array
    {
        return match (true) {
            isset($_SESSION['adm']) => $nav['adm'],
            isset($_SESSION['usr']) => $nav['usr'],
            default => $nav['non']
        };
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
        if (strlen($pw) <= 11) {
            self::log('Passwords must be at least 12 characters');
            return false;
        }

        $checks = [
            '/[0-9]+/' => 'Password must contain at least one number',
            '/[A-Z]+/' => 'Password must contain at least one capital letter',
            '/[a-z]+/' => 'Password must contain at least one lowercase letter'
        ];

        foreach ($checks as $pattern => $message) {
            if (!preg_match($pattern, $pw)) {
                self::log($message);
                return false;
            }
        }

        if ($pw2 && $pw !== $pw2) {
            self::log('Passwords do not match, please try again');
            return false;
        }

        return true;
    }

    public static function chkapi(object $g): void
    {
        [$apiusr, $apikey] = explode(':', $g->in['a'], 2);

        if (!self::is_usr($apiusr)) {
            db::$dbh ??= new db($g->db);
            db::$tbl = 'accounts';

            $usr = db::read('id,grp,acl,login,fname,lname,webpw', 'id', $apiusr, '', 'one');
            
            if (!$usr) {
                exit('Invalid Email Or Password');
            }

            if ($usr['acl'] === 9) {
                exit('Account is disabled, contact your System Administrator');
            }

            if (!password_verify(html_entity_decode($apikey, ENT_QUOTES, 'UTF-8'), $usr['webpw'])) {
                exit('Invalid Email Or Password');
            }

            $_SESSION['usr'] = $usr;
            if ($usr['acl'] == 0) {
                $_SESSION['adm'] = $apiusr;
            }
        }
    }

    public static function remember(object $g): void
    {
        if (!self::is_usr() && ($c = self::get_cookie('remember'))) {
            db::$dbh ??= new db($g->db);
            db::$tbl = 'accounts';
            if ($usr = db::read('id,grp,acl,login,fname,lname,cookie', 'cookie', $c, '', 'one')) {
                $_SESSION['usr'] = $usr;
                if ($usr['acl'] == 0) {
                    $_SESSION['adm'] = $usr['id'];
                }
                self::log($usr['login'] . ' is remembered and logged back in', 'success');
                self::ses('o', '', $g->in['o']);
                self::ses('m', '', $g->in['m']);
            }
        }
    }

    public static function redirect(string $url, string $method = 'location', int $ttl = 5, string $msg = ''): never
    {
        if ($method === 'refresh') {
            header("refresh:$ttl;url=$url");
            echo "<!DOCTYPE html><title>Redirect...</title><h2 style=\"text-align:center\">Redirecting in $ttl seconds...</h2><pre style=\"width:50em;margin:0 auto;\">$msg</pre>";
        } else {
            header("Location:$url");
        }
        exit;
    }

    public static function relist(string $m = ''): void
    {
        $m = $m ?: 'list';
        self::redirect("?o={$_SESSION['o']}&m=$m");
    }

    public static function numfmt(float $size, ?int $precision = null): string
    {
        $units = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
        $precision ??= match (true) {
            $size >= 1000000000000 => 3,
            $size >= 1000000000 => 2,
            $size >= 1000000 => 1,
            default => 0
        };
        $unit = min((int)log($size, 1000), count($units) - 1);
        return ($size > 0) ? round($size / (1000 ** $unit), $precision) . ' ' . $units[$unit] : '0';
    }

    public static function numfmtsi(float $size, int $precision = 2): string
    {
        if ($size == 0) return '0';
        $base = log($size, 1024);
        $suffixes = [' Bytes', ' KiB', ' MiB', ' GiB', ' TiB'];
        return round(1024 ** ($base - floor($base)), $precision) . $suffixes[floor($base)];
    }

    public static function is_valid_domain_name(string $domainname): bool
    {
        $domainname = idn_to_ascii($domainname);
        return preg_match('/^([a-z\d](-*[a-z\d])*)(\\.([a-z\d](-*[a-z\d])*))*$/i', $domainname)
            && preg_match('/^.{1,253}$/', $domainname)
            && preg_match('/^[^\\.]{1,63}(\\.[^\\.]{1,63})*$/', $domainname);
    }

    public static function mail_password(string $pw, string $hash = 'SHA512-CRYPT'): string
    {
        $salt_str = bin2hex(random_bytes(8));
        return match ($hash) {
            'SHA512-CRYPT' => '{SHA512-CRYPT}' . crypt($pw, '$6$' . $salt_str . '$'),
            default => '{SSHA256}' . base64_encode(hash('sha256', $pw . $salt_str, true) . $salt_str)
        };
    }

    public static function sec2time(int $seconds): string
    {
        return (new \DateTime('@0'))->diff(new \DateTime("@$seconds"))->format('%a days, %h hours, %i mins and %s secs');
    }

    public static function is_post(): bool
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!isset($_POST['c']) || $_SESSION['c'] !== $_POST['c']) {
                self::log('Possible CSRF attack');
                self::redirect("?o={$_SESSION['o']}&m=list");
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
        $ary[2] = $day == $ymd ? $ymd . sprintf('%02d', (int)$rev + 1) : $ymd . '00';
        return implode(' ', $ary);
    }

    public static function random_token(int $length = 32): string
    {
        $random_base64 = str_replace(['+', '/', '='], '', base64_encode(random_bytes($length)));
        return strlen($random_base64) < $length ? self::random_token($length) : substr($random_base64, 0, $length);
    }
}

