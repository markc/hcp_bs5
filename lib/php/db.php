<?php

declare(strict_types=1);
// lib/php/db.php 20150225 - 20230623
// Copyright (C) 2015-2023 Mark Constable <markc@renta.net> (AGPL-3.0)

class Db extends \PDO
{
    public static $dbh;

    public static $tbl;

    public function __construct(array $dbcfg)
    {
        if (is_null(self::$dbh)) {
            extract($dbcfg);
            $dsn = 'mysql' === $type
                ? 'mysql:' . ($sock ? 'unix_socket=' . $sock : 'host=' . $host . ';port=' . $port) . ';dbname=' . $name
                : 'sqlite:' . $path;
            $pass = file_exists($pass) ? trim(file_get_contents($pass)) : $pass;

            try {
                parent::__construct($dsn, $user, $pass, [
                    \PDO::ATTR_EMULATE_PREPARES => false,
                    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                    \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                ]);
            } catch (\PDOException $e) {
                exit(__FILE__ . ' ' . __LINE__ . "<br>\n" . $e->getMessage());
            }
        }
    }

    public static function create(array $ary)
    {
        $fields = $values = '';
        foreach ($ary as $k => $v) {
            $fields .= "
                {$k},";
            $values .= "
                :{$k},";
        }
        $fields = rtrim($fields, ',');
        $values = rtrim($values, ',');

        $sql = '
 INSERT INTO `' . self::$tbl . "` ({$fields})
 VALUES ({$values})";

        elog("sql={$sql}");

        try {
            $stm = self::$dbh->prepare($sql);
            self::bvs($stm, $ary);
            $res = $stm->execute();

            return self::$dbh->lastInsertId();
        } catch (\PDOException $e) {
            exit(__FILE__ . ' ' . __LINE__ . "<br>\n" . $e->getMessage());
        }
    }

    public static function read(
        string $field,
        string $where = '',
        string $wval = '',
        string $extra = '',
        string $type = 'all'
    ) {
        $w = $where ? "
    WHERE {$where} = :wval" : '';

        $a = ($wval || '0' == $wval) ? ['wval' => $wval] : [];

        $sql = "
 SELECT {$field}
   FROM `" . self::$tbl . "`{$w} {$extra}";

        elog("sql={$sql}");

        return self::qry($sql, $a, $type);
    }

    public static function update(array $set, array $where)
    {
        $set_str = '';
        foreach ($set as $k => $v) {
            $set_str .= "
        {$k} = :{$k},";
        }
        $set_str = rtrim($set_str, ',');

        $where_str = '';
        $where_ary = [];
        foreach ($where as $k => $v) {
            $where_str .= ' ' . $v[0] . ' ' . $v[1] . ' :' . $v[0];
            $where_ary[$v[0]] = $v[2];
        }
        $ary = array_merge($set, $where_ary);

        $sql = '
 UPDATE `' . self::$tbl . "` SET{$set_str}
  WHERE{$where_str}";

        elog("sql={$sql}");

        try {
            $stm = self::$dbh->prepare($sql);
            self::bvs($stm, $ary);

            return $stm->execute();
        } catch (\PDOException $e) {
            exit(__FILE__ . ' ' . __LINE__ . "<br>\n" . $e->getMessage());
        }
    }

    public static function delete(array $where)
    {
        $where_str = '';
        $where_ary = [];
        foreach ($where as $k => $v) {
            $where_str .= ' ' . $v[0] . ' ' . $v[1] . ' :' . $v[0];
            $where_ary[$v[0]] = $v[2];
        }

        $sql = '
 DELETE FROM `' . self::$tbl . "`
  WHERE {$where_str}";

        elog("sql={$sql}");

        try {
            $stm = self::$dbh->prepare($sql);
            self::bvs($stm, $where_ary);

            return $stm->execute();
        } catch (\PDOException $e) {
            exit(__FILE__ . ' ' . __LINE__ . "<br>\n" . $e->getMessage());
        }
    }

