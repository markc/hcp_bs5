<?php
// lib/php/ddns.php 20180606 - 20200414
// Copyright (C) 2015-2020 Mark Constable <markc@renta.net> (AGPL-3.0)

//elog(__FILE__.' '.$_SERVER['REMOTE_ADDR']);
//elog(var_export($_REQUEST, true));

// TODO: REFACTOR and test, for now wrap in unused function to isolate namespace

function ddns() {

    define('APIKEY', "../.ht_ddns");

    if (file_exists(APIKEY)) {
        $mykey = trim(file_get_contents(APIKEY));
    } else {
        die("Error: missing " . APIKEY);
    }

    $db = [
        'host'  => '127.0.0.1', // DB site
        'name'  => 'sysadm',    // DB name
        'pass'  => '../.ht_pw', // MySQL password override
        'path'  => '/var/lib/sqlite/sysadm/sysadm.db', // SQLite DB
        'port'  => '3306',      // DB port
        'sock'  => '',          // '/run/mysqld/mysqld.sock',
        'type'  => 'mysql',     // mysql | sqlite
        'user'  => 'sysadm',    // DB user
    ];

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
    }

    function inc_soa(string $soa) : string
    {
        $ary = explode(' ', $soa);
        $ymd = date('Ymd');
        $day = substr($ary[2], 0, 8);
        $rev = substr($ary[2], -2);
        $ary[2] = ($day == $ymd)
            ? "$ymd" . sprintf("%02d", $rev + 1)
            : "$ymd" . "00";
        return implode(' ', $ary);
    }

    if (!isset($_GET['key'])) {
        die("API key not supplied");
    }

    if (!isset($_GET['for'])) {
        die("API domain not supplied");
    }

    $key = htmlentities(trim($_GET['key']), ENT_QUOTES, 'UTF-8');
    $for = htmlentities(trim($_GET['for']), ENT_QUOTES, 'UTF-8');
    $sub = isset($_GET['sub']) ? htmlentities(trim($_GET['sub'])) . '.' : '';
    $msg = isset($_GET['msg']) ? htmlentities(trim($_GET['msg'])) : 'admin@' . $for;

    if ($key !== $mykey) {
        die("API key does not match");
    }

    if (!filter_var(gethostbyname($for . '.'), FILTER_VALIDATE_IP)) {
        die("Invalid domain name: gethostbyname($for)");
    }

    // Okay, let's get this show on the road
    $time = time();
    $date = date('Y-m-d H:i:s');
    $newip = $_SERVER['REMOTE_ADDR'];
    db::$dbh = new db($db);

    $stmt = db::$dbh->prepare("
    SELECT id FROM domains
    WHERE name = :name");

    $res = $stmt->execute(['name' => $for]);

    if (empty($res)) {
        die("Error looking up target domain in domains table");
    }

    $did = $stmt->fetchColumn();

    if (empty($did)) {
        die("'$for' is not in the local DNS domains table");
    }

    $stmt = db::$dbh->prepare("
    SELECT content
    FROM records
    WHERE type='SOA'
        AND domain_id=:did");

    $res = $stmt->execute(['did' => $did]);

    if (empty($res)) {
        die("Error looking up SOA record in records table");
    }

    $oldsoa = $stmt->fetchColumn();

    if (empty($oldsoa)) {
        die("SOA record for '$for' does not exist");
    }

    $newsoa = inc_soa($oldsoa);

    $stmt = db::$dbh->prepare("
    UPDATE records
        SET content=:content,change_date=:change_date
    WHERE type='SOA'
        AND domain_id=:did");

    $res = $stmt->execute(['did' => $did, 'content' => $newsoa, 'change_date' => $time]);

    if (empty($res)) {
        die("Error updating SOA record table");
    }

    $stmt = db::$dbh->prepare("
    UPDATE records
        SET content=:content,change_date=:change_date
    WHERE type='A'
        AND domain_id=:did
        AND name=:name");

    $res = $stmt->execute(['did' => $did, 'name' => $sub.$for, 'content' => $newip, 'change_date' => $time]);

    if (empty($res)) {
        die("Error updating A record in records table");
    }

    mail(
        $msg,
        '[DDNS] ' . $for . '\'s new IP is ' .  $newip,
        "$date -> $newip"
    );

}

?>
