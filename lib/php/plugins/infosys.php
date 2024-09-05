<?php

declare(strict_types=1);

// plugins/infosys.php 20170225 - 20240904
// Copyright (C) 2015-2024 Mark Constable <markc@renta.net> (AGPL-3.0)

class Plugins_InfoSys extends Plugin
{
    private array $memInfo = [];
    private array $cpuInfo = [];
    private string $osInfo = 'Unknown OS';

    public function list(): string
    {
        $this->gatherMemoryInfo();
        $this->gatherCpuInfo();
        $this->gatherOsInfo();

        $systemInfo = array_merge(
            $this->getDiskInfo(),
            $this->getMemoryInfo(),
            $this->getCpuInfo(),
            $this->getNetworkInfo(),
            [
                'os_name' => $this->osInfo,
                'uptime' => $this->getUptime(),
                'loadav' => $this->getLoadAverage(),
                'kernel' => $this->getKernelVersion(),
            ]
        );

        return $this->g->t->list($systemInfo);
    }

    private function gatherMemoryInfo(): void
    {
        $meminfo = explode("\n", trim(file_get_contents('/proc/meminfo')));
        foreach ($meminfo as $line) {
            [$key, $value] = explode(':', $line);
            $this->memInfo[$key] = explode(' ', trim($value))[0];
        }
    }

    private function gatherCpuInfo(): void
    {
        if (is_readable('/proc/cpuinfo')) {
            $cpuinfo = trim(file_get_contents('/proc/cpuinfo'));
            $matches = [];
            preg_match_all('/model name.+/', $cpuinfo, $matches);
            $this->cpuInfo['name'] = $matches[0] ? explode(': ', $matches[0][0])[1] : 'Unknown CPU';
            $this->cpuInfo['num'] = count($matches[0]);
        }

        $stat1 = file('/proc/stat');
        sleep(1);
        $stat2 = file('/proc/stat');

        $info1 = explode(' ', preg_replace('!cpu +!', '', $stat1[0]));
        $info2 = explode(' ', preg_replace('!cpu +!', '', $stat2[0]));
        
        $dif = [
            'user' => $info2[0] - $info1[0],
            'nice' => $info2[1] - $info1[1],
            'sys'  => $info2[2] - $info1[2],
            'idle' => $info2[3] - $info1[3]
        ];

        $total = array_sum($dif);
        $this->cpuInfo['usage'] = array_map(fn($y) => round($y / $total * 100, 2), $dif);
    }

    private function gatherOsInfo(): void
    {
        if (is_readable('/etc/os-release')) {
            $osRelease = explode("\n", trim(file_get_contents('/etc/os-release')));
            $osInfo = array_reduce($osRelease, function($carry, $item) {
                [$k, $v] = explode('=', $item);
                $carry[$k] = trim($v, '" ');
                return $carry;
            }, []);
            $this->osInfo = $osInfo['PRETTY_NAME'] ?? 'Unknown OS';
        }
    }
    
    private function getDiskInfo(): array
    {
        $total = (float) disk_total_space('/');
        $free = (float) disk_free_space('/');
        $used = $total - $free;
        $usedPercent = ($used / $total) * 100;
    
        return [
            'dsk_color' => $this->getColorForPercentage($usedPercent),
            'dsk_free' => util::numfmt($free, 1),
            'dsk_pcnt' => floor($usedPercent),
            'dsk_text' => $usedPercent > 5 ? floor($usedPercent) . "%" : '',
            'dsk_total' => util::numfmt($total, 1),
            'dsk_used' => util::numfmt($used, 1),
        ];
    }

    private function getMemoryInfo(): array
    {
        $total = (float) $this->memInfo['MemTotal'] * 1000;
        $used = (float) ($this->memInfo['MemTotal'] - $this->memInfo['MemFree'] - $this->memInfo['Cached'] - $this->memInfo['SReclaimable'] - $this->memInfo['Buffers']) * 1000;
        $free = $total - $used;
        $usedPercent = ($used / $total) * 100;

        return [
            'mem_color' => $this->getColorForPercentage($usedPercent),
            'mem_free' => util::numfmt($free),
            'mem_pcnt' => floor($usedPercent),
            'mem_text' => $usedPercent > 5 ? floor($usedPercent) . "%" : '',
            'mem_total' => util::numfmt($total, 1),
            'mem_used' => util::numfmt($used, 1),
        ];
    }

    private function getCpuInfo(): array
    {
        $usage = $this->cpuInfo['usage'];
        $usedPercent = intval(round(100 - $usage['idle']));

        return [
            'cpu_all' => sprintf('User: %01.1f - System: %01.1f - Nice: %01.1f - Idle: %01.1f', 
                                 $usage['user'], $usage['sys'], $usage['nice'], $usage['idle']),
            'cpu_name' => $this->cpuInfo['name'],
            'cpu_num' => $this->cpuInfo['num'],
            'cpu_color' => $this->getColorForPercentage($usedPercent),
            'cpu_pcnt' => $usedPercent,
            'cpu_text' => $usedPercent > 5 ? "$usedPercent%" : '',
        ];
    }

    private function getNetworkInfo(): array
    {
        $ip = gethostbyname(gethostname());
        return [
            'hostname' => gethostbyaddr($ip),
            'host_ip' => $ip,
        ];
    }

    private function getUptime(): string
    {
        return util::sec2time(intval(explode(' ', file_get_contents('/proc/uptime'))[0]));
    }

    private function getLoadAverage(): string
    {
        $loadAvg = sys_getloadavg();
        return sprintf('1 min: %.2f - 5 min: %.2f - 15 min: %.2f', ...$loadAvg);
    }

    private function getKernelVersion(): string
    {
        return is_readable('/proc/version')
            ? explode(' ', trim(file_get_contents('/proc/version')))[2]
            : 'Unknown';
    }
    private function getColorForPercentage(float $percentage): string
    {
        $intPercentage = (int)$percentage;
        return match(true) {
            $intPercentage > 90 => 'danger',
            $intPercentage > 80 => 'warning',
            default => 'default',
        };
    }
}