    public static function qry(string $sql, array $ary = [], string $type = 'all')
    {
        try {
            if ('all' !== $type) {
                $sql .= ' LIMIT 1';
            }
            $stm = self::$dbh->prepare($sql);
            if ($ary) {
                self::bvs($stm, $ary);
            }
            if ($stm->execute()) {
                $res = null;
                if ('all' === $type) {
                    $res = $stm->fetchAll();
                } elseif ('one' === $type) {
                    $res = $stm->fetch();
                } elseif ('col' === $type) {
                    $res = $stm->fetchColumn();
                }
                $stm->closeCursor();

                return $res;
            }

            return false;
        } catch (\PDOException $e) {
            exit(__FILE__ . ' ' . __LINE__ . "<br>\n" . $e->getMessage());
        }
    }

    // bind value statement
    public static function bvs($stm, array $ary): void
    {
        if (is_object($stm) && ($stm instanceof \PDOStatement)) {
            foreach ($ary as $k => $v) {
                if (is_numeric($v)) {
                    $p = \PDO::PARAM_INT;
                } elseif (is_bool($v)) {
                    $p = \PDO::PARAM_BOOL;
                } elseif (is_null($v)) {
                    $p = \PDO::PARAM_NULL;
                } elseif (is_string($v)) {
                    $p = \PDO::PARAM_STR;
                } else {
                    $p = false;
                }
                if (false !== $p) {
                    $stm->bindValue(":{$k}", $v, $p);
                }
            }
        }
    }

