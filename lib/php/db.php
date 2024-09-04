<?php

declare(strict_types=1);

// lib/php/db.php 20150225 - 20230623
// Copyright (C) 2015-2023 Mark Constable <markc@renta.net> (AGPL-3.0)

class Db extends \PDO
{
    public static ?self $dbh = null;
    public static string $tbl;

    public function __construct(array $dbcfg)
    {
        if (self::$dbh === null) {
            extract($dbcfg);
            $dsn = $type === 'mysql'
                ? "mysql:" . ($sock ? "unix_socket=$sock" : "host=$host;port=$port") . ";dbname=$name"
                : "sqlite:$path";
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

    public static function create(array $ary): string
    {
        $fields = $values = '';
        foreach ($ary as $k => $v) {
            $fields .= "\n                $k,";
            $values .= "\n                :$k,";
        }
        $fields = rtrim($fields, ',');
        $values = rtrim($values, ',');

        $sql = "\n INSERT INTO `" . self::$tbl . "` ($fields)\n VALUES ($values)";

        elog("sql=$sql");

        try {
            $stm = self::$dbh->prepare($sql);
            self::bvs($stm, $ary);
            $stm->execute();
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
        $w = $where ? "\n    WHERE $where = :wval" : '';
        $a = ($wval || $wval === '0') ? ['wval' => $wval] : [];
        $sql = "\n SELECT $field\n   FROM `" . self::$tbl . "`$w $extra";
        elog("sql=$sql");
        return self::qry($sql, $a, $type);
    }

    public static function update(array $set, array $where): bool
    {
        $set_str = implode(",\n        ", array_map(fn($k) => "$k = :$k", array_keys($set)));
        $where_str = implode(' AND ', array_map(fn($v) => "{$v[0]} {$v[1]} :{$v[0]}", $where));
        $where_ary = array_combine(array_column($where, 0), array_column($where, 2));
        $ary = array_merge($set, $where_ary);

        $sql = "\n UPDATE `" . self::$tbl . "` SET\n        $set_str\n  WHERE $where_str";

        elog("sql=$sql");

        try {
            $stm = self::$dbh->prepare($sql);
            self::bvs($stm, $ary);
            return $stm->execute();
        } catch (\PDOException $e) {
            exit(__FILE__ . ' ' . __LINE__ . "<br>\n" . $e->getMessage());
        }
    }

    public static function delete(array $where): bool
    {
        $where_str = implode(' AND ', array_map(fn($v) => "{$v[0]} {$v[1]} :{$v[0]}", $where));
        $where_ary = array_combine(array_column($where, 0), array_column($where, 2));

        $sql = "\n DELETE FROM `" . self::$tbl . "`\n  WHERE $where_str";

        elog("sql=$sql");

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
            if ($type !== 'all') {
                $sql .= ' LIMIT 1';
            }
            $stm = self::$dbh->prepare($sql);
            if ($ary) {
                self::bvs($stm, $ary);
            }
            if ($stm->execute()) {
                $res = match ($type) {
                    'all' => $stm->fetchAll(),
                    'one' => $stm->fetch(),
                    'col' => $stm->fetchColumn(),
                    default => null,
                };
                $stm->closeCursor();
                return $res;
            }
            return false;
        } catch (\PDOException $e) {
            exit(__FILE__ . ' ' . __LINE__ . "<br>\n" . $e->getMessage());
        }
    }

    public static function bvs($stm, array $ary): void
    {
        if ($stm instanceof \PDOStatement) {
            foreach ($ary as $k => $v) {
                $p = match (true) {
                    is_int($v) => \PDO::PARAM_INT,
                    is_bool($v) => \PDO::PARAM_BOOL,
                    is_null($v) => \PDO::PARAM_NULL,
                    is_string($v) => \PDO::PARAM_STR,
                    default => false,
                };
                if ($p !== false) {
                    $stm->bindValue(":$k", $v, $p);
                }
            }
        }
    }

    public static function simple($request, $table, $primaryKey, $columns, $extra = ''): array
    {
        $db = self::$dbh;
        $cols = '`' . implode('`, `', self::pluck($columns, 'db')) . '`';
        $bind = [];

        $limit = self::limit($request, $columns);
        $order = self::order($request, $columns);
        $where = self::filter($request, $columns, $bind);

        if ($extra) {
            $where .= $where ? " AND ($extra)" : " WHERE $extra";
        }

        elog("where=$where");

        $query = "\n SELECT $cols\n   FROM `$table` $where $order $limit";

        $data = self::sql_exec($db, $bind, $query);

        $recordsFiltered = self::sql_exec($db, $bind, "\n SELECT COUNT(`$primaryKey`)\n   FROM `$table` $where", 'col');

        $recordsTotal = self::qry("\n SELECT COUNT(`$primaryKey`)\n   FROM `$table`", [], 'col');

        return [
            'draw' => $request['draw'] ?? 0,
            'recordsTotal' => (int)$recordsTotal,
            'recordsFiltered' => (int)$recordsFiltered,
            'data' => self::data_output($columns, $data),
        ];
    }

    public static function data_output($columns, $data): array
    {
        return array_map(function($d) use ($columns) {
            $row = [];
            foreach ($columns as $column) {
                if (isset($column['formatter'])) {
                    $row[$column['dt']] = $column['formatter']($d[$column['db']] ?? '', $d);
                } elseif ($column['dt'] !== null) {
                    $row[$column['dt']] = $d[$column['db']];
                }
            }
            return $row;
        }, $data);
    }

    public static function limit($request, $columns): string
    {
        return isset($request['start'], $request['length']) && $request['length'] != -1
            ? 'LIMIT ' . (int)$request['start'] . ', ' . (int)$request['length']
            : '';
    }

    public static function order($request, $columns): string
    {
        $order = '';
        if (isset($request['order']) && count($request['order'])) {
            $orderBy = array_filter(array_map(function($ord) use ($request, $columns) {
                $columnIdx = (int)$ord['column'];
                $requestColumn = $request['columns'][$columnIdx];
                $columnIdx = array_search($requestColumn['data'], array_column($columns, 'dt'));
                $column = $columns[$columnIdx];
                if ($requestColumn['orderable'] == 'true') {
                    $dir = $ord['dir'] === 'asc' ? 'ASC' : 'DESC';
                    return "`{$column['db']}` $dir";
                }
                return null;
            }, $request['order']));
            if (count($orderBy)) {
                $order = 'ORDER BY ' . implode(', ', $orderBy);
            }
        }
        return $order;
    }

    public static function filter($request, $columns, &$bindings): string
    {
        $globalSearch = $columnSearch = [];
        $dtColumns = self::pluck($columns, 'dt');

        if (isset($request['search']) && $request['search']['value'] != '') {
            $str = $request['search']['value'];
            for ($i = 0, $ien = count($request['columns']); $i < $ien; $i++) {
                $requestColumn = $request['columns'][$i];
                $columnIdx = array_search($requestColumn['data'], $dtColumns);
                $column = $columns[$columnIdx];
                if ($requestColumn['searchable'] == 'true' && $column['db']) {
                    $binding = self::bind($bindings, "%$str%", \PDO::PARAM_STR);
                    $globalSearch[] = "`{$column['db']}` LIKE $binding";
                }
            }
        }

        if (isset($request['columns'])) {
            foreach ($request['columns'] as $requestColumn) {
                $columnIdx = array_search($requestColumn['data'], $dtColumns);
                $column = $columns[$columnIdx];
                $str = $requestColumn['search']['value'];
                if ($requestColumn['searchable'] == 'true' && $str != '' && $column['db'] !== null) {
                    $binding = self::bind($bindings, "%$str%", \PDO::PARAM_STR);
                    if ($column['db']) {
                        $columnSearch[] = "`{$column['db']}` LIKE $binding";
                    }
                }
            }
        }

        $where = '';
        if (count($globalSearch)) {
            $where = '(' . implode(' OR ', $globalSearch) . ')';
        }
        if (count($columnSearch)) {
            $where = $where === ''
                ? implode(' AND ', $columnSearch)
                : $where . ' AND ' . implode(' AND ', $columnSearch);
        }
        if ($where !== '') {
            $where = 'WHERE ' . $where;
        }

        return $where;
    }

    public static function sql_exec($db, $bindings, $sql = null, string $type = 'all')
    {
        elog("sql=$sql");
        if ($sql === null) {
            $sql = $bindings;
        }
        $stmt = $db->prepare($sql);
        if (is_array($bindings)) {
            foreach ($bindings as $binding) {
                $stmt->bindValue($binding['key'], $binding['val'], $binding['type']);
            }
        }
        try {
            $stmt->execute();
        } catch (PDOException $e) {
            self::fatal('An SQL error occurred: ' . $e->getMessage());
        }
        return match ($type) {
            'all' => $stmt->fetchAll(),
            'both' => $stmt->fetchAll(PDO::FETCH_BOTH),
            'one' => $stmt->fetch(),
            'col' => $stmt->fetchColumn(),
            default => null,
        };
    }

    private static function fatal($msg): void
    {
        echo json_encode(['error' => $msg]);
        exit(0);
    }

    private static function bind(&$a, $val, $type): string
    {
        $key = ':binding_' . count($a);
        $a[] = ['key' => $key, 'val' => $val, 'type' => $type];
        return $key;
    }

    private static function pluck($a, $prop): array
    {
        return array_filter(array_column($a, $prop));
    }

    private static function _flatten($a, $join = ' AND '): string
    {
        if (!$a) {
            return '';
        }
        return is_array($a) ? implode($join, $a) : $a;
    }
}
