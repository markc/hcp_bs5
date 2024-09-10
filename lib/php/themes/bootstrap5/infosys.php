<?php

declare(strict_types=1);

// lib/php/themes/bootstrap5/infosys.php 20170225 - 20240906
// Copyright (C) 2015-2024 Mark Constable <markc@renta.net> (AGPL-3.0)

class Themes_Bootstrap5_InfoSys extends Themes_Bootstrap5_Theme
{
    public function list(array $in): string
    {
elog(__METHOD__);

        $data = $this->prepareData($in);
        return $this->generateInfoSysContent($data);
    }

    private function prepareData(array $in): array
    {
elog(__METHOD__);

        return [
            'csrfToken' => $_SESSION['c'] ?? '',
            'hostname'  => $in['hostname'] ?? '',
            'host_ip'   => $in['host_ip'] ?? '',
            'os_name'   => $in['os_name'] ?? '',
            'uptime'    => $in['uptime'] ?? '',
            'loadav'    => $in['loadav'] ?? '',
            'cpu_num'   => $in['cpu_num'] ?? '',
            'cpu_name'  => $in['cpu_name'] ?? '',
            'kernel'    => $in['kernel'] ?? '',
            'mem_used'  => $in['mem_used'] ?? '',
            'mem_total' => $in['mem_total'] ?? '',
            'mem_free'  => $in['mem_free'] ?? '',
            'mem_pcnt'  => $in['mem_pcnt'] ?? '',
            'mem_color' => $in['mem_color'] ?? '',
            'mem_text'  => $in['mem_text'] ?? '',
            'dsk_used'  => $in['dsk_used'] ?? '',
            'dsk_total' => $in['dsk_total'] ?? '',
            'dsk_free'  => $in['dsk_free'] ?? '',
            'dsk_pcnt'  => $in['dsk_pcnt'] ?? '',
            'dsk_color' => $in['dsk_color'] ?? '',
            'dsk_text'  => $in['dsk_text'] ?? '',
            'cpu_all'   => $in['cpu_all'] ?? '',
            'cpu_pcnt'  => $in['cpu_pcnt'] ?? '',
            'cpu_color' => $in['cpu_color'] ?? '',
            'cpu_text'  => $in['cpu_text'] ?? '',
        ];
    }

    private function generateInfoSysContent(array $data): string
    {
elog(__METHOD__);

        $progressBar = fn($label, $used, $total, $free, $pcnt, $color, $text) => <<<HTML
        <div><b>{$label}</b><br>Used: {$used} - Total: {$total} - Free: {$free}</div>
        <div class="progress mb-2">
            <div class="progress-bar bg-{$color}" role="progressbar" aria-valuenow="{$pcnt}"
                aria-valuemin="0" aria-valuemax="100" style="width:{$pcnt}%" title="Used {$label}">{$text}
            </div>
        </div>
        HTML;
        $progressBar2 = fn($label, $used, $total, $free, $pcnt, $color, $text) => <<<HTML
        <div><b>{$label}</b><br>{$used}</div>
        <div class="progress mb-2">
            <div class="progress-bar bg-{$color}" role="progressbar" aria-valuenow="{$pcnt}"
                aria-valuemin="0" aria-valuemax="100" style="width:{$pcnt}%" title="Used {$label}">{$text}
            </div>
        </div>
        HTML;

        return <<<HTML
        <div class="d-flex justify-content-between mb-4">
            <h1 class="mb-0"><i class="bi bi-server"></i> System Info</h1>
            <form method="post" class="form-inline">
                <input type="hidden" name="c" value="{$data['csrfToken']}">
                <input type="hidden" name="o" value="infosys">
                <div class="form-group">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-arrow-repeat"></i> Refresh</button>
                </div>
            </form>
        </div>
        <div class="row align-items-md-stretch">
            <div class="col-md-6 mb-4 order-md-0 order-last">
                <div class="pt-md-2 px-md-5 py-3 px-2 border rounded-3">
                    <table class="table table-sm table-borderless mb-0 info-table">
                        <tbody>
                            <tr><td>Hostname</td><td>{$data['hostname']}</td></tr>
                            <tr><td>Host IP</td><td>{$data['host_ip']}</td></tr>
                            <tr><td>Distro</td><td>{$data['os_name']}</td></tr>
                            <tr><td>Uptime</td><td>{$data['uptime']}</td></tr>
                            <tr><td>CPU Load</td><td>{$data['loadav']} - {$data['cpu_num']} cpus</td></tr>
                            <tr><td>CPU Model</td><td>{$data['cpu_name']}</td></tr>
                            <tr><td>Kernel&nbsp;Version</td><td>{$data['kernel']}</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="col-md-6 mb-4">
                <div class="py-md-3 px-md-5 py-3 px-2 border rounded-3">
                    {$progressBar('RAM', $data['mem_used'], $data['mem_total'], $data['mem_free'], $data['mem_pcnt'], $data['mem_color'], $data['mem_text'])}
                    {$progressBar('DISK', $data['dsk_used'], $data['dsk_total'], $data['dsk_free'], $data['dsk_pcnt'], $data['dsk_color'], $data['dsk_text'])}
                    {$progressBar2('CPU', $data['cpu_all'], '', '', $data['cpu_pcnt'], $data['cpu_color'], $data['cpu_text'])}
                </div>
            </div>
        </div>
        HTML;
    }
}