    // See http://datatables.net/usage/server-side
    public static function simple($request, $table, $primaryKey, $columns, $extra = '')
    {
        $db = self::$dbh;
        $cols = '`' . implode('`, `', self::pluck($columns, 'db')) . '`';
        $bind = [];

        $limit = self::limit($request, $columns);
        $order = self::order($request, $columns);
        $where = self::filter($request, $columns, $bind);

        if ($extra) {
            $where .= $where ? " AND ({$extra})" : " WHERE {$extra}";
        }

        elog("where={$where}");

        $query = "
 SELECT {$cols}
   FROM `{$table}` {$where} {$order} {$limit}";

        $data = self::sql_exec($db, $bind, $query);

        $recordsFiltered = self::sql_exec($db, $bind, "
 SELECT COUNT(`{$primaryKey}`)
   FROM `{$table}` {$where}", 'col');

        $recordsTotal = self::qry("
 SELECT COUNT(`{$primaryKey}`)
   FROM `{$table}`", [], 'col');

        return [
            'draw' => isset($request['draw']) ? intval($request['draw']) : 0,
            'recordsTotal' => intval($recordsTotal),
            'recordsFiltered' => intval($recordsFiltered),
            'data' => self::data_output($columns, $data),
        ];
    }

    public static function data_output($columns, $data)
    {
        $out = [];

        for ($i = 0, $ien = count($data); $i < $ien; ++$i) {
            $row = [];

            for ($j = 0, $jen = count($columns); $j < $jen; ++$j) {
                $column = $columns[$j];

                // Is there a formatter?
                if (isset($column['formatter'])) {
                    $row[$column['dt']] = $column['formatter'](($data[$i][$column['db']] ?? ''), $data[$i]);
                } else {
                    if (null !== $column['dt']) {
                        $row[$column['dt']] = $data[$i][$columns[$j]['db']];
                    }
                }
            }

            $out[] = $row;
        }

        return $out;
    }

    public static function limit($request, $columns)
    {
        $limit = '';

        if (isset($request['start']) && -1 != $request['length']) {
            $limit = 'LIMIT ' . intval($request['start']) . ', ' . intval($request['length']);
        }

        return $limit;
    }

    public static function order($request, $columns)
    {
        $order = '';

        if (isset($request['order']) && count($request['order'])) {
            $orderBy = [];
            //            $dtColumns = self::pluck($columns, 'dt');

            for ($i = 0, $ien = count($request['order']); $i < $ien; ++$i) {
                $columnIdx = intval($request['order'][$i]['column']);
                $requestColumn = $request['columns'][$columnIdx];
                //                $columnIdx = array_search($requestColumn['data'], $dtColumns); // don't use $dtColumns
                $columnIdx = array_search($requestColumn['data'], array_column($columns, 'dt'));
                $column = $columns[$columnIdx];

                if ('true' == $requestColumn['orderable']) {
                    $dir = 'asc' === $request['order'][$i]['dir'] ? 'ASC' : 'DESC';
                    $orderBy[] = '`' . $column['db'] . '` ' . $dir;
                }
            }

            if (count($orderBy)) {
                $order = 'ORDER BY ' . implode(', ', $orderBy);
            }
        }

        return $order;
    }

    public static function filter($request, $columns, &$bindings)
    {
        $globalSearch = $columnSearch = [];
        $dtColumns = self::pluck($columns, 'dt');

        if (isset($request['search']) && '' != $request['search']['value']) {
            $str = $request['search']['value'];

            for ($i = 0, $ien = count($request['columns']); $i < $ien; ++$i) {
                $requestColumn = $request['columns'][$i];
                $columnIdx = array_search($requestColumn['data'], $dtColumns);
                $column = $columns[$columnIdx];

                if ('true' == $requestColumn['searchable'] && $column['db']) {
                    $binding = self::bind($bindings, '%' . $str . '%', PDO::PARAM_STR);
                    $globalSearch[] = '`' . $column['db'] . '` LIKE ' . $binding;
                }
            }
        }

        // Individual column filtering
        if (isset($request['columns'])) {
            for ($i = 0, $ien = count($request['columns']); $i < $ien; ++$i) {
                $requestColumn = $request['columns'][$i];
                $columnIdx = array_search($requestColumn['data'], $dtColumns);
                $column = $columns[$columnIdx];

                $str = $requestColumn['search']['value'];

                if ('true' == $requestColumn['searchable'] && '' != $str && null !== $column['db']) {
                    $binding = self::bind($bindings, '%' . $str . '%', PDO::PARAM_STR);
                    if ($column['db']) {
                        $columnSearch[] = '`' . $column['db'] . '` LIKE ' . $binding;
                    }
                }
            }
        }

        // Combine the filters into a single string
        $where = '';

        if (count($globalSearch)) {
            $where = '(' . implode(' OR ', $globalSearch) . ')';
        }

        if (count($columnSearch)) {
            $where = '' === $where ?
                implode(' AND ', $columnSearch) :
                $where . ' AND ' . implode(' AND ', $columnSearch);
        }

        if ('' !== $where) {
            $where = 'WHERE ' . $where;
        }

        return $where;
    }

    public static function sql_exec($db, $bindings, $sql = null, string $type = 'all')
    {
        elog("sql={$sql}");

        // Argument shifting
        if (null === $sql) {
            $sql = $bindings;
        }

        $stmt = $db->prepare($sql);

        // Bind parameters
        if (is_array($bindings)) {
            for ($i = 0, $ien = count($bindings); $i < $ien; ++$i) {
                $binding = $bindings[$i];
                $stmt->bindValue($binding['key'], $binding['val'], $binding['type']);
            }
        }

        // Execute
        try {
            $stmt->execute();
        } catch (PDOException $e) {
            self::fatal('An SQL error occurred: ' . $e->getMessage());
        }

        if ('all' === $type) {
            return $stmt->fetchAll();
        }
        if ('both' === $type) {
            return $stmt->fetchAll(PDO::FETCH_BOTH);
        }
        if ('one' === $type) {
            return $stmt->fetch();
        }
        if ('col' === $type) {
            return $stmt->fetchColumn();
        }
    }

    private static function fatal($msg): void
    {
        echo json_encode(['error' => $msg]);

        exit(0);
    }

    private static function bind(&$a, $val, $type)
    {
        $key = ':binding_' . count($a);
        $a[] = ['key' => $key, 'val' => $val, 'type' => $type];

        return $key;
    }

    private static function pluck($a, $prop)
    {
        $out = [];
        for ($i = 0, $len = count($a); $i < $len; ++$i) {
            if ($a[$i][$prop]) {
                $out[] = $a[$i][$prop];
            }
        }

        return $out;
    }

    private static function _flatten($a, $join = ' AND ')
    {
        if (!$a) {
            return '';
        }
        if ($a && is_array($a)) {
            return implode($join, $a);
        }

        return $a;
    }
}
