<?php
// lib/php/db.php 20150225 - 20170316
// Copyright (C) 2015-2017 Mark Constable <markc@renta.net> (AGPL-3.0)

class Db extends \PDO
{
    public static $dbh = null;
    public static $tbl = null;

    public function __construct(array $dbcfg)
    {
error_log(__METHOD__);

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
error_log(__METHOD__);

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

error_log("create sql = $sql");

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
error_log(__METHOD__);

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
error_log(__METHOD__);

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

error_log("update sql = $sql");

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
error_log(__METHOD__);

        $where_str = '';
        $where_ary = [];
        foreach($where as $k=>$v) {
            $where_str .= " " . $v[0] . " " . $v[1] . " :" . $v[0];
            $where_ary[$v[0]] = $v[2] ;
        }

        $sql = "
 DELETE FROM `" . self::$tbl . "`
  WHERE $where_str";

error_log("delete sql = $sql");

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
error_log(__METHOD__);

error_log("qry sql = $sql");

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
error_log(__METHOD__);

error_log("bvs = ".var_export($ary, true));

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

?>
