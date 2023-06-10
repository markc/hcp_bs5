<?php

declare(strict_types=1);
// plugins/infosys.php 20170225 - 20200807
// Copyright (C) 2015-2020 Mark Constable <markc@renta.net> (AGPL-3.0)

class Plugins_InfoSys extends Plugin
{
    public function list(): string
    {
        elog(__METHOD__);

        $mem = $dif = $cpu = [];
        $cpu_name = $procs = '';
        $cpu_num = 0;
        $os = 'Unknown OS';

        $pmi = explode("\n", trim(file_get_contents('/proc/meminfo')));
        $loadAvg = sys_getloadavg();
        $lav = sprintf('1 min: %.2f - 5 min: %.2f - 15 min: %.2f', $loadAvg[0], $loadAvg[1], $loadAvg[2]);
        $stat1 = file('/proc/stat');
        sleep(1);
        $stat2 = file('/proc/stat');

        if (is_readable('/proc/cpuinfo')) {
            $tmp = trim(file_get_contents('/proc/cpuinfo'));
            $ret = preg_match_all('/model name.+/', $tmp, $matches);
            $cpu_name = $ret ? explode(': ', $matches[0][0])[1] : 'Unknown CPU';
            $cpu_num = count($matches[0]);
        }

        if (is_readable('/etc/os-release')) {
            $tmp = explode("\n", trim(file_get_contents('/etc/os-release')));
            $osr = [];
            foreach ($tmp as $line) {
                [$k, $v] = explode('=', $line);
                $osr[$k] = trim($v, '" ');
            }
            $os = $osr['PRETTY_NAME'] ?? 'Unknown OS';
        }

        foreach ($pmi as $line) {
            [$k, $v] = explode(':', $line);
            [$mem[$k],] = explode(' ', trim($v));
        }

        $info1 = explode(' ', preg_replace('!cpu +!', '', $stat1[0]));
        $info2 = explode(' ', preg_replace('!cpu +!', '', $stat2[0]));
        $dif['user'] = $info2[0] - $info1[0];
        $dif['nice'] = $info2[1] - $info1[1];
        $dif['sys'] = $info2[2] - $info1[2];
        $dif['idle'] = $info2[3] - $info1[3];
        $total = array_sum($dif);
        foreach ($dif as $x => $y) {
            $cpu[$x] = round($y / $total * 100, 2);
        }
        $cpu_all = sprintf('User: %01.1f - System: %01.1f - Nice: %01.1f - Idle: %01.1f', $cpu['user'], $cpu['sys'], $cpu['nice'], $cpu['idle']);
        $cpu_pcnt = intval(round(100 - $cpu['idle']));

        $dt = (float) disk_total_space('/');
        $df = (float) disk_free_space('/');
        $du = (float) $dt - $df;
        $dp = floor(($du / $dt) * 100);

        $mt = (float) $mem['MemTotal'] * 1000;
        $mu = (float) ($mem['MemTotal'] - $mem['MemFree'] - $mem['Cached'] - $mem['SReclaimable'] - $mem['Buffers']) * 1000;
        $mf = (float) $mt - $mu;
        $mp = floor(($mu / $mt) * 100);

        $ip = gethostbyname(gethostname());
        $hn = gethostbyaddr($ip);
        $knl = is_readable('/proc/version')
            ? explode(' ', trim(file_get_contents('/proc/version')))[2]
            : 'Unknown';

        return $this->t->list([
            'dsk_color' => $dp > 90 ? 'danger' : ($dp > 80 ? 'warning' : 'default'),
            'dsk_free' => util::numfmt($df, 1),
            'dsk_pcnt' => $dp,
            'dsk_text' => $dp > 5 ? $dp . '%' : '',
            'dsk_total' => util::numfmt($dt, 1),
            'dsk_used' => util::numfmt($du, 1),
            'mem_color' => $mp > 90 ? 'danger' : ($mp > 80 ? 'warning' : 'default'),
            'mem_free' => util::numfmt($mf),
            'mem_pcnt' => $mp,
            'mem_text' => $mp > 5 ? $mp . '%' : '',
            'mem_total' => util::numfmt($mt, 1),
            'mem_used' => util::numfmt($mu, 1),
            'os_name' => $os,
            'uptime' => util::sec2time(intval(explode(' ', (string) file_get_contents('/proc/uptime'))[0])),
            'loadav' => $lav,
            'hostname' => $hn,
            'host_ip' => $ip,
            'kernel' => $knl,
            'cpu_all' => $cpu_all,
            'cpu_name' => $cpu_name,
            'cpu_num' => $cpu_num,
            'cpu_color' => $cpu_pcnt > 90 ? 'danger' : ($cpu_pcnt > 80 ? 'warning' : 'default'),
            'cpu_pcnt' => $cpu_pcnt,
            'cpu_text' => $cpu_pcnt > 5 ? $cpu_pcnt . '%' : '',
        ]);
    }
}
