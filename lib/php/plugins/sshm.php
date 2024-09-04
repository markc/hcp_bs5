<?php

declare(strict_types=1);

// lib/php/plugins/sshm.php 20230703 - 20240904
// Copyright (C) 2015-2024 Mark Constable <markc@renta.net> (AGPL-3.0)

class Plugins_Sshm extends Plugin
{
    public array $inp = [
        'name'      => '',
        'host'      => '',
        'port'      => '22',
        'user'      => 'root',
        'skey'      => 'none',
        'key_name'  => '',
        'key_cmnt'  => '',
        'key_pass'  => '',
    ];

    public function create(): ?string
    {
        if (util::is_post()) {
            util::run('sshm create ' . implode(' ', $this->inp));
            util::relist();
            return null;
        }
        
        $keys = util::run('sshm key_list');
        $this->inp['keys'] = $keys['ary'];
        return $this->g->t->create($this->inp);
    }

    public function update(): ?string
    {
        if (util::is_post()) {
            util::run('sshm create ' . implode(' ', $this->inp));
            util::relist();
            return null;
        }
        
        $host = util::run('sshm read ' . $this->inp['name']);
        $inp = array_combine(
            array_keys($this->inp),
            array_map(fn($k, $i) => $host['ary'][$i] ?? '', array_keys($this->inp), array_keys($this->inp))
        );
        $keys = util::run('sshm key_list');
        $inp['keys'] = $keys['ary'];
        return $this->g->t->update($inp);
    }

    public function delete(): ?string
    {
        if (util::is_post()) {
            util::run('sshm delete ' . $this->inp['name']);
            util::relist();
            return null;
        }
        return $this->g->t->delete($this->inp);
    }

    public function list(): string
    {
        return $this->g->t->list(util::run('sshm list'));
    }

    public function help(): string
    {
        return $this->g->t->help(
            $this->inp['name'],
            util::run('sshm help ' . escapeshellarg($this->inp['name']))
        );
    }

    public function key_create(): ?string
    {
        if (util::is_post()) {
            util::run(
                'sshm key_create ' .
                $this->inp['key_name'] . ' ' .
                $this->inp['key_cmnt'] . ' ' .
                $this->inp['key_pass']
            );
            util::relist('key_list');
            return null;
        }
        return $this->g->t->key_create($this->inp);
    }

    protected function key_read(): string
    {
        return $this->g->t->key_read(
            $this->inp['skey'],
            shell_exec('sshm key_read ' . $this->inp['skey'])
        );
    }

    public function key_delete(): ?string
    {
        if (util::is_post()) {
            util::run('sshm key_delete ' . $this->inp['key_name']);
            util::relist('key_list');
            return null;
        }
        return $this->g->t->key_delete($this->inp);
    }

    public function key_list(): string
    {
        return $this->g->t->key_list(util::run('sshm key_list all', $this->g->in['r']));
    }
}
