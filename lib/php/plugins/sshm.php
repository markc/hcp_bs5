<?php

declare(strict_types=1);
// lib/php/plugins/sshm.php 20230703 - 20230707
// Copyright (C) 2015-2023 Mark Constable <markc@renta.net> (AGPL-3.0)

class Plugins_Sshm extends Plugin
{
    public array $inp = [
        'name'      => '',
        'host'      => '',
        'port'      => '22',
        'user'      => 'root',
        'skey'      => 'none',
    ];

    public function create(): string
    {
        if (util::is_post()) {
            util::run('sshm add ' . implode(' ', $this->inp));
            util::relist();
        } else {
            $keys = util::run('sshm keys');
            $this->inp['keys'] = $keys['ary'];
            return $this->g->t->create($this->inp);
        }
    }

    public function update(): string
    {
        if (util::is_post()) {
            util::run('sshm add ' . implode(' ', $this->inp));
            util::relist();
        } else {
            $host = util::run('sshm host ' . $this->inp['name']);
            $i = 0;
            foreach ($this->inp as $k => $v) {
                $inp[$k] = isset($host['ary'][$i]) ? $host['ary'][$i] : '';
                $i++;
            }
            $keys = util::run('sshm keys');
            $inp['keys'] = $keys['ary'];
            return $this->g->t->update($inp);
        }
    }

    public function delete(): string
    {
        if (util::is_post()) {
            util::run('sshm del ' . escapeshellarg($this->inp['name']));
            util::relist();
        } else {
            return $this->g->t->delete($this->inp);
        }
    }

    public function list(): string
    {
        return $this->g->t->list(util::run('sshm list'));
    }

    protected function shkey(): string
    {
        $skey = escapeshellarg($this->inp['skey'] . '.pub');
        return $this->g->t->shkey($skey, shell_exec('sshm show ' . $skey));
    }
}
