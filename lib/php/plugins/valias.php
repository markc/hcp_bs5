<?php

declare(strict_types=1);

// lib/php/plugins/valias.php 20170225 - 20240904
// Copyright (C) 1995-2024 Mark Constable <markc@renta.net> (AGPL-3.0)

class Plugins_Valias extends Plugin
{
    protected string $tbl = 'valias';

    public array $inp = [
        'aid'       => 1,
        'hid'       => 1,
        'source'    => '',
        'target'    => '',
        'active'    => 0,
    ];

    protected function create(): string
    {
        if (!util::is_post()) {
            return $this->g->t->create($this->inp);
        }

        ['active' => $active, 'source' => $source, 'target' => $target] = $this->inp;
        $active = $active ? 1 : 0;
        $sources = array_map('trim', preg_split("/( |,|;|\n)/", $source));
        $targets = array_map('trim', preg_split("/( |,|;|\n)/", $target));

        if (empty($sources[0])) {
            util::log('Alias source address is empty');
            return $this->g->t->list($this->inp);
        }

        if (empty($targets[0])) {
            util::log('Alias target address is empty');
            return $this->g->t->list($this->inp);
        }

        foreach ($sources as $s) {
            if (empty($s)) continue;

            [$lhs, $rhs] = str_contains($s, '@') ? explode('@', $s) : ['', $s];

            if (!$domain = idn_to_ascii($rhs)) {
                util::log("Invalid source domain: $rhs");
                return $this->g->t->create($this->inp);
            }

            $hid = db::qry('SELECT `id` FROM `vhosts` WHERE `domain` = :domain', ['domain' => $domain], 'col');

            if (!$hid) {
                util::log("$domain does not exist as a local domain");
                return $this->g->t->create($this->inp);
            }

            if (!filter_var($s, FILTER_VALIDATE_EMAIL) && !empty($lhs)) {
                util::log('Alias source address is invalid');
                return $this->g->t->create($this->inp);
            }

            $catchall = db::qry('SELECT 1 FROM `valias` WHERE `source` = :catchall', ['catchall' => "@$domain"], 'col');

            if ($catchall !== 1) {
                $num_results = count(db::qry('SELECT `source` FROM `valias` WHERE `source` = :source', ['source' => $s]));
                if ($num_results) {
                    util::log("$s already exists as an alias");
                    return $this->g->t->create($this->inp);
                }
            }

            $num_results = count(db::qry('SELECT `user` FROM `vmails` WHERE `user` = :source', ['source' => $s]));
            if ($num_results) {
                util::log("$s already exists as a regular mailbox");
                return $this->g->t->create($this->inp);
            }

            foreach ($targets as $t) {
                if (empty($t)) continue;
                
                [$tlhs, $trhs] = explode('@', $t);

                if (!$tdomain = idn_to_ascii($trhs)) {
                    util::log("Invalid target domain: $trhs");
                    return $this->g->t->create($this->inp);
                }

                if (!filter_var($t, FILTER_VALIDATE_EMAIL)) {
                    util::log('Alias target address is invalid');
                    return $this->g->t->create($this->inp);
                }

                if ($catchall !== 1 && $t === $s) {
                    util::log('Alias source and target addresses must not be the same');
                    return $this->g->t->create($this->inp);
                }
            }

            $target = implode(',', $targets);
            $s = filter_var($s, FILTER_VALIDATE_EMAIL) ? $s : "@$domain";

            $sql = 'INSERT INTO `valias` (`active`, `hid`, `source`, `target`, `updated`, `created`) 
                    VALUES (:active, :hid, :source, :target, :updated, :created)';
            
            db::qry($sql, [
                'active'  => $active,
                'hid'     => $hid,
                'source'  => $s,
                'target'  => $target,
                'updated' => date('Y-m-d H:i:s'),
                'created' => date('Y-m-d H:i:s'),
            ]);
        }

        util::log('Alias added', 'success');
        util::ses('p', '', '1');
        util::redirect("{$this->g->cfg['self']}?o={$this->g->in['o']}&m=list");
    }

    protected function read(): string
    {
        return $this->g->t->update(db::read('*', 'id', $this->g->in['i'], '', 'one'));
    }

    protected function update(): string
    {
        if (!util::is_post()) {
            return $this->g->in['i'] ? $this->read() : 'Error updating item';
        }

        ['active' => $active, 'source' => $source, 'target' => $target] = $this->inp;
        $active = $active ? 1 : 0;
        $sources = array_map('trim', preg_split("/( |,|;|\n)/", $source));
        $targets = array_map('trim', preg_split("/( |,|;|\n)/", $target));

        if (empty($sources[0])) {
            util::log('Alias source address is empty');
            return $this->read();
        }

        if (empty($targets[0])) {
            util::log('Alias target address is empty');
            return $this->read();
        }

        foreach ($sources as $s) {
            if (empty($s)) continue;

            [$lhs, $rhs] = str_contains($s, '@') ? explode('@', $s) : ['', $s];

            if (!$domain = idn_to_ascii($rhs)) {
                util::log("Invalid source domain: $rhs");
                return $this->read();
            }

            $hid = db::qry('SELECT `id` FROM `vhosts` WHERE `domain` = :domain', ['domain' => $domain], 'col');

            if (!$hid) {
                util::log("$domain does not exist as a local domain");
                return $this->read();
            }

            if (!filter_var($s, FILTER_VALIDATE_EMAIL) && !empty($lhs)) {
                util::log('Alias source address is invalid');
                return $this->read();
            }

            $catchall = db::qry('SELECT 1 FROM `valias` WHERE `source` = :catchall', ['catchall' => "@$domain"], 'col');

            if ($catchall !== 1) {
                $num_results = count(db::qry('SELECT `user` FROM `vmails` WHERE `user` = :source', ['source' => $s]));
                if ($num_results) {
                    util::log("$s already exists as a regular mailbox");
                    return $this->read();
                }
            }

            foreach ($targets as $t) {
                if (empty($t)) continue;
                
                [$tlhs, $trhs] = explode('@', $t);

                if (!$tdomain = idn_to_ascii($trhs)) {
                    util::log("Invalid target domain: $trhs");
                    return $this->read();
                }

                if (!filter_var($t, FILTER_VALIDATE_EMAIL)) {
                    util::log('Alias target address is invalid');
                    return $this->read();
                }

                if ($catchall !== 1 && $t === $s) {
                    util::log('Alias source and target addresses must not be the same');
                    return $this->read();
                }
            }

            $target = implode(',', $targets);
            $s = filter_var($s, FILTER_VALIDATE_EMAIL) ? $s : "@$domain";

            $exists = count(db::qry('SELECT `source` FROM `valias` WHERE `source` = :source', ['source' => $s]));

            if ($exists || count($sources) == 1) {
                $sql = 'UPDATE `valias` SET `active` = :active, `source` = :source, `target` = :target, `updated` = :updated WHERE `id` = :id';
                db::qry($sql, [
                    'id' => $this->g->in['i'],
                    'active' => $active,
                    'source' => $s,
                    'target' => $target,
                    'updated' => date('Y-m-d H:i:s'),
                ]);
            } else {
                $sql = 'INSERT INTO `valias` (`active`, `hid`, `source`, `target`, `updated`, `created`) 
                        VALUES (:active, :hid, :source, :target, :updated, :created)';
                db::qry($sql, [
                    'active' => $active,
                    'hid' => $hid,
                    'source' => $s,
                    'target' => $target,
                    'updated' => date('Y-m-d H:i:s'),
                    'created' => date('Y-m-d H:i:s'),
                ]);
            }
        }

        util::log('Changes to alias have been saved', 'success');
        util::relist();
    }

    protected function list(): string
    {
        if ($this->g->in['x'] === 'json') {
            $columns = [
                ['dt' => 0, 'db' => 'source', 'formatter' => fn($d, $row) => "
                    <a href=\"?o=valias&m=update&i={$row['id']}\" class=\"bslink\" title=\"Update entry for $d\">
                      <b>$d</b></a>"],
                ['dt' => 1, 'db' => 'target', 'formatter' => fn($d) => str_replace(',', '<br>', $d)],
                ['dt' => 2, 'db' => 'domain'],
                ['dt' => 3, 'db' => 'active', 'formatter' => fn($d) => "<i class=\"" . ($d ? 'bi-check-lg text-success' : 'bi-x-lg text-danger') . "\"></i>"],
                ['dt' => 4, 'db' => 'id'],
                ['dt' => 5, 'db' => 'updated'],
            ];

            return json_encode(db::simple($_GET, 'valias_view', 'id', $columns), JSON_PRETTY_PRINT);
        }

        return $this->g->t->list([]);
    }
}
