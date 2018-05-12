<?php declare(strict_types = 1);
// netserva.php 2018-05-12 05:15:39 UTC
// Copyright (C) 2015-2018 Mark Constable <markc@renta.net> (AGPL-3.0)
// This is single script concatenation of all PHP files in lib/php at
// https://github.com/netserva/hcp

// lib/php/init.php 20150101 - 20180512

class Init
{
    private $t = null;

    public function __construct(object $g)
    {
        $g->cfg['host'] = $g->cfg['host']
            ? $g->cfg['host']
            : getenv('HOSTNAME');

        session_start();
        //$_SESSION = []; // to reset session for testing
        util::cfg($g);
        $g->in = util::esc($g->in);
        $g->cfg['self'] = str_replace('index.php', '', $_SERVER['PHP_SELF']);

        if (!isset($_SESSION['c'])) $_SESSION['c'] = sha1(microtime());
        util::ses('o'); util::ses('m'); util::ses('l');
        $t = util::ses('t', '', $g->in['t']);

        $t1 = 'themes_' . $t . '_' . $g->in['o'];
        $t2 = 'themes_' . $t . '_theme';

        $this->t = $thm = class_exists($t1) ? new $t1($g)
            : (class_exists($t2) ? new $t2($g) : new Theme($g));

        $p  = 'plugins_' . $g->in['o'];
        if (class_exists($p)) {
            util::remember($g);
            $g->out['main'] = (string) new $p($thm);
        } else $g->out['main'] = "Error: no plugin object!";

        if (empty($g->in['x']))
            foreach ($g->out as $k => $v)
                $g->out[$k] = method_exists($thm, $k) ? $thm->$k() : $v;
    }

    public function __toString() : string
    {
        $g = $this->t->g;
        $x = $g->in['x'];
        if ($x === 'text') {
            return $g->out['main'];
        } elseif ($x === 'json') {
            header('Content-Type: application/json');
            return $g->out['main'];
        } elseif ($x) {
            $out = $g->out[$x] ?? '';
            if ($out) {
                header('Content-Type: application/json');
                return json_encode($out, JSON_PRETTY_PRINT);
            }
        }
        return $this->t->html();
    }

    public function __destruct()
    {
        error_log($_SERVER['REMOTE_ADDR'].' '.round((microtime(true)-$_SERVER['REQUEST_TIME_FLOAT']), 4));
    }
}

function dbg($var = null)
{
    if (is_object($var))
        error_log(ReflectionObject::export($var, true));
    ob_start();
    print_r($var);
    $ob = ob_get_contents();
    ob_end_clean();
    error_log($ob);
}

// lib/php/theme.php 20150101 - 20180512

class Theme
{
    private
    $buf = '',
    $in  = [];

    public function __construct(object $g)
    {
        $this->g = $g;
    }

    public function __toString() : string
    {
        return $this->buf;
    }

    public function log() : string
    {
        list($lvl, $msg) = util::log();
        return $msg ? '
      <p class="alert ' . $lvl . '">' . $msg . '</p>' : '';
    }

    public function nav1() : string
    {
        $o = '?o='.$this->g->in['o'];
        return '
      <nav>' . join('', array_map(function ($n) use ($o) {
            $c = $o === $n[1] ? ' class="active"' : '';
            return '
        <a' . $c . ' href="' . $n[1] . '">' . $n[0] . '</a>';
        }, $this->g->nav1)) . '
      </nav>';
    }

    public function head() : string
    {
        return '
    <header>
      <h1>
        <a href="' . $this->g->cfg['self'] . '">' . $this->g->out['head'] . '</a>
      </h1>' . $this->g->out['nav1'] . '
    </header>';
    }

    public function main() : string
    {
        return '
    <main>' . $this->g->out['log'] . $this->g->out['main'] . '
    </main>';
    }

    public function foot() : string
    {
        return '
    <footer class="text-center">
      <br>
      <p><em><small>' . $this->g->out['foot'] . '</small></em></p>
    </footer>';
    }

    public function end() : string
    {
        return '
    <pre>' . $this->g->out['end'] . '
    </pre>';
    }

    public function html() : string
    {
        extract($this->g->out, EXTR_SKIP);
        return '<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>' . $doc . '</title>' . $css . $js . '
  </head>
  <body>' . $head . $main . $foot . $end . '
  </body>
</html>
';
    }

    public static function dropdown(
        array $ary,
        string $name,
        string $sel = '',
        string $label = '',
        string $class = '',
        string $extra = '') : string
    {
        $opt = $label ? '
          <option value="">' . ucfirst($label) . '</option>' : '';
        $buf = '';
        $c = $class ? ' class="' . $class . '"' : '';
        foreach($ary as $k => $v) {
            $t = str_replace('?t=', '', $v[1]);
            $s = $sel === $t ? ' selected' : '';
            $buf .= '
          <option value="' . $t . '"' . $s . '>' . $v[0] . '</option>';
        }
        return '
        <select' . $c . ' name="' . $name . '" id="' . $name . '"' . $extra . '>' . $opt . $buf . '
        </select>';
    }

    public function __call(string $name, array $args) : string
    {
        return 'Theme::' . $name . '() not implemented';
    }
}

// lib/php/plugin.php 20150101 - 20180512

class Plugin
{
    protected
    $buf = '',
    $dbh = null,
    $tbl = '',
    $in  = [];

    public function __construct(Theme $t)
    {
        $o = $t->g->in['o'];
        $m = $t->g->in['m'];

        if (!util::is_usr() && $o !== 'auth' && $m !== 'list' && $m !== 'read') {
            util::log('You must <a href="?o=auth">Sign in</a> to create, update or delete items');
            header('Location: ' . $t->g->cfg['self'] . '?o=auth');
            exit();
        }

        $this->t  = $t;
        $this->g  = $t->g;
        $this->in = util::esc($this->in);
        if ($this->tbl) {
            if (!is_null($this->dbh))
                db::$dbh = $this->dbh;
            elseif (is_null(db::$dbh))
                db::$dbh = new db($t->g->db);
            db::$tbl = $this->tbl;
        }
        $this->buf .= $this->{$t->g->in['m']}();
    }

    public function __toString() : string
    {
        return $this->buf;
    }

    protected function create() : string
    {
        if ($_POST) {
            $this->in['updated'] = date('Y-m-d H:i:s');
            $this->in['created'] = date('Y-m-d H:i:s');
            $lid = db::create($this->in);
            util::log('Item number ' . $lid . ' created', 'success');
            util::ses('p', '', '1');
            return $this->list();
        } else return $this->t->create($this->in);
    }

    protected function read() : string
    {
        return $this->t->read(db::read('*', 'id', $this->g->in['i'], '', 'one'));
    }

    protected function update() : string
    {
        if ($_POST) {
            $this->in['updated'] = date('Y-m-d H:i:s');
            db::update($this->in, [['id', '=', $this->g->in['i']]]);
            util::log('Item number ' . $this->g->in['i'] . ' updated', 'success');
            util::ses('p', '', '1');
            return $this->list();
        } elseif ($this->g->in['i']) {
            return $this->t->update(db::read('*', 'id', $this->g->in['i'], '', 'one'));
        } else return 'Error updating item';
    }

    protected function delete() : string
    {
        if ($this->g->in['i']) {
            $res = db::delete([['id', '=', $this->g->in['i']]]);
            util::log('Item number ' . $this->g->in['i'] . ' removed', 'success');
            util::ses('p', '', '1');
            return $this->list();
        } else return 'Error deleting item';
    }

    protected function list() : string
    {
        return $this->t->list(db::read('*', '', '', 'ORDER BY `updated` DESC'));
    }

    public function __call(string $name, array $args) : string
    {
        return 'Plugin::' . $name . '() not implemented';
    }
}

// lib/php/plugins/vmails.php 20180321

class Plugins_Vmails extends Plugin
{
    protected
    $tbl = 'vmails',
    $in = [
        'active'    => 0,
        'aid'       => 1,
        'did'       => 1,
        'gid'       => 1000,
        'home'      => '',
        'passwd1'   => '',
        'passwd2'   => '',
        'password'  => '',
        'quota'     => 1000000000,
        'spamf'     => 0,
        'uid'       => 1000,
        'user'      => '',
    ];

    function create() : string
    {
        if (util::is_post()) {
            extract($this->in);
            $spamf  = $spamf ? 1 : 0;
            $user_esc = trim(escapeshellarg($user), "'");
            $spamf_str = $spamf === 1 ? '' : 'nospam';
            exec("sudo addvmail $user_esc $spamf_str 2>&1", $retArr, $retVal);
            util::log('<pre>' . trim(implode("\n", $retArr)) . '</pre>', $retVal ? 'danger' : 'success');
            util::ses('p', '', '1');
            return $this->list();
        }
        return $this->t->create($this->in);
    }

    protected function read() : string
    {
        return $this->t->update(db::read('*', 'id', $this->g->in['i'], '', 'one'));
    }

    function update() : string
    {
        if (util::is_post()) {
            extract($this->in);
            $quota *= 1000000;
            $active = $active ? 1 : 0;
            $spamf  = $spamf ? 1 : 0;

            if (!filter_var($user, FILTER_VALIDATE_EMAIL)) {
                util::log('Email address is invalid');
                $_POST = []; return $this->read();
            }

            if ($passwd1 && $passwd2) {
                if (!util::chkpw($passwd1, $passwd2)) {
                    $_POST = []; return $this->read();
                }

                $sql = "
 UPDATE `vmails` SET
        `password`  = :password,
        `updated`   = :updated
  WHERE `id` = :id";

                $res = db::qry($sql, [
                    'id'        => $this->g->in['i'],
                    'updated'   => date('Y-m-d H:i:s'),
                    'password'  => util::mail_password($passwd1),
                ]);
            }

            $spamf_old = db::read('spamf', 'id', $this->g->in['i'], '', 'col');

            $sql = "
 UPDATE `vmails` SET
        `active`    = :active,
        `quota`     = :quota,
        `updated`   = :updated
  WHERE `id` = :id";

            $res = db::qry($sql, [
                'id'      => $this->g->in['i'],
                'active'  => $active,
                'quota'   => $quota,
                'updated' => date('Y-m-d H:i:s'),
            ]);

            $spamf_buf = '';
            if ($spamf_old !== $spamf) {
                $user_esc = trim(escapeshellarg($user), "'");
                $spamf_str = ($spamf === 1) ? 'on' : 'off';
                exec("sudo spamf $user_esc $spamf_str 2>&1", $retArr, $retVal);
                $spamf_buf = trim(implode("\n", $retArr));
                $spamf_buf = $spamf_buf ? '<pre>' . $spamf_buf . '</pre>' : '';
            }
            util::log($spamf_buf . 'Mailbox details for ' . $user . ' have been saved', 'success');
            util::ses('p', '', '1');
            return $this->list();
        } elseif ($this->g->in['i']) {
            return $this->read();
        } else return 'Error updating item';
    }

    function delete() : string
    {
        if ($this->g->in['i']) {
            $user = db::read('user', 'id', $this->g->in['i'], '', 'col');
            if ($user) {
                $retArr = []; $retVal = null;
                $user_esc = trim(escapeshellarg($user), "'");
                exec("sudo delvmail $user_esc 2>&1", $retArr, $retVal);
                util::log('<pre>' . trim(implode("\n", $retArr)) . '</pre>', $retVal ? 'danger' : 'success');
            } else {
                util::log('ERROR: user does not exist');
            }
        }
        util::ses('p', '', '1');
        return $this->list();
    }

    protected function list() : string
    {
        if ($this->g->in['x'] === 'json') {
            $columns = [
                ['dt' => null, 'db' => 'id'],
                ['dt' => 0, 'db' => 'user',       'formatter' => function($d) { return "<b>$d</b>"; }],
                ['dt' => 1, 'db' => 'domain'],
                ['dt' => 2, 'db' => '',           'formatter' => function($d, $row) {
                    $percent = round(($row['size_mail'] / $row['quota']) * 100);
                    $pbuf    = $percent > 9 ? $percent.'%' : '';
                    $pbar    = $percent >= 90 ? 'bg-danger' : ($percent >= 75 ? 'bg-warning' : '');
                    return '
                      <div class="progress">
                        <div class="progress-bar ' . $pbar . '" role="progressbar" aria-valuenow="' . $percent . '" aria-valuemin="0" aria-valuemax="100" style="width: ' . $percent . '%;">
                          ' . $pbuf . '
                        </div>
                      </div>';
                }],
                ['dt' => 3, 'db' => 'size_mail',  'formatter' => function($d) { return util::numfmt(intval($d)); }],
                ['dt' => 4, 'db' => null,         'formatter' => function($d) { return '/'; }],
                ['dt' => 5, 'db' => 'quota',      'formatter' => function($d) { return util::numfmt(intval($d)); }],
                ['dt' => 6, 'db' => 'num_total',  'formatter' => function($d) { return util::numfmt(intval($d)); }],
                ['dt' => 7, 'db' => 'active',     'formatter' => function($d, $row) {
                    $active_buf = $d
                        ? '<i class="fas fa-check text-success"></i>'
                        : '<i class="fas fa-times text-danger"></i>';
                    return $active_buf . '
                    <a class="editlink" href="?o=vmails&m=update&i=' . $row['id'] . '" title="Update entry for ' . $row['user'] . '">
                      <i class="fas fa-edit fa-fw cursor-pointer"></i></a>
                    <a href="?o=vmails&m=delete&i=' . $row['id'] . '" title="Remove Mailbox" onClick="javascript: return confirm(\'Are you sure you want to remove: ' . $row['user'] . '?\')">
                      <i class="fas fa-trash fa-fw cursor-pointer text-danger"></i></a>';
                }],
                ['dt' => 8, 'db' => 'updated'],
            ];
            return json_encode(db::simple($_GET, 'vmails_view', 'id', $columns), JSON_PRETTY_PRINT);
        }
        return $this->t->list([]);
    }
}

// plugins/processes.php 20170225 - 20180430

class Plugins_Processes extends Plugin
{
    public function list() : string
    {
        return $this->t->list(['procs' => shell_exec('sudo processes')]);
    }
}

// plugins/infosys.php 20170225 - 20180430

class Plugins_InfoSys extends Plugin
{
    public function list() : string
    {
        $mem = $dif = $cpu = [];
        $cpu_name = $procs = '';
        $cpu_num = 0;
        $os  = 'Unknown OS';

        $pmi = explode("\n", trim(file_get_contents('/proc/meminfo')));
        $lav = join(', ', sys_getloadavg());
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
                list($k, $v) = explode('=', $line);
                $osr[$k] = trim($v, '" ');
            }
            $os = $osr['PRETTY_NAME'] ?? 'Unknown OS';
        }

        foreach ($pmi as $line) {
            list($k, $v) = explode(':', $line);
            list($mem[$k],) = explode(' ', trim($v));
        }

        $info1 = explode(" ", preg_replace("!cpu +!", "", $stat1[0]));
        $info2 = explode(" ", preg_replace("!cpu +!", "", $stat2[0]));
        $dif['user'] = $info2[0] - $info1[0];
        $dif['nice'] = $info2[1] - $info1[1];
        $dif['sys']  = $info2[2] - $info1[2];
        $dif['idle'] = $info2[3] - $info1[3];
        $total = array_sum($dif);
        foreach($dif as $x=>$y) $cpu[$x] = round($y / $total * 100, 2);
        $cpu_all = sprintf("User: %01.2f, System: %01.2f, Nice: %01.2f, Idle: %01.2f", $cpu['user'], $cpu['sys'], $cpu['nice'], $cpu['idle']);
        $cpu_pcnt = intval(round(100 - $cpu['idle']));

        $dt  = (float) disk_total_space('/');
        $df  = (float) disk_free_space('/');
        $du  = (float) $dt - $df;
        $dp  = floor(($du / $dt) * 100);

        $mt  = (float) $mem['MemTotal'] * 1024;
        $mf  = (float) ($mem['MemFree'] + $mem['Cached']) * 1024;
        $mu  = (float) $mt - $mf;
        $mp  = floor(($mu / $mt) * 100);

        $hn  = is_readable('/proc/sys/kernel/hostname')
            ? trim(file_get_contents('/proc/sys/kernel/hostname'))
            : 'Unknown';
        $ip  = gethostbyname($hn);
        $knl = is_readable('/proc/version')
            ? explode(' ', trim(file_get_contents('/proc/version')))[2]
            : 'Unknown';

        return $this->t->list([
            'dsk_color' => $dp > 90 ? 'danger' : ($dp > 80 ? 'warning' : 'default'),
            'dsk_free'  => util::numfmt($df),
            'dsk_pcnt'  => $dp,
            'dsk_text'  => $dp > 5 ? $dp. '%' : '',
            'dsk_total' => util::numfmt($dt),
            'dsk_used'  => util::numfmt($du),
            'mem_color' => $mp > 90 ? 'danger' : ($mp > 80 ? 'warning' : 'default'),
            'mem_free'  => util::numfmt($mf),
            'mem_pcnt'  => $mp,
            'mem_text'  => $mp > 5 ? $mp . '%' : '',
            'mem_total' => util::numfmt($mt),
            'mem_used'  => util::numfmt($mu),
            'os_name'   => $os,
            'uptime'    => util::sec2time(intval(explode(' ', (string) file_get_contents('/proc/uptime'))[0])),
            'loadav'    => $lav,
            'hostname'  => $hn,
            'host_ip'   => $ip,
            'kernel'    => $knl,
            'cpu_all'   => $cpu_all,
            'cpu_name'  => $cpu_name,
            'cpu_num'   => $cpu_num,
            'cpu_color' => $cpu_pcnt > 90 ? 'danger' : ($cpu_pcnt > 80 ? 'warning' : 'default'),
            'cpu_pcnt'  => $cpu_pcnt,
            'cpu_text'  => $cpu_pcnt > 5 ? $cpu_pcnt. '%' : '',
        ]);
    }
}

// lib/php/plugins/home.php 20150101 - 20180503

class Plugins_Home extends Plugin
{
    public function list() : string
    {
        return $this->t->list([]);
    }
}

// lib/php/plugins/dkim.php 20180511 - 20180511

class Plugins_Dkim extends Plugin
{
    public function list() : string
    {
        $retArr = []; $retVal = null;
        exec("sudo dkim show 2>&1", $retArr, $retVal);
//        util::log('<pre>' . trim(implode("\n", $retArr)) . '</pre>', $retVal ? 'danger' : 'success');
        return $this->t->list(['buf' =>  trim(implode("\n", $retArr))]);
    }

}

// lib/php/plugins/records.php 20150101 - 20170423

class Plugins_Records extends Plugin
{
    protected
    $tbl = 'records',
    $in = [
        'auth'        => 1, // ?
        'change_date' => '', // ?
        'content'     => '',
        'disabled'    => 0, // ?
        'domain_id'   => null,
        'name'        => '',
        'ordername'   => '', // ?
        'prio'        => 0,
        'ttl'         => 300,
        'type'        => '',
    ];

    public function __construct(Theme $t)
    {
        if ($t->g->dns['db']['type'])
            $this->dbh = new db($t->g->dns['db']);
        parent::__construct($t);
    }

    protected function create() : string
    {
        if ($_POST) {
            $sql = "
 SELECT name FROM domains
  WHERE id = :did";

            $domain = db::qry($sql, ['did' => $this->in['domain_id']], 'col');
            $this->in['updated'] = date('Y-m-d H:i:s');
            $this->in['created'] = date('Y-m-d H:i:s');
            $this->in['name'] = $this->in['name']
                ? $this->in['name'] . '.' . $domain
                : $domain;
            $lid = db::create($this->in);
            util::log('Created DNS record ID: ' . $lid, 'success');
            $this->g->in['i'] = $this->in['domain_id'];
            return $this->list();
        }
        return $this->t->create($this->in);
    }

    protected function update() : string
    {
        if ($_POST) {
            $this->in['disabled'] = isset($_POST['active']) && $_POST['active'] ? 0 : 1;
            $res = db::update($this->in, [['id', '=', $this->g->in['i']]]);
            // TODO check $res ???
            util::log('Updated DNS record ID: ' . $this->g->in['i'], 'success');
            $this->g->in['i'] = $this->in['domain_id'];
            return $this->list();
        } elseif ($this->g->in['i']) {
            return $this->list();
        }
        return 'Error updating DNS record';
    }

    protected function delete() : string
    {
        if ($this->g->in['i']) {
            $res = db::delete([['id', '=', $this->g->in['i']]]);
            // TODO check $res ???
            util::log('Deleted DNS record ID: ' . $this->g->in['i'], 'success');
            util::ses('p', '', '1');
            $this->g->in['i'] = $this->in['domain_id'];
            return $this->list();
        }
        return 'Error deleting item';
    }

    protected function list() : string
    {
          $sql = "
 SELECT name FROM domains
  WHERE id = :did";

        $domain = db::qry($sql, ['did' => $this->g->in['i']], 'col');

        $sql = "
 SELECT id,name,type,content,ttl,disabled,prio AS priority
   FROM records
  WHERE (name LIKE '' OR 1) AND
        (content LIKE '' OR 1) AND
        (domain_id = :did) AND
        (type != 'SOA')";

        return $this->t->update(array_merge(
            ['domain' => $domain],
            ['domain_id' => $this->g->in['i']],
            db::qry($sql, ['did' => $this->g->in['i']])
        ));
    }
}

// lib/php/plugins/mail/infomail.php 20170225 - 20170514

class Plugins_InfoMail extends Plugin
{
    protected $pflog = '/tmp/pflogsumm.log';

    public function list() : string
    {
        return $this->t->list([
            'mailq' => shell_exec('mailq'),
            'pflogs' => is_readable($this->pflog)
                ? file_get_contents($this->pflog)
                : 'none',
            'pflog_time' => is_readable($this->pflog)
                ? round(abs(date('U') - filemtime($this->pflog)) / 60, 0) . ' min.'
                : '0 min.',
        ]);
    }

    public function pflog_renew()
    {
        $this->pflogs = shell_exec('sudo pflogs');
        return $this->list();
    }
}

// lib/php/plugins/valias.php 20170225 - 20180512

class Plugins_Valias extends Plugin
{
    protected
    $tbl = 'valias',
    $in = [
        'aid'    => 1,
        'hid'    => 1,
        'source' => '',
        'target' => '',
        'active' => 0,
    ];

// TODO recfactor common parts of create() and update() into private methods
// yep, as of 20170704 this is still a medium to high priority TODO

    protected function create() : string
    {
        if ($_POST) {
            extract($this->in);
            $active = $active ? 1 : 0;
            $sources = array_map('trim', preg_split("/( |,|;|\n)/", $source));
            $targets = array_map('trim', preg_split("/( |,|;|\n)/", $target));

            if (empty($source[0])) {
                util::log('Alias source address is empty');
                $_POST = []; return $this->t->create($this->in);
            }

            if (empty($targets[0])) {
                util::log('Alias target address is empty');
                $_POST = []; return $this->t->create($this->in);
            }

            foreach ($sources as $s) {
                if (empty($s)) continue;
                $lhs = ''; $rhs = '';
                if (strpos($s, '@') !== false)
                    list($lhs, $rhs) = explode('@', $s);
                else $rhs = $s;

                if (!$domain = idn_to_ascii($rhs)) {
                    util::log('Invalid source domain: ' . $rhs);
                    $_POST = []; return $this->t->create($this->in);
                }

                $sql = "
 SELECT `id`
   FROM `vhosts`
  WHERE `domain` = :domain";

                $hid = db::qry($sql, ['domain' => $domain], 'col');

                if (!$hid) {
                    util::log($domain . ' does not exist as a local domain');
                    $_POST = []; return $this->t->create($this->in);
                }

                if ((!filter_var($s, FILTER_VALIDATE_EMAIL)) && !empty($lhs)) {
                    util::log('Alias source address is invalid');
                    $_POST = []; return $this->t->create($this->in);
                }

                $sql = "
 SELECT 1 FROM `valias`
  WHERE `source` = :catchall";

                $catchall = db::qry($sql, ['catchall' => '@'.$domain], 'col');
//error_log("catchall=$catchall");

                if ($catchall !== 1) {
                    $sql = "
 SELECT `source`
   FROM `valias`
  WHERE `source` = :source";

                    $num_results = count(db::qry($sql, ['source' => $s]));

                    if ($num_results) {
                        util::log($s . ' already exists as an alias');
                        $_POST = []; return $this->t->create($this->in);
                    }
                }

                $sql = "
 SELECT `user`
   FROM `vmails`
  WHERE `user` = :source";

                $num_results = count(db::qry($sql, ['source' => $s]));

                if ($num_results) {
                    util::log($s . ' already exists as a regular mailbox');
                    $_POST = []; return $this->t->create($this->in);
                }

                foreach ($targets as $t) {
                    if (empty($t)) continue;
                    list($tlhs, $trhs) = explode('@', $t);

                    if (!$tdomain = idn_to_ascii($trhs)) {
                        util::log('Invalid target domain: ' . $tdomain);
                        $_POST = []; return $this->t->create($this->in);
                    }

                    if (!filter_var($t, FILTER_VALIDATE_EMAIL)) {
                        util::log('Alias target address is invalid');
                        $_POST = []; return $this->t->create($this->in);
                    }

                    if ($catchall !== 1) {
                        if ($t === $s) {
                            util::log('Alias source and target addresses must not be the same');
                            $_POST = []; return $this->t->create($this->in);
                        }
                    }
                }

                $target  = implode(',', $targets);

                $sql = "
 INSERT INTO `valias` (
        `active`,
        `hid`,
        `source`,
        `target`,
        `updated`,
        `created`
) VALUES (
        :active,
        :hid,
        :source,
        :target,
        :updated,
        :created
)";
                $s = filter_var($s, FILTER_VALIDATE_EMAIL)
                    ? $s
                    : '@' . $domain;

                $result = db::qry($sql, [
                    'active'  => $active ? 1 : 0,
                    'hid'     => $hid,
                    'source'  => $s,
                    'target'  => $target,
                    'updated' => date('Y-m-d H:i:s'),
                    'created' => date('Y-m-d H:i:s')
                ]);
                // test $result?
            }
            util::log('Alias added', 'success');
            util::ses('p', '', '1');
            return $this->list();
        } else return $this->t->create($this->in);
    }

    protected function read() : string
    {
        return $this->t->update(db::read('*', 'id', $this->g->in['i'], '', 'one'));
    }

    protected function update() : string
    {
        if ($_POST) {
            extract($this->in);
            $active = $active ? 1 : 0;
            $sources = array_map('trim', preg_split("/( |,|;|\n)/", $source));
            $targets = array_map('trim', preg_split("/( |,|;|\n)/", $target));

            if (empty($source[0])) {
                util::log('Alias source address is empty');
                $_POST = []; return $this->read();
            }

            if (empty($targets[0])) {
                util::log('Alias target address is empty');
                $_POST = []; return $this->read();
            }

            foreach ($sources as $s) {
                if (empty($s)) continue;
                $lhs = ''; $rhs = '';
                if (strpos($s, '@') !== false)
                    list($lhs, $rhs) = explode('@', $s);
                else $rhs = $s;

                if (!$domain = idn_to_ascii($rhs)) {
                    util::log('Invalid source domain: ' . $rhs);
                    $_POST = []; return $this->read();
                }

                $sql = "
 SELECT `id`
   FROM `vhosts`
  WHERE `domain` = :domain";

                $hid = db::qry($sql, ['domain' => $domain], 'col');

                if (!$hid) {
                    util::log($domain . ' does not exist as a local domain');
                    $_POST = []; return $this->read();
                }

                if ((!filter_var($s, FILTER_VALIDATE_EMAIL)) && !empty($lhs)) {
                    util::log('Alias source address is invalid');
                    $_POST = []; return $this->read();
                }

                $sql = "
 SELECT 1
   FROM `valias`
  WHERE `source` = :catchall";

                $catchall = db::qry($sql, ['catchall' => '@'.$domain], 'col');
//error_log("catchall=$catchall");

                if ($catchall !== 1) {
                    $sql = "
 SELECT `user`
   FROM `vmails`
  WHERE `user` = :source";

                    $num_results = count(db::qry($sql, ['source' => $s]));

                    if ($num_results) {
                        util::log($s . ' already exists as a regular mailbox');
                        $_POST = []; return $this->read();
                    }
                }

                foreach ($targets as $t) {
                    if (empty($t)) continue;
                    list($tlhs, $trhs) = explode('@', $t);

                    if (!$tdomain = idn_to_ascii($trhs)) {
                        util::log('Invalid target domain: ' . $tdomain);
                        $_POST = []; return $this->read();
                    }

                    if (!filter_var($t, FILTER_VALIDATE_EMAIL)) {
                        util::log('Alias target address is invalid');
                        $_POST = []; return $this->read();
                    }

                    if ($catchall !== 1) {
                        if ($t === $s) {
                            util::log('Alias source and target addresses must not be the same');
                            $_POST = []; return $this->read();
                        }
                    }
                }

                $target  = implode(',', $targets);
                $s = filter_var($s, FILTER_VALIDATE_EMAIL)
                    ? $s
                    : '@' . $domain;

                $sql = "
 SELECT `source`
   FROM `valias`
  WHERE `source` = :source";

                $exists = count(db::qry($sql, ['source' => $s]));

                if ($exists or (count($sources) == 1)) {
                    $sql = "
 UPDATE `valias` SET
        `active`  = :active,
        `source`  = :source,
        `target`  = :target,
        `updated` = :updated
  WHERE `id` = :id";

                    $result = db::qry($sql, [
                        'id'      => $this->g->in['i'],
                        'active'  => $active,
                        'source'  => $s,
                        'target'  => $target,
                        'updated' => date('Y-m-d H:i:s'),
                    ]);
                } else {
                    $sql = "
 INSERT INTO `valias` (
        `active`,
        `hid`,
        `source`,
        `target`,
        `updated`,
        `created`
) VALUES (
        :active,
        :hid,
        :source,
        :target,
        :updated,
        :created
)";
                    $result = db::qry($sql, [
                        'active'  => $active ? 1 : 0,
                        'hid'     => $hid,
                        'source'  => $s,
                        'target'  => $target,
                        'updated' => date('Y-m-d H:i:s'),
                        'created' => date('Y-m-d H:i:s')
                    ]);
                }
            }
            util::log('Changes to alias have been saved', 'success');
            util::ses('p', '', '1');
            return $this->list();
        } elseif ($this->g->in['i']) {
            return $this->read();
        } else return 'Error updating item';
    }
    
    protected function list() : string
    {
        if ($this->g->in['x'] === 'json') {
            $columns = [
                ['dt' => 0, 'db' => 'source',     'formatter' => function($d) { return "<b>$d</b>"; }],
                ['dt' => 1, 'db' => 'target'],
                ['dt' => 2, 'db' => 'domain'],
                ['dt' => 3, 'db' => 'active',     'formatter' => function($d, $row) {
                    $active_buf = $d
                        ? '<i class="fas fa-check text-success"></i>'
                        : '<i class="fas fa-times text-danger"></i>';
                    return $active_buf . '
                    <a class="editlink" href="?o=valias&m=update&i=' . $row['id'] . '" title="Update entry for ' . $row['source'] . '">
                      <i class="fas fa-edit fa-fw cursor-pointer"></i></a>
                    <a href="?o=valias&m=delete&i=' . $row['id'] . '" title="Remove Alias" onClick="javascript: return confirm(\'Are you sure you want to remove: ' . $row['source'] . '?\')">
                      <i class="fas fa-trash fa-fw cursor-pointer text-danger"></i></a>';
                }],
                ['dt' => 4, 'db' => 'id'],
                ['dt' => 5, 'db' => 'updated'],
            ];
            return json_encode(db::simple($_GET, 'valias_view', 'id', $columns), JSON_PRETTY_PRINT);
        }
        return $this->t->list([]);
    }
}

// lib/php/plugins/mailgraph.php 20170225 - 20170514

class Plugins_MailGraph extends Plugin
{
    public function list() : string
    {
        $return = '';
        $images = ['0-n', '1-n', '2-n', '3-n'];
        foreach ($images as $image) {
            $image = 'http://localhost:81/mailgraph.cgi?' . $image;
            $headers = get_headers($image);
            $return_code = substr($headers[0], 9, 3);
            if ($return_code >= 400) {
                $return .= '<img class="img-responsive" alt="not-yet-available" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACAAAAAgCAYAAABzenr0AAAG3UlEQVRYR8WXC2yT1xXH/+dcx0loRkMhULZuA2WxAwjStVsYiZ06IYUOClRbVaGJ0m0MCaEpqOs00anVgErtNq1ZH5PWVRUIOtZ2LevGUOkjbydMbKM8x+wQKKWsCYSUFvLAj++c6fvAxo4NDtKkWbIsfd+55/+79/7PudeE//OHblR/A8A11TNnwIiXVSYqMZPggsA6MTQy/vCSffuGbyTnmAFafZ56JXxfhb7JjOJsIgSJQrhTmV4eKYq/smh3TyQXTE6A5pryKkX8WaPma4lklkivIToiRGcYahF0ggJeAXsYcHI6MYYfD3SENxOg1wK5JkBrIODSWO+Tyvjx5aQyAOIXGLL9ro5j/24NzCpikanCljGWDPiDPeferSotyTeubwP0Q4XOtEUJeJdhragJ9vRnX7UsT1sD0wog+X+EYokCQkAjjGujZUW+ZGBWiyWL2XBZ6lAB+gnaBPDW2mDovXaf9yGL9GkGTQBwnAl339UR/mC0XMYK2Cbz+707GLgPis8Avd+QHIzBNDKwIteeOrNW/J3YWhOP531ijOxUYI4KPpCCvHn1zUfOpObIAGitKd8A1Z8RcMEC17oopgLzVyi+MBbxRIxjSOU16oq8CSu/DUCFQto6gsfmbwDkalxK1hafp4KU9gmDifReUTptFB0g3Hwj4mmxhB9ITN8G0wFmTCJQQyAYej4rQJvf+44CC4j0xZg7/hMzkrefGNNTE06uX4zB7qMYPpW+naagEJ9fthynX98KleQEISJxMFUzYTqUXhXo+XyhL/u6whevmPRy+ia/d44BDhIwzLCmCZnHVNGQKn7rPfeh/NGnEP3kHA6sewjDp044r23x2b94AcVfnYu+t/6E8C8fS4Mg4NDZW8N3TO717lFCJYCHa4PhZ9IAWn2eX4HoERG8xHmux8mKfqhgdwJgysJlmPHTnwN02TYORMNKRM72JsUTsVkhFA8oqQHoFRHaP78rdEcaQJu//F927Sq0HooKIno6kXDCnfNQ0bg5KZ54Hj0/gJGPTuLmOXdmWOTU9hdx4neNyecKvF0QH/pWhG86D0Z+1IpPWbjn+FlnOnZTgRW/CIGVL0Ofi5jCN0G8MDGa89yYtelZTKyuHZMXL/X9x1kd+zfxEcGlyHiruGDQNBHgg9KS2s7QLgfAcT/RAbth1AbDX2mqLvvYME9NVRsrxGXxB3Gp7+NMWMOz1dIGgq5O+MABaK4pq2HldruBBDrDc5ury2LM7BqdwYF44jlMrApkXYnrisNpqfNJtZ5AjxJoUyAYsvvNVQAA/6gNhiub/WVRBueNVkl1ezaC6EA/DqxbmVGiiVhi1KlFC0C6HkQbaztCGxyA9pry2aJ6SEROzu86Nr3Z5/2ICbeliuQSTxrzSnUkSjRtG0lmivAjIKxSxbq6zvBzDsCeebcVjrhuGrKPzVhh/nj3cPQ1kC5ODB6r+PUg7P5SNFxUfHHcYDuAeSS6KNDVvTt5FrT5vQftQ8N+ocylgCbbZfHtX0dF4xaQK90WttGGPzyOW+b6M3bk5JbfwP4mlx+y0yW8IsIyALBL3VRS3xwaSAK0+D1P2uZQ4GVx08OuqJ5SYFwiwSR/vVOKZIzzKFFq9r6PLtHTb2xDz/NPAXr1HkJKS5UwEdAtAO2tDYa+YedJArQGPOVi0VEDianRUrV4rQ2UOrVJNXdj1sZnEOnvSyu11BLNJm4LBoKhqna/d7+zysDaQDD82zQApyFVe/8MxjJS/OHiSNHqcYWD/2TCjFQIuwSHTnRn1LkNMWXBUvS+tSNt5nYDchmqFGglFC8RcNYdH5pW9bfTIxkATb5yj4usw84ZQLqcYd5XlU4FJo+pBY4KEkCJsJzYvE+WtU+B8apYVdcZ3nzVG6MGtfg8P7LPAQFGQHKPiukjsXaNvoLlAhKRQUO0Mp7v3kMjkaA9XqC76oLdS1MvqVkvpS1+7zYCHrQhGFiZJ3gnRvKEENZma1AZMITdYNNgWVG3gdkJoJRAR43m+fydh8+nxmcFsG/EFO/dqoTv2MEKbIlZ8fVuN7kh5rsWdDFZdDszCq68F1V0E+l7JLSt/1PXoZKJVgOpbrQrSVSOWMoLF3SFMw6Ia17LFaB2f/l6S6xN9rlgNxIBtgLYTmbq3v6SNi3p90ywjNtE3Zc+tf+EtFR7vUS4X4A1iU4qkDciUV21aG/PhWzblvOPiX1Tcgl+rYy6ZAKRITCFCHQGkLiAb2HAk2pWgfYQeH1dMLTjen7JCZAY3Oz3VDLoe6K4d/Q5keLoC0rUrMDvz00J/eWB12HlMuuYAVITtdeUftFS4yHCJAUbqPUZGTreX9J9bCyiOU2Yi/p/+f6/yPUmTii6UZAAAAAASUVORK5CYII=" /><p>Not enough data</p>';
            } else {
                $imageData = base64_encode(file_get_contents($image));
                $return .= '<img class="img-responsive" alt="' . $image . '" src="data:image/png;base64,' . $imageData . '" />';
            }
        }
        return $this->t->list(['mailgraph' => $return]);
    }
}

// lib/php/plugins/mail/alias_domains.php 20170225

class Plugins_Mail_DomainAlias extends Plugin
{
    protected
    $tbl = 'alias_domain',
    $in = [
        'alias_domain'      => '',
        'target_domain'     => '',
        'active'            => 0,
    ];

    protected function create() : string
    {
        if ($_POST) {
            extract($this->in);

            if (!util::is_valid_domain_name($alias_domain)) {
                util::log('Invalid alias domain name');
                $_POST = [];
                return $this->t->create($this->in);
            }

            if (!util::is_valid_domain_name($target_domain)) {
                util::log('Invalid target domain name');
                $_POST = [];
                return $this->t->create($this->in);
            }

            if ($alias_domain === $target_domain) {
                util::log('Alias domain must not be equal to target domain');
                $_POST = [];
                return $this->t->create($this->in);
            }

            $sql = "
 SELECT `domain` FROM `domain`
  WHERE `domain`= :target_domain";

            $num_results = db::qry($sql, ['target_domain' => $target_domain], 'one');

            if (!$num_results) {
                util::log('Target domain not found');
                $_POST = [];
                return $this->t->create($this->in);
            }

            $sql = "
 SELECT `alias_domain` FROM `alias_domain`
  WHERE `alias_domain`= :alias_domain";

            $num_results = db::qry($sql, [
                'alias_domain' => $alias_domain,
            ], 'one');

            if ($num_results) {
                util::log('Alias domain already exists');
                $_POST = [];
                return $this->t->create($this->in);
            }

            $sql = "
 INSERT INTO `alias_domain` (
        `alias_domain`,
        `target_domain`,
        `active`,
        `updated`,
        `created`
) VALUES (
        :alias_domain,
        :target_domain,
        :active,
        :updated,
        :created
)";
            $res = db::qry($sql, [
                'alias_domain'  => $alias_domain,
                'target_domain' => $target_domain,
                'active'        => $active ? 1 : 0,
                'updated'       => date('Y-m-d H:i:s'),
                'created'       => date('Y-m-d H:i:s'),
            ]);
//            $lid = db::$dbh->lastInsertId();
            util::log('Created domain alias from ' . $alias_domain . ' to ' . $target_domain, 'success');
            return $this->list();
        } else return $this->t->create($this->in);
    }
}

// lib/php/plugins/users.php 20150101 - 20180405

class Plugins_Accounts extends Plugin
{
    protected
    $tbl = 'accounts',
    $in = [
        'grp'       => 1,
        'acl'       => 2,
        'vhosts'    => 1,
        'login'     => '',
        'fname'     => '',
        'lname'     => '',
        'altemail'  => '',
        'webpw'     => '',
    ];

    protected function list() : string
    {
        if (util::is_acl(0)) { // superadmin
            $where = '';
            $wval = '';
        } elseif (util::is_acl(1)) { // normal admin
            $where = 'grp';
            $wval = $_SESSION['usr']['id'];
        } else {
            $where = 'id';
            $wval = $_SESSION['usr']['id'];
         }

//        return $this->t->list(db::read('*')),
        return $this->t->list(db::read('*', '', '', 'ORDER BY `updated` DESC'));
/*
        $pager = util::pager(
            (int) util::ses('p'),
            (int) $this->g->cfg['perp'],
            (int) db::read('count(id)', $where, $wval, '', 'col')
        );

        return $this->t->list(array_merge(
            db::read('*', $where, $wval, 'ORDER BY `updated` DESC LIMIT ' . $pager['start'] . ',' . $pager['perp']),
            ['pager' => $pager]
        ));
*/
    }

    protected function switch_user()
    {
        if (util::is_adm() and !is_null($this->g->in['i'])) {
            $_SESSION['usr'] = db::read('id,acl,grp,login,fname,lname,webpw,cookie', 'id', $this->g->in['i'], '', 'one');
            util::log('Switch to user: ' . $_SESSION['usr']['login'], 'success');
        } else util::log('Not authorized to switch users');
        return $this->list();
    }
}

// lib/php/plugins/vhosts.php 20180512

class Plugins_Vhosts extends Plugin
{
    protected
    $tbl = 'vhosts',
    $in = [
        'active'    => 0,
        'aid'       => 0,
        'aliases'   => 10,
        'diskquota' => 1000000000,
        'domain'    => '',
        'gid'       => 1000,
        'mailboxes' => 1,
        'mailquota' => 500000000,
        'uid'       => 1000,
        'uname'     => '',
        'plan'      => 'personal',
    ];

    protected function create() : string
    {
        if ($_POST) {
            extract($this->in);
            $active = $active ? 1 : 0;

            if (file_exists('/home/u/' . $domain)) {
                util::log('/home/u/' . $domain . ' already exists', 'warning');
                $_POST = []; return $this->t->create($this->in);
            }

            if (!filter_var(gethostbyname($domain . '.'), FILTER_VALIDATE_IP)) {
                util::log("Invalid domain name: gethostbyname($domain)");
                $_POST = []; return $this->t->create($this->in);
            }

//            if ($mailquota > $diskquota) {
//                util::log('Mailbox quota exceeds domain disk quota');
//                $_POST = []; return $this->t->create($this->in);
//            }

            $num_results = db::read('COUNT(id)', 'domain', $domain, '', 'col');

            if ($num_results != 0) {
                util::log('Domain already exists');
                $_POST = []; return $this->t->create($this->in);
            }

            $plan_esc = trim(escapeshellarg($plan), "'");
            $domain_esc = trim(escapeshellarg($domain), "'");
            shell_exec("nohup sh -c 'sudo addvhost $domain_esc $plan_esc' > /tmp/addvhost.log 2>&1 &");
            util::log('Added ' . $domain . ', please wait another few minutes for the setup to complete', 'success');
            util::redirect($this->g->cfg['self'] . '?o=vhosts');
        }
        return $this->t->create($this->in);
    }

    protected function read() : string
    {
        return $this->t->update(db::read('*', 'id', $this->g->in['i'], '', 'one'));
    }

    protected function update() : string
    {
        if ($_POST) {
            extract($this->in);
            $diskquota *= 1000000;
            $mailquota *= 1000000;
            $active = $active ? 1 : 0;

            if ($mailquota > $diskquota) {
                util::log('Mailbox quota exceeds disk quota');
                $_POST = []; return $this->read();
            }

            $size_upath = db::qry("
 SELECT size_upath
   FROM logging
  WHERE name = :name", ['name' => $domain], 'col');

//            if ($mailquota < $size_upath) {
//                util::log('Mailbox quota must be greater than current used diskspace of ' . util::numfmt($size_upath));
//                $_POST = []; return $this->read();
//            }

            if (!filter_var(gethostbyname($domain . '.'), FILTER_VALIDATE_IP)) {
                util::log('Domain name is invalid');
                $_POST = []; return $this->read();
            }

            $sql = "
 UPDATE `vhosts` SET
        `active`    = :active,
        `aliases`   = :aliases,
        `diskquota` = :diskquota,
        `domain`    = :domain,
        `mailboxes` = :mailboxes,
        `mailquota` = :mailquota,
        `updated`   = :updated
  WHERE `id` = :id";

            $res = db::qry($sql, [
                'id'        => $this->g->in['i'],
                'active'    => $active,
                'aliases'   => $aliases,
                'diskquota' => $diskquota,
                'domain'    => $domain,
                'mailboxes' => $mailboxes,
                'mailquota' => $mailquota,
                'updated'   => date('Y-m-d H:i:s'),
            ]);

            util::log('Vhost ID ' . $this->g->in['i'] . ' updated', 'success');
            util::ses('p', '', '1');
            return $this->list();
        } elseif ($this->g->in['i']) {
            return $this->read();
        } else return 'Error updating item';
    }

    protected function delete() : string
    {
        if ($this->g->in['i']) {
            $vhost = db::read('domain', 'id', $this->g->in['i'], '', 'col');
            $vhost_esc = trim(escapeshellarg($vhost), "'");
            shell_exec("nohup sh -c 'sudo delvhost $vhost_esc' > /tmp/delvhost.log 2>&1 &");
            util::log('Removed ' . $vhost, 'success');
            util::redirect($this->g->cfg['self'] . '?o=vhosts');
        }
        return 'Error deleting item';
    }

    protected function list() : string
    {
       if ($this->g->in['x'] === 'json') {
            $columns = [
                ['dt' => 0,  'db' => 'domain',      'formatter' => function($d) { return "<b>$d</b>"; }],
                ['dt' => 1,  'db' => 'num_aliases'],
                ['dt' => 2,  'db' => null,          'formatter' => function($d) { return '/'; } ],
                ['dt' => 3,  'db' => 'aliases'],
                ['dt' => 4,  'db' => 'num_mailboxes'],
                ['dt' => 5,  'db' => null,          'formatter' => function($d) { return '/'; } ],
                ['dt' => 6,  'db' => 'mailboxes'],
                ['dt' => 7,  'db' => 'size_mpath',  'formatter' => function($d) { return util::numfmt(intval($d)); }],
                ['dt' => 8,  'db' => null,          'formatter' => function($d) { return '/'; } ],
                ['dt' => 9,  'db' => 'mailquota',   'formatter' => function($d) { return util::numfmt(intval($d)); }],
                ['dt' => 10, 'db' => 'size_upath',  'formatter' => function($d) { return util::numfmt(intval($d)); }],
                ['dt' => 11, 'db' => null,          'formatter' => function($d) { return '/'; } ],
                ['dt' => 12, 'db' => 'diskquota',   'formatter' => function($d) { return util::numfmt(intval($d)); }],
                ['dt' => 13, 'db' => 'active',      'formatter' => function($d, $row) {
                    $active_buf = $d
                        ? '<i class="fas fa-check text-success"></i>'
                        : '<i class="fas fa-times text-danger"></i>';
                    return $active_buf . '
                    <a class="editlink" href="?o=vhosts&m=update&i=' . $row['id'] . '" title="Update entry for ' . $row['domain'] . '">
                      <i class="fas fa-edit fa-fw cursor-pointer"></i></a>
                    <a href="?o=vhosts&m=delete&i=' . $row['id'] . '" title="Remove Vhost" onClick="javascript: return confirm(\'Are you sure you want to remove: ' . $row['domain'] . '?\')">
                      <i class="fas fa-trash fa-fw cursor-pointer text-danger"></i></a>';
                }],
                ['dt' => 14, 'db' => 'id'],
                ['dt' => 15, 'db' => 'updated'],
            ];
            return json_encode(db::simple($_GET, 'vhosts_view', 'id', $columns), JSON_PRETTY_PRINT);
        }
        return $this->t->list([]);
    }
}

// lib/php/plugins/contact.php 20150101 - 20180503

class Plugins_Contact extends Plugin
{
    public function list() : string
    {
        return $this->t->list([]);
    }
}

// lib/php/plugins/auth.php 20150101 - 20180511

class Plugins_Auth extends Plugin
{
    protected
    $tbl = 'accounts',
    $in = [
        'id'        => null,
        'acl'       => null,
        'grp'       => null,
        'login'     => '',
        'webpw'     => '',
        'remember'  => '',
        'otp'       => '',
        'passwd1'   => '',
        'passwd2'   => '',
    ];

    public function create() : string
    {
        $u = $this->in['login'];

        if ($_POST) {
            if (filter_var($u, FILTER_VALIDATE_EMAIL)) {
                if ($usr = db::read('id,acl', 'login', $u, '', 'one')) {
                    if ($usr['acl'] != 9) {
                        $newpass = util::genpw();
                        if ($this->mail_forgotpw($u, $newpass, 'From: ' . $this->g->cfg['email'])) {
                            db::update([
                                'otp' => $newpass,
                                'otpttl' => time()
                            ], [['id', '=', $usr['id']]]);
                            util::log('Sent reset password key for "' . $u . '" so please check your mailbox and click on the supplied link.', 'success');
                        } else util::log('Problem sending message to ' . $u, 'danger');
                        return $this->t->list(['login' => $u]);
                    } else util::log('Account is disabled, contact your System Administrator');
                } else util::log('User does not exist');
            } else util::log('You must provide a valid email address');
        }
        return $this->t->create(['login' => $u]);
    }

    public function list() : string
    {
        $u = $this->in['login'];
        $p = $this->in['webpw'];
        $c = $this->in['remember'];

        if ($u) {
            if ($usr = db::read('id,grp,acl,login,fname,lname,webpw,cookie', 'login', $u, '', 'one')) {
                extract($usr);
                if ($acl !== 9) {
//                    if ($p == 'changeme') { // for testing a clear text password
                    if (password_verify(html_entity_decode($p), $webpw)) {
                        $uniq = md5(uniqid());
                        if ($c) {
                            db::update(['cookie' => $uniq], [['login', '=', $u]]);
                            util::put_cookie('remember', $uniq, 60*60*24*7);
                            $tmp = $uniq;
                        } else $tmp = '';
                        $_SESSION['usr'] = $usr;
                        util::log($login.' is now logged in', 'success');
                        if ((int) $acl === 0) $_SESSION['adm'] = $id;
                        $_SESSION['m'] = 'list';
                        header('Location: ' . $this->g->cfg['self']);
                        exit();
                    } else util::log('Incorrect password');
                } else util::log('Account is disabled, contact your System Administrator');
            } else util::log('Username does not exist');
        }
        return $this->t->list(['login' => $u]);
    }

    public function update() : string
    {
        $i = !is_null($this->in['id']) ? $this->in['id'] : $_SESSION['usr']['id'];
        $u = !empty($this->in['login']) ? $this->in['login'] : $_SESSION['usr']['login'];

        if ($_POST) {
            if ($usr = db::read('login,acl,otpttl', 'id', $i, '', 'one')) {
                $p1 = html_entity_decode($this->in['passwd1']);
                $p2 = html_entity_decode($this->in['passwd2']);
                if (util::chkpw($p1, $p2)) {
                    if (util::is_usr() or ($usr['otpttl'] && (($usr['otpttl'] + 3600) > time()))) {
                        if (!is_null($usr['acl'])) {
                            if (db::update([
                                    'webpw'   => password_hash($p1, PASSWORD_DEFAULT),
                                    'otp'     => '',
                                    'otpttl'  => '',
                                    'updated' => date('Y-m-d H:i:s'),
                                ], [['id', '=', $i]])) {
                                util::log('Password reset for ' . $usr['login'], 'success');
                                if (util::is_usr()) {
                                    header('Location: ' . $this->g->cfg['self']);
                                    exit();
                                } else return $this->t->list(['login' => $usr['login']]);
                            } else util::log('Problem updating database');
                        } else util::log($usr['login'] . ' is not allowed access');
                    } else util::log('Your one time password key has expired');
                }
            } else util::log('User does not exist');
        }
        return $this->t->update(['id' => $i, 'login' => $u]);
    }

    public function delete() : string
    {
        $u = $_SESSION['usr']['login'];
        if (isset($_SESSION['adm']) and $_SESSION['usr']['id'] === $_SESSION['adm'])
            unset($_SESSION['adm']);
        unset($_SESSION['usr']);
        util::del_cookie('remember');
        util::log($u . ' is now logged out', 'success');
        header('Location: ' . $this->g->cfg['self']);
        exit();
    }

    // Utilities

    public function resetpw() : string
    {
        $otp = html_entity_decode($this->in['otp']);
        if (strlen($otp) === 10) {
            if ($usr = db::read('id,acl,login,otp,otpttl', 'otp', $otp, '', 'one')) {
                extract($usr);
                if ($otpttl && (($otpttl + 3600) > time())) {
                    if ($acl != 3) { // suspended
                        return $this->t->update(['id' => $id, 'login' => $login]);
                    } else util::log($login . ' is not allowed access');
                } else util::log('Your one time password key has expired');
            } else util::log('Your one time password key no longer exists');
        } else util::log('Incorrect one time password key');
        header('Location: ' . $this->g->cfg['self']);
        exit();
    }

    private function mail_forgotpw(string $email, string $newpass, string $headers = '') : bool
    {
        $host = $_SERVER['REQUEST_SCHEME'] . '://'
            . $this->g->cfg['host']
            . $this->g->cfg['self'];
        return mail(
            "$email",
            'Reset password for ' . $this->g->cfg['host'],
'Here is your new OTP (one time password) key that is valid for one hour.

Please click on the link below and continue with reseting your password.

If you did not request this action then please ignore this message.

' . $host . '?o=auth&m=resetpw&otp=' . $newpass,
            $headers
        );
    }
}

// lib/php/plugins/domains.php 20150101 - 20180510

class Plugins_Domains extends Plugin
{
    protected
    $dbh = null,
    $tbl = 'domains',
    $in = [
        'name'        => '',
        'master'      => '',
        'last_check'  => '',
        'disabled'    => 0,
        'type'        => '',
        'notified_serial' => '',
        'account'     => '',
        'increment'   => 0,
    ];

    public function __construct(Theme $t)
    {
        if ($t->g->dns['db']['type'])
            $this->dbh = new db($t->g->dns['db']);
        parent::__construct($t);
    }

    protected function create() : string
    {
        if ($_POST) {
            extract($_POST);
            extract($this->g->dns);
            $created = date('Y-m-d H:i:s');
            $disable = 0;
            $soa_buf =
              $soa['primary'] . $domain . ' ' .
              $soa['email'] . $domain . '. ' .
              date('Ymd') . '00' . ' ' .
              $soa['refresh'] . ' ' .
              $soa['retry'] . ' ' .
              $soa['expire'] . ' ' .
              $soa['ttl'];
            $did = db::create([
                'name'    => $domain,
                'master'  => $type === 'SLAVE' ? $master : '',
                'type'    => $type ? $type : 'MASTER',
                'updated' => $created,
                'created' => $created,
            ]);

            if ($type === 'SLAVE') {
                util::log('Created DNS Zone: ' . $domain, 'success');
                return $this->list();
            }

            $sql = "
 INSERT INTO `records` (
        content, created, disabled, domain_id, name, prio, ttl, type, updated
) VALUES (
        :content, :created, :disabled, :did, :domain, :prio, :ttl, :type, :updated
)";
            db::qry($sql, [
                'content' => $soa_buf,
                'created' => $created,
                'did'     => $did,
                'disabled'=> $disable,
                'domain'  => $domain,
                'prio'    => $prio,
                'ttl'     => $ttl,
                'type'    => 'SOA',
                'updated' => $created,
            ]);
            db::qry($sql, [
                'content' => $ns1 . $domain,
                'created' => $created,
                'did'     => $did,
                'disabled'=> $disable,
                'domain'  => $domain,
                'prio'    => $prio,
                'ttl'     => $ttl,
                'type'    => 'NS',
                'updated' => $created,
            ]);
            db::qry($sql, [
                'content' => $ns2 . $domain,
                'created' => $created,
                'did'     => $did,
                'disabled'=> $disable,
                'domain'  => $domain,
                'prio'    => $prio,
                'ttl'     => $ttl,
                'type'    => 'NS',
                'updated' => $created,
            ]);
            db::qry($sql, [
                'content' => $a,
                'created' => $created,
                'did'     => $did,
                'disabled'=> $disable,
                'domain'  => $domain,
                'prio'    => $prio,
                'ttl'     => $ttl,
                'type'    => 'A',
                'updated' => $created,
            ]);
            db::qry($sql, [
                'content' => $a,
                'created' => $created,
                'did'     => $did,
                'disabled'=> $disable,
                'domain'  => 'cdn.' . $domain,
                'prio'    => $prio,
                'ttl'     => $ttl,
                'type'    => 'A',
                'updated' => $created,
            ]);
            db::qry($sql, [
                'content' => $a,
                'created' => $created,
                'did'     => $did,
                'disabled'=> $disable,
                'domain'  => 'www.' . $domain,
                'prio'    => $prio,
                'ttl'     => $ttl,
                'type'    => 'A',
                'updated' => $created,
            ]);
            db::qry($sql, [
                'content' => $mx . $domain,
                'created' => $created,
                'did'     => $did,
                'disabled'=> $disable,
                'domain'  => $domain,
                'prio'    => $prio,
                'ttl'     => $ttl,
                'type'    => 'MX',
                'updated' => $created,
            ]);
            util::log('Created DNS Zone: ' . $domain, 'success');
            return $this->list();
        }
        return $this->t->create($this->in);
    }

    protected function update() : string
    {
        if ($_POST || $this->in['increment']) {
            if ($this->in['increment']) {
                $sql = "
 SELECT content as soa
   FROM records
  WHERE type='SOA'
    AND domain_id=:did";

                $oldsoa   = explode(' ', db::qry($sql, ['did' => $this->g->in['i']], 'col'));
                $primary  = $oldsoa[0];
                $email    = $oldsoa[1];
                $serial   = $oldsoa[2];
                $refresh  = $oldsoa[3];
                $retry    = $oldsoa[4];
                $expire   = $oldsoa[5];
                $ttl      = $oldsoa[6];
            } else {
                extract($_POST);
            }

            $today = date('Ymd');
            $serial_day = substr($serial, 0, 8);
            $serial_rev = substr($serial, -2);

            $serial = ($serial_day == $today)
                ? "$today" . sprintf("%02d", $serial_rev + 1)
                : "$today" . "00";

            $soa =
              $primary . ' ' .
              $email . ' ' .
              $serial . ' ' .
              $refresh . ' ' .
              $retry . ' ' .
              $expire . ' ' .
              $ttl;

            $sql = "
 UPDATE records SET
        ttl     = :ttl,
        content = :soa,
        updated = :updated
  WHERE type = 'SOA'
    AND domain_id = :did";

            $res = db::qry($sql, [
                'did' => $this->g->in['i'],
                'soa' => $soa,
                'ttl' => $ttl,
                'updated' => date('Y-m-d H:i:s'),
            ]);

            if ($this->in['increment']) return $serial;

            // TODO check $res ???
            util::log('Updated DNS domain ID ' . $this->g->in['i'], 'success');
            return $this->list();

        } elseif ($this->g->in['i']) {

            $dom = db::read('name,type,master', 'id', $this->g->in['i'], '', 'one');
            if ($dom['type'] === 'SLAVE') {
                return $this->t->update($dom);
            } else {
                $sql = "
 SELECT content as soa
   FROM records
  WHERE type='SOA'
    AND domain_id=:did";

                $soa = db::qry($sql, ['did' => $this->g->in['i']], 'one');
                return $this->t->update(array_merge($dom, $soa));
            }
        }
        return 'Error updating item';
    }

    protected function delete() : string
    {
        if ($this->g->in['i']) {
            $sql = "
 DELETE FROM `records`
  WHERE  domain_id = :id";

            $res1 = db::qry($sql, ['id' => $this->g->in['i']]);
            $res2 = db::delete([['id', '=', $this->g->in['i']]]);
            // TODO check $res1 and $res2 ???
            util::log('Deleted DNS zone ID: ' . $this->g->in['i'], 'success');
            util::ses('p', '', '1');
            return $this->list();
        }
        return 'Error deleting item';
    }

    protected function list() : string
    {
        if ($this->g->in['x'] === 'json') {
            $columns = [
                ['dt' => 0,   'db' => 'name',       'formatter' => function($d) { return "<b>$d</b>"; }],
                ['dt' => 1,   'db' => 'type'],
                ['dt' => 2,   'db' => 'records'],
                ['dt' => 3,   'db' => 'soa',        'formatter' => function($d, $row) {
                    $soa = explode(' ', $row['soa']);
                    return ($row['type'] === 'MASTER') ? '
        <a class="serial" href="?o=domains&m=update&i=' . $row['id'] . '" title="Update Serial">' . $soa[2] . '</a>' : $soa[2];
                }],
                ['dt' => 4,   'db' => 'id',         'formatter' => function($d, $row) {
                    return '
                    <a class="editlink" href="?o=records&m=update&i=' . $row['id'] . '" title="Update entry for ' . $row['name'] . '">
                      <i class="fas fa-edit fa-fw cursor-pointer"></i></a>
                    <a href="?o=valias&m=delete&i=' . $row['id'] . '" title="Remove Domain" onClick="javascript: return confirm(\'Are you sure you want to remove: ' . $row['name'] . '?\')">
                      <i class="fas fa-trash fa-fw cursor-pointer text-danger"></i></a>';
                }],
                ['dt' => 5, 'db' => 'updated'],
            ];
            return json_encode(db::simple($_GET, 'domains_view2', 'id', $columns), JSON_PRETTY_PRINT);
        }
        return $this->t->list([]);    
    }
}
// lib/php/plugins/news.php 20150101 - 20170317

class Plugins_News extends Plugin
{
    protected
    $tbl = 'news',
    $in = [
        'title'     => '',
        'media'     => '',
        'author'    => 1,
        'content'   => '',
    ];

    protected function read() : string
    {
        $sql = "
 SELECT n.*, u.id as uid, u.login, u.fname, u.lname
   FROM `news` n
        JOIN `accounts` u
            ON n.author=u.id
  WHERE n.id=:nid";

        return $this->t->read(db::qry($sql, ['nid' => $this->g->in['i']], 'one'));
    }

    protected function update() : string
    {
        if ($_POST) return parent::update();

        $sql = "
 SELECT n.*, u.id as uid, u.login, u.fname, u.lname
   FROM `news` n
        JOIN `accounts` u
            ON n.author=u.id
  WHERE n.id=:nid";

        return $this->t->update(db::qry($sql, ['nid' => $this->g->in['i']], 'one'));
    }

    protected function delete() : string
    {
        if (!util::is_adm()) {
            $author = db::read('author', 'id', $this->g->in['i'], '', 'col');
            if ($_SESSION['usr']['id'] !== $author) {
                util::log('You do not have permissions to delete this post');
                return $this->list();
            }
        }

        return parent::delete();
    }

    protected function list() : string
    {
        $pager = util::pager(
            (int) util::ses('p'),
            (int) $this->g->cfg['perp'],
            (int) db::qry("SELECT count(*) FROM `news` n JOIN `accounts` u ON n.author=u.id", [], 'col')
        );

        $sql = "
 SELECT n.*, u.id as uid, u.login, u.fname, u.lname
   FROM `news` n
        JOIN `accounts` u
            ON n.author=u.id
  ORDER BY n.updated DESC LIMIT " . $pager['start'] . "," . $pager['perp'];

        return $this->t->list(array_merge(db::qry($sql), ['pager' => $pager]));
    }
}

// lib/php/plugins/about.php 20150101 - 20180503

class Plugins_About extends Plugin
{
    public function list() : string
    {
        return $this->t->list([]);
    }
}

// lib/php/themes/bootstrap/mailgraph.php 20170225 - 20170514

class Themes_Bootstrap_MailGraph extends Themes_Bootstrap_Theme
{
    public function list(array $in) : string
    {
        return '
        <h3><i class="fa fa-envelope fa-fw" aria-hidden="true"></i> MailServer Graph</h3>
        <div class="row">
          <div class="col-md-12 text-center">' . $in['mailgraph'] . '
          </div>
        </div>';
    }
}

// lib/php/themes/bootstrap/vhosts.php 20170101 - 20180512

class Themes_Bootstrap_Vhosts extends Themes_Bootstrap_Theme
{
    public function create(array $in) : string
    {
        return $this->editor($in);
    }

    public function update(array $in) : string
    {
        return $this->editor($in);
    }

    public function list(array $in) : string
    {
        // TODO migrate plans to a database table
        $plans = [
            ['Select Plan', ''],
            ['Personal - 1 GB Storage, 1 Domain, 1 Website, 1 Mailbox', 'personal'],
            ['SOHO - 5 GB Storage, 2 Domains, 2 Websites, 5 Mailboxes', 'soho'],
            ['Business - 10 GB Storage, 5 Domains, 5 Websites, 10 Mailboxes', 'business'],
            ['Enterprise - 20 GB Storage, 10 Domains, 10 Websites, 20 Mailboxes', 'enterprise'],
        ];

        $plans_buf = $this->dropdown($plans, 'plan', '', '', 'custom-select');

        return '
        <div class="col-12">
          <h3>
            <i class="fa fa-globe fa-fw"></i> Vhosts
            <a href="#" title="Add new vhost" data-toggle="modal" data-target="#createmodal">
              <small><i class="fas fa-plus-circle fa-fw"></i></small>
            </a>
          </h3>
        </div>
      </div><!-- END UPPER ROW -->
      <div class="row">
        <div class="table-responsive">
          <table id=vhosts class="table table-sm" style="min-width:1100px;table-layout:fixed">
            <thead class="nowrap">
              <tr>
                <th>Domain</th>
                <th>Alias&nbsp;</th>
                <th></th>
                <th></th>
                <th>Mbox&nbsp;</th>
                <th></th>
                <th></th>
                <th>Mail&nbsp;</th>
                <th></th>
                <th></th>
                <th>Disk&nbsp;</th>
                <th></th>
                <th></th>
                <th></th>
              </tr>
            </thead>
            <tfoot>
            </tfoot>
          </table>
        </div>
        <div class="modal fade" id="createmodal" tabindex="-1" role="dialog" aria-labelledby="createmodal" aria-hidden="true">
          <div class="modal-dialog" role="document">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title">Vhosts</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>
              <form method="post" action="' . $this->g->cfg['self'] . '">
                <div class="modal-body">
                  <input type="hidden" name="c" value="' . $_SESSION['c'] . '">
                  <input type="hidden" name="o" value="' . $this->g->in['o'] . '">
                  <input type="hidden" name="i" value="' . $this->g->in['i'] . '">
                  <input type="hidden" name="m" value="create">
                  <div class="form-group">
                    <label for="domain" class="form-control-label">Vhost</label>
                    <input type="text" class="form-control" id="domain" name="domain">
                  </div>
                  <div class="form-group">
                    <label for="plan" class="form-control-label">Plan</label>' . $plans_buf . '
                  </div>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                  <button type="submit" class="btn btn-primary">Add New Vhost</button>
                </div>
              </form>
            </div>
          </div>
        </div>
        <script>
$(document).ready(function() {
  $("#vhosts").DataTable({
    "processing": true,
    "serverSide": true,
    "ajax": "?x=json&o=vhosts&m=list",
    "order": [[ 15, "desc" ]],
    "columnDefs": [
      {"targets":0,   "className":"text-truncate", "width":"25%"},
      {"targets":1,   "className":"text-right", "width":"3rem"},
      {"targets":2,   "className":"text-center", "width":"0.5rem", "sortable": false},
      {"targets":3,   "width":"3rem"},
      {"targets":4,   "className":"text-right", "width":"3rem"},
      {"targets":5,   "className":"text-center", "width":"0.5rem", "sortable": false},
      {"targets":6,   "width":"3rem"},
      {"targets":7,   "className":"text-right", "width":"4rem"},
      {"targets":8,   "className":"text-center", "width":"0.5rem", "sortable": false},
      {"targets":9,   "width":"4rem"},
      {"targets":10,  "className":"text-right", "width":"4rem"},
      {"targets":11,  "className":"text-center", "width":"0.5rem", "sortable": false},
      {"targets":12,  "width":"4rem"},
      {"targets":13,  "className":"text-right", "width":"4rem", "sortable": false},
      {"targets":14,  "visible":false, "sortable": true},
      {"targets":15,  "visible":false, "sortable": true},
    ]
  });
});
        </script>';
    }

    private function editor(array $in) : string
    {
        extract($in);

        $active = $active ? 1 : 0;
        $header = $this->g->in['m'] === 'create' ? 'Add Vhost' : $domain;
        $submit = $this->g->in['m'] === 'create' ? '
                <a class="btn btn-secondary" href="?o=vhosts&m=list">&laquo; Back</a>
                <button type="submit" name="m" value="create" class="btn btn-primary">Add</button>' : '
                <a class="btn btn-secondary" href="?o=vhosts&m=list">&laquo; Back</a>
                <a class="btn btn-danger" href="?o=vhosts&m=delete&i=' . $this->g->in['i'] . '" title="Remove Vhost" onClick="javascript: return confirm(\'Are you sure you want to remove ' . $domain . '?\')">Remove</a>
                <button type="submit" name="m" value="update" class="btn btn-primary">Update</button>';
        $enable = $this->g->in['m'] === 'create' ? '
                <input type="text" autocorrect="off" autocapitalize="none" class="form-control" name="domain" id="domain" value="' . $domain . '">' : '
                <input type="text" class="form-control" value="' . $domain . '" disabled>
                <input type="hidden" name="domain" id="domain" value="' . $domain . '">';

        $checked = $active ? ' checked' : '';

        return '
          <div class="col-12">
            <h3><a href="?o=vhosts&m=list">&laquo;</a> ' . $header . '</h3>
          </div>
        </div><!-- END UPPER ROW -->
        <div class="row">
          <div class="col-12">
            <form method="post" action="' . $this->g->cfg['self'] . '">
              <input type="hidden" name="c" value="' . $_SESSION['c'] . '">
              <input type="hidden" name="o" value="' . $this->g->in['o'] . '">
              <input type="hidden" name="i" value="' . $this->g->in['i'] . '">
              <div class="row">
                <div class="form-group col-12 col-md-6 col-lg-4">
                  <label for="domain">Domain</label>' . $enable . '
                </div>
                <div class="form-group col-6 col-md-3 col-lg-2">
                  <label for="aliases">Max Aliases</label>
                  <input type="number" class="form-control" name="aliases" id="aliases" value="' . $aliases . '">
                </div>
                <div class="form-group col-6 col-md-3 col-lg-2">
                  <label for="mailboxes">Max Mailboxes</label>
                  <input type="number" class="form-control" name="mailboxes" id="mailboxes" value="' . $mailboxes . '">
                </div>
                <div class="form-group col-6 col-md-3 col-lg-2">
                  <label for="mailquota">Mail Quota (MB)</label>
                  <input type="number" class="form-control" name="mailquota" id="mailquota" value="' . intval($mailquota / 1000000) . '">
                </div>
                <div class="form-group col-6 col-md-3 col-lg-2">
                  <label for="diskquota">Disk Quota (MB)</label>
                  <input type="number" class="form-control" name="diskquota" id="diskquota" value="' . intval($diskquota / 1000000) . '">
                </div>
              </div>
              <div class="row">
                <div class="col-12 col-sm-6">
                  <div class="form-group">
                    <div class="custom-control custom-checkbox">
                      <input type="checkbox" class="custom-control-input" name="active" id="active"' . $checked . '>
                      <label class="custom-control-label" for="active">Active</label>
                    </div>
                  </div>
                </div>
                <div class="col-12 col-sm-6 text-right">
                  <div class="btn-group">' . $submit . '
                  </div>
                </div>
              </div>
            </form>
          </div>';
    }
}

// lib/php/themes/bootstrap/domains.php 20170225 - 20180512

class Themes_Bootstrap_Domains extends Themes_Bootstrap_Theme
{
    public function create(array $in) : string
    {
        return $this->editor($in);
    }

    public function update(array $in) : string
    {
        return $this->editor($in);
    }

    public function list(array $in) : string
    {
        return '
        <div class="col-12">
          <h3>
            <i class="fas fa-globe fa-fw"></i> Domains
            <a href="#" title="Add new domain" data-toggle="modal" data-target="#createmodal">
              <small><i class="fas fa-plus-circle fa-fw"></i></small>
            </a>
          </h3>
        </div>
      </div><!-- END UPPER ROW -->
      <div class="row">
        <div class="table-responsive">
          <table id=domains class="table table-sm" style="min-width:1100px;table-layout:fixed">
            <thead>
              <tr>
                <th>Name</th>
                <th>Type</th>
                <th>Records</th>
                <th>Serial</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
            </tbody>
          </table>
        </div>
      </div>
      <div class="modal fade" id="createmodal" tabindex="-1" role="dialog" aria-labelledby="createmodal" aria-hidden="true">
        <div class="modal-dialog" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">Domain</h5>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
              <form method="post" action="' . $this->g->cfg['self'] . '">
            <div class="modal-body">
              <input type="hidden" name="c" value="' . $_SESSION['c'] . '">
              <input type="hidden" name="o" value="' . $this->g->in['o'] . '">
              <input type="hidden" name="i" value="' . $this->g->in['i'] . '">
              <input type="hidden" name="m" value="create">
              <div class="form-group">
                <label for="domain" class="form-control-label">Name</label>
                <input type="text" class="form-control" id="domain" name="domain">
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
              <button type="submit" class="btn btn-primary">Add New Domain</button>
            </div>
              </form>
          </div>
        </div>
        <script>
$(document).ready(function() {
  $("#domains").DataTable({
    "processing": true,
    "serverSide": true,
    "ajax": "?x=json&o=domains&m=list",
    "order": [[ 5, "desc" ]],
    "columnDefs": [
      {"targets":0,   "className":"text-truncate", "width":"25%"},
      {"targets":4,   "className":"text-right", "width":"4rem", "sortable": false},
      {"targets":5,   "visible":false},
    ],
  });
  $(document).on("click", ".serial", {}, (function() {
    var a = $(this)
    $.post("?x=text&increment=1&" + this.toString().split("?")[1], function(data) {
      $(a).text(data);
    });
    return false;
  }));
});
        </script>';
    }

    private function editor(array $in) : string
    {
        $domain = $in['name'];
        $soa = isset($in['soa'])
            ? explode(' ', $in['soa'])
            : ['', '', '', 7200, 540, 604800, 300];

        if ($this->g->in['m'] === 'create') {
            $serial = $hidden = '';
            $header = 'Add Domain';
            $submit = '
                <a class="btn btn-secondary" href="?o=domains&m=list">&laquo; Back</a>
                <button type="submit" id="m" name="m" value="create" class="btn btn-primary">Add Domain</button>';
        } else {
            $serial = '&nbsp;&nbsp;<small>Serial: ' . $soa[2] . '</small>';
            $header = $domain;
            $submit = '
                <a class="btn btn-secondary" href="?o=domains&m=list">&laquo; Back</a>
                <button type="submit" id="m" name="m" value="update" class="btn btn-primary">Update</button>';
            $hidden = '
            <input type="hidden" name="serial" value="' . $soa[2] . '">';
        }

        return '
          <div class="col-12">
            <h3><a href="?o=domains&m=list">&laquo;</a> ' . $header . $serial . '</h3>
          </div>
        </div><!-- END UPPER ROW -->
        <div class="row">
          <div class="col-12">
            <form method="post" action="' . $this->g->cfg['self'] . '">
              <input type="hidden" name="c" value="' . $_SESSION['c'] . '">
              <input type="hidden" name="o" value="' . $this->g->in['o'] . '">
              <input type="hidden" name="i" value="' . $this->g->in['i'] . '">' . $hidden . '
              <div class="row">
                <div class="col-3">
                  <div class="form-group">
                    <label for="primary">Primary</label>
                    <input type="text" class="form-control" id="primary" name="primary" value="' . $soa[0] . '" required>
                  </div>
                </div>
                <div class="col-3">
                  <div class="form-group">
                    <label for="email">Email</label>
                    <input type="text" class="form-control" id="email" name="email" value="' . $soa[1] . '" required>
                  </div>
                </div>
                <div class="col-1">
                  <div class="form-group">
                    <label for="refresh">Refresh</label>
                    <input type="text" class="form-control" id="refresh" name="refresh" value="' . $soa[3] . '" required>
                  </div>
                </div>
                <div class="col-1">
                  <div class="form-group">
                    <label for="retry">Retry</label>
                    <input type="text" class="form-control" id="retry" name="retry" value="' . $soa[4] . '" required>
                  </div>
                </div>
                <div class="col-2">
                  <div class="form-group">
                    <label for="expire">Expire</label>
                    <input type="text" class="form-control" id="expire" name="expire" value="' . $soa[5] . '" required>
                  </div>
                </div>
                <div class="col-2">
                  <div class="form-group">
                    <label for="ttl">TTL</label>
                    <input type="text" class="form-control" id="ttl" name="ttl" value="' . $soa[6] . '" required>
                  </div>
                </div>
              </div>
              <div class="row">
                <div class="col-12 text-right">
                  <div class="btn-group">' . $submit . '
                  </div>
                </div>
              </div>
            </form>
          </div>';
    }
}
// lib/php/themes/bootstrap/mail/domainaliases.php 20170225

class Themes_Bootstrap_Mail_DomainAlias extends Themes_Bootstrap_Theme
{
    public function create(array $in) : string
    {
        return $this->editor($in);
    }

    public function update(array $in) : string
    {
        return $this->editor($in);
    }

    public function list(array $in) : string
    {
        $buf = $pgr_top = $pgr_end = '';
        $pgr = $in['pager']; unset($in['pager']);

        if ($pgr['last'] > 1) {
            $pgr_top ='
          <div class="col-md-6">' . $this->pager($pgr) . '
          </div>';
            $pgr_end = '
          <div class="row">
            <div class="col-12">' . $this->pager($pgr) . '
            </div>
          </div>';
        }

        foreach($in as $row) {
            extract($row);
            $active_buf = $active
                ? '<i class="fa fa-check text-success"></i>'
                : '<i class="fa fa-times text-danger"></i>';

            $buf .= '
                <tr id="data">
                  <td><a href="?o=mail_domainalias&m=update&i=' . $id . '"><strong>' . $alias_domain . '<strong></a></td>
                  <td>' . $target_domain . '</td>
                  <td>' . $active_buf . '</td>
                </tr>';
        }

        if (empty($buf)) $buf .= '
                <tr>
                  <td colspan="3" class="text-center">No Records</td>
                </tr>';

        return '
        <div class="row">
          <div class="col-md-6">
            <h3 class="min600">
              <a href="?o=mail_domainalias&m=create" title="Add DomainAlias">
                <i class="fa fa-globe fa-fw"></i> Domain Aliases
                <small><i class="fa fa-plus-circle fa-fw"></i></small>
              </a>
            </h3>
          </div>' . $pgr_top . '
        </div>
        <div class="table-responsive">
          <table class="table table-sm min600">
            <thead class="nowrap">
              <tr class="bg-primary text-white">
                <th class="min100">Alias</th>
                <th class="min150">Target Domain</th>
                <th class="min50">Active</th>
              </tr>
            </thead>
            <tbody>' . $buf . '
            </tbody>
          </table>
        </div>' . $pgr_end;
    }

    private function editor(array $in) : string
    {

        extract($in);

        $options = '';
        $checked = $active ? ' checked' : '';
        $tmp =  db::qry("SELECT `domain` FROM `domain`");
        $rows = [];
        foreach($tmp as $r) $rows[] = [$r['domain'], $r['domain']];
        $options = $this->dropdown(
            $rows,
            'target_domain',
            $target_domain,
            'Please select...',
            'custom-select'
        );

        $header = $this->g->in['m'] === 'create' ? 'Add Domain Alias' : 'Update Domain Alias';
        $submit = $this->g->in['m'] === 'create' ? '
                      <a class="btn btn-secondary" href="?o=mail_domainalias&m=list">&laquo; Back</a>
                      <button type="submit" name="m" value="create" class="btn btn-primary">Add Domain Alias</button>' : '
                      <a class="btn btn-secondary" href="?o=mail_domainalias&m=list">&laquo; Back</a>
                      <a class="btn btn-danger" href="?o=mail_domainalias&m=delete&i=' . $id . '" title="Remove domain" onClick="javascript: return confirm(\'Are you sure you want to remove ' . $alias_domain . '?\')">Remove</a>
                      <button type="submit" name="m" value="update" class="btn btn-primary">Update</button>';

        return '
          <h3 class="w30">
            <a href="?o=mail_domainalias&m=list">
              <i class="fa fa-globe fa-fw"></i> ' . $header . '
            </a>
          </h3>
          <form method="post" action="' . $this->g->cfg['self'] . '">
            <input type="hidden" name="o" value="' . $this->g->in['o'] . '">
            <input type="hidden" name="i" value="' . $this->g->in['i'] . '">
            <div class="row">
              <div class="form-group col-md-6">
                <label for="alias_domain">Alias domain</label>
                <textarea class="form-control" rows="4" name="alias_domain" id="alias_domain">' . $alias_domain . '</textarea>
                <p>Valid domain names only (comma-separated)</p>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label class="control-label" for="target_domain">Target domain</label>
                  <br>' . $options . '
                </div>
                <div class="row">
                  <div class="form-group col-md-6">
                    <label class="custom-control custom-checkbox">
                      <input type="checkbox" class="custom-control-input" name="active" id="active"' . $checked . '>
                      <span class="custom-control-indicator"></span>
                      <span class="custom-control-description">Active</span>
                    </label>
                  </div>
                  <div class="col-md-6 text-right">
                    <div class="btn-group">' . $submit . '
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </form>';
    }
}

// lib/php/themes/bootstrap/about.php 20150101 - 20180512

class Themes_Bootstrap_About extends Themes_Bootstrap_Theme
{
    public function list(array $in) : string
    {
        // TODO change the a class btn links to form input submits
        return '
      <div class="col-12">
        <h3>About</h3>
        <p class="columns">
This is an example of a simple PHP7 "framework" to provide the core
structure for further experimental development with both the framework
design and some of the new features of PHP7.
        </p>
        <form method="post">
          <input type="hidden" name="c" value="' . $_SESSION['c'] . '">
          <p class="text-center">
            <a class="btn btn-success" href="?o=about&l=success:Howdy, all is okay.">Success Message</a>
            <a class="btn btn-danger" href="?o=about&l=danger:Houston, we have a problem.">Danger Message</a>
            <a class="btn btn-secondary" href="" onclick="ajax(\'json\');return false;">JSON</a>
            <a class="btn btn-secondary" href="" onclick="ajax(\'head\');return false;">HTML</a>
            <a class="btn btn-secondary" href="" onclick="ajax(\'foot\');return false;">FOOT</a>
          </p>
        </form>
        <pre id="dbg"></pre>
      </div>
      <script>
function ajax(a) {
  if (window.XMLHttpRequest)  {
    var x = new XMLHttpRequest();
    x.open("POST", "", true);
    x.onreadystatechange = function() {
      if (x.readyState == 4 && x.status == 200) {
        document.getElementById("dbg").innerHTML = x.responseText
          .replace(/</g,"&lt;")
          .replace(/>/g,"&gt;")
          .replace(/\\\n/g,"\n")
          .replace(/\\\/g,"");
    }}
    x.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    x.send("o=about&x="+a);
    return false;
  }
}
      </script>';
    }
}

// lib/php/themes/bootstrap/infomail.php 20170225 - 20180512

class Themes_Bootstrap_InfoMail extends Themes_Bootstrap_Theme
{
    public function list(array $in) : string
    {
        extract($in);

        return '
          <div class="col-6">
            <h3><i class="fas fa-envelope fa-fw"></i> MailServer Info</h3>
          </div>
          <div class="col-6">
            <form method="post" class="form-inline">
              <input type="hidden" name="c" value="' . $_SESSION['c'] . '">
              <input type="hidden" name="m" value="pflog_renew">
              <div class="form-group ml-auto">
                <button type="submit" class="btn btn-primary"><i class="fas fa-sync-alt fa-fw" aria-hidden="true"></i> Refreshed ' . $pflog_time . ' ago</button>
              </div>
            </form>
          </div>
        </div><!-- END UPPER ROW -->
        <div class="row">
          <div class="col-12">
            <h5>Mail Queue</h5>
            <pre>' . $mailq . '</pre>
          </div>
        </div>
        <div class="row">
          <div class="col-12">
            <pre>' . $pflogs . '
            </pre>
          </div>
        </div>';
    }
}
//            <textarea rows="20" style="font-family:monospace;font-size:9pt;width:100%;">' . $pflogs . '</textarea>

// lib/php/themes/bootstrap/contact.php 20150101 - 20170317

class Themes_Bootstrap_Contact extends Themes_Bootstrap_Theme
{
    public function list(array $in) : string
    {
        return '
        <div class="col-md-4 offset-md-4">
          <h3><i class="fa fa-envelope"></i> Contact us</h3>
          <form action="' . $this->g->cfg['self'] . '" method="post" onsubmit="return mailform(this)">
            <input type="hidden" name="o" value="auth">
            <div class="form-group">
              <label for="subject">Subject</label>
              <input type="text" class="form-control" id="subject" placeholder="Your Subject" required>
            </div>
            <div class="form-group">
              <label for="message">Message</label>
              <textarea class="form-control" id="message" rows="9" placeholder="Your Message" required></textarea>
            </div>
            <small class="form-text text-muted text-center">
              Submitting this form will attempt to start your local mail program. If it does not work then you may have to configure your browser to recognize mailto: links.
            </small>
            <div class="form-group text-right">
              <div class="btn-group">
                <button class="btn btn-primary" type="submit">Send</button>
              </div>
            </div>
          </form>
        </div>';
    }
}

// lib/php/themes/bootstrap/dkim.php 20180511 - 20180511

class Themes_Bootstrap_Dkim extends Themes_Bootstrap_Theme
{
     public function list(array $in) : string
    {
       return '
        <div class="col-12">
          <h3>
            <i class="fas fa-address-card fa-fw"></i> DKIM
            <a href="#" title="Add New DKIM" data-toggle="modal" data-target="#createmodal">
              <small><i class="fas fa-plus-circle fa-fw"></i></small>
            </a>
          </h3>
        </div>
      </div><!-- END UPPER ROW -->
      <div class="row">
        <div class="col-12">
          <p>DKIM records will appear here... </p>
          <pre>' . $in['buf'] . '</pre>
        </div>
      </div>
    </div>';
    }
}

// lib/php/themes/bootstrap/home.php 20150101 - 20180503

class Themes_Bootstrap_Home extends Themes_Bootstrap_Theme
{
    public function list(array $in) : string
    {
        return '
        <div class="col-12">
      <h3>
        <i class="fas fa-server fa-fw"></i> NetServa HCP
      </h3>
      <p class="columns">
This is an ultra simple web based <b>Hosting Control Panel</b> for a 
lightweight Mail, Web and DNS server based on Ubuntu Bionic (18.04). It 
uses PowerDNS for DNS, Postfix/Dovecot + Spamprobe for SMTP and spam 
filtered IMAP email hosting along with nginx + PHP7 FPM + LetsEncrypt SSL 
for efficient and secure websites. It can use either SQLite or MySQL as 
database backends and the SQLite version only requires <b>60Mb</b> of ram 
on a fresh install so it is ideal for lightweight 256Mb ram LXD containers 
or KVM/Xen cloud provisioning.
      </p>
      <p>
Some of the features are...
      </p>
      <ul>
        <li><b>NetServa HCP</b> does not reqire Python or Ruby, just PHP and Bash</li>
        <li>Fully functional Mail server with personalised Spam filtering</li>
        <li>Secure SSL enabled <a href="http://nginx.org/">nginx</a> web server with <a href="http://www.php.net/manual/en/install.fpm.php">PHP FPM 7+</a></li>
        <li>Always based and tested on the latest release of <a href="https://kubuntu.org">*buntu</a></li>
        <li>Optional DNS server for local LAN or real-world DNS provisioning</li>
        <li>Built from the ground up using <a href="https://getbootstrap.com">Bootstrap 4</a> and <a href="https://datatables.net/examples/styling/bootstrap4">DataTables</a></li>
      </ul>
      <p class="columns">
You can change the content of this page by editing where ever this
<a href="https://github.com/netserva/hcp/blob/master/lib/php/themes/bootstrap/home.php">
home.php</a> theme file ends up on your system. Modifying the navigation
menus above can be done by creating a <code>lib/.ht_conf.php</code> file and
copying the <a href="https://github.com/netserva/hcp/blob/master/index.php#L62">
$nav1 array</a> from <code>index.php</code> into that optional config override file.
Comments and pull requests are most welcome via the Issue Tracker link below.
      </p>
      <p class="text-center">
        <a class="btn btn-primary" href="https://github.com/netserva/hcp">
          <i class="fas fa-code-branch fa-fw"></i> Project Page</a>
        <a class="btn btn-primary" href="https://github.com/netserva/hcp/issues">
          <i class="fas fa-ticket-alt fa-fw"></i> Issue Tracker</a>
      </p>
      </div>';        
    }
}

// lib/php/themes/bootstrap/processes.php 20170225 - 20180512

class Themes_Bootstrap_Processes extends Themes_Bootstrap_Theme
{
    public function list(array $in) : string
    {
        return '
          <div class="col-12 col-sm-6">
            <h3><i class="fas fa-code-branch fa-fw"></i> Processes</h3>
          </div>
          <div class="col-12 col-sm-6">
            <form method="post" class="form-inline">
              <input type="hidden" name="c" value="' . $_SESSION['c'] . '">
              <input type="hidden" id="o" name="o" value="processes">
              <div class="form-group ml-auto">
                <button type="submit" class="btn btn-primary"><i class="fas fa-sync-alt fa-fw" aria-hidden="true"></i> Refresh</button>
              </div>
            </form>
          </div>
        </div><!-- END UPPER ROW -->
        <div class="row">
          <div class="col-12">
            <h5>Process List <small>(' . (count(explode("\n", $in['procs'])) - 1) . ')</small></h5>
            <pre><code>' . $in['procs'] . '
            </code></pre>
          </div>
        </div>';
    }
}

// lib/php/themes/bootstrap/news.php 20170225 - 20180512

class Themes_Bootstrap_News extends Themes_Bootstrap_Theme
{
    public function create(array $in) : string
    {
        return $this->editor($in);
    }

    public function read(array $in) : string
    {
        extract($in);

        $author_buf = $fname && $lname
            ? $fname . ' ' . $lname
            : ($fname && empty($lname) ? $fname : $login);

        $media = $media ?
              '<img src="' . $media . '" alt="' . $title . ' Image">' :
              '<div class="media-blank"></div>';

        return '
          <div class="col-12">

            <h2 class=my-0><a href="?o=news&m=list" title="Go back to list">&laquo;</a> ' . $title . '
            </h2>
          </div>
          <div class="col-12">

            <div class="media text-muted">
                <div class="media-img">' . $media . '
                  <small class="text-center">
                    <i>by <a href="?o=accounts&m=update&i=' . $uid . '">' . $author_buf . '</a>
                    <br>' . util::now($updated) . '
                    </i>
                  </small>
                </div>
              <div class="media-body">' . nl2br($content) . '
              </div>
            </div>

          </div>
          <div class="col-12 text-right mt-4">
            <div class="btn-group">
              <a class="btn btn-secondary" href="?o=news&m=list">&laquo; Back</a>
              <a class="btn btn-danger" href="?o=news&m=delete&i=' . $id . '" title="Remove this item" onClick="javascript: return confirm(\'Are you sure you want to remove ' . $title . '?\')">Remove</a>
              <a class="btn btn-primary" href="?o=news&m=update&i=' . $id . '">Update</a>
            </div>
          </div>';
    }

    public function update(array $in) : string
    {
        if (!util::is_adm() && ($_SESSION['usr']['id'] !== $in['author'])) {
            util::log('You do not have permissions to update this post');
            return $this->read($in);
        }

        return $this->editor($in);
    }

    public function list(array $in) : string
    {
        $buf = $pgr_top = $pgr_end = '';
        $pgr = $in['pager']; unset($in['pager']);

        if ($pgr['last'] > 1) {
            $pgr_top = $this->pager($pgr);
            $pgr_end = '
          <div class="col-12">' . $this->pager($pgr) . '
          </div>';
        }

        foreach($in as $row) {
            extract($row);
            $author_buf = $fname && $lname
                ? $fname . ' ' . $lname
                : ($fname && empty($lname) ? $fname : $login);

            $media = $media ? '
              <img src="' . $media . '" alt="' . $title . ' Image">' : '
              <div class="media-blank"></div>';

            $content = strlen($content) > 400 ? mb_strimwidth($content, 0, 396, '...') : $content;

            $buf .= '
            <div class="media">' . $media . '
              <div class="media-body text-muted">
                <div class="media-title">
                  <h4 class="mb-0">
                    <a href="?o=news&m=read&i=' . $id . '" title="Show item ' . $id . '">' . $title . '</a>
                  </h4>
                  <small>
                    <i>by <a href="?o=accounts&m=update&i=' . $uid . '">' . $author_buf . '</a>
                    - ' . util::now($updated) . '
                    </i>
                  </small>
                </div>' . nl2br($content) . '
              </div>
            </div>
            <hr>';
        }

        return '
          <div class="col-12">
            <h2 class="my-0"><i class="fas fa-newspaper"></i> News
              <a href="?o=news&m=create" title="Add news item">
                <i class="fas fa-plus-circle fa-xs"></i>
              </a>
            </h2>
          </div>
          <div class="col-12">' . $buf . '
          </div>' . $pgr_end;
    }

    private function editor(array $in) : string
    {
        if ($this->g->in['m'] === 'create') {
            extract($_SESSION['usr']);
            $author = $uid = $id;
            $header = 'Add News';
            $submit = '
                <a class="btn btn-secondary" href="?o=news&m=list">&laquo; Back</a>
                <button type="submit" class="btn btn-primary">Add This Item</button>';
        } else {
            extract($in);
            $header = 'Update News';
            $submit = '
                <a class="btn btn-secondary" href="?o=news&m=read&i=' . $id . '">&laquo; Back</a>
                <a class="btn btn-danger" href="?o=news&m=delete&i=' . $id . '" title="Remove this item" onClick="javascript: return confirm(\'Are you sure you want to remove ' . $title . '?\')">Remove</a>
                <button type="submit" name="i" value="' . $id . '" class="btn btn-primary">Update</button>';
        }

        $author_label = $fname && $lname
            ? $fname . ' ' . $lname
            : ($fname && empty($lname) ? $fname : $login);

        $author_buf = '
                  <p class="form-control-static"><b><a href="?o=accounts&m=update&i=' . $uid . '">' . $author_label . '</a></b></p>';
        $media = $media ?? '';

        return '
          <div class="col-12">
            <h2 class=my-0>
              <a href="?o=news&m=list" title="Go back to list">&laquo;</a> ' . $header . '
            </h2>
          </div>
            <form class="col-12" method="post" action="' . $this->g->cfg['self'] . '">
              <input type="hidden" name="c" value="' . $_SESSION['c'] . '">
              <input type="hidden" name="o" value="' . $this->g->in['o'] . '">
              <input type="hidden" name="m" value="' . $this->g->in['m'] . '">
              <input type="hidden" name="author" value="' . $uid . '">
              <div class="form-row">
                <div class="col-md-4">
                  <div class="form-group">
                    <label for="title">Title</label>
                    <input type="text" class="form-control" id="title" name="title" value="' . $title . '" required>
                  </div>
                  <div class="form-group">
                    <label for="media">Media</label>
                    <input type="text" class="form-control" id="media" name="media" value="' . $media . '">
                  </div>
                  <div class="form-group">
                    <label for="author">Author</label>' . $author_buf . '
                  </div>
                </div>
                <div class="col-md-8">
                  <div class="form-group">
                    <label for="content">Content</label>
                    <textarea class="form-control" id="content" name="content" rows="12" required>' . $content . '</textarea>
                  </div>
                </div>
              </div>
              <div class="col-12 text-right">
                <div class="btn-group">' . $submit . '
                </div>
              </div>
            </form>';
    }
}

// lib/php/themes/bootstrap/records.php 20180323

class Themes_Bootstrap_Records extends Themes_Bootstrap_Theme
{
    public function create(array $in) : string
    {
        return $this->editor($in);
    }

    public function list(array $in) : string
    {
        $buf = '';
        $domain = $in['domain']; unset($in['domain']);
        $active_buf = (isset($disabled) && $disabled == 0)
            ? '<i class="fa fa-check text-success"></i>'
            : '<i class="fa fa-times text-danger"></i>';

        foreach ($in as $row) {
            extract($row);
            $buf .= '
                <tr>
                  <td class="nowrap">
                    <a href="?o=records&m=read&i=' . $id . '" title="Show record ' . $id . '">
                      <strong>' . $name . '</strong>
                    </a>
                  </td>
                  <td>' . $type . '
                  </td>
                  <td class="nowrap ellide">' . $content . '
                  </td>
                  <td>' . $priority . '
                  </td>
                  <td>' . $ttl . '
                  </td>
                </tr>';
        }

        return '
          <div class="col-12">
            <h3>
              <i class="fas fa-globe fa-fw"></i> ' . $domain . '
              <a href="?o=records&m=create&domain=' . $domain . '" title="Add new DNS record">
                <small><i class="fas fa-plus-circle fa-fw"></i></small>
              </a>
            </h3>
          </div>
        </div><!-- END UPPER ROW -->
        <div class="row">
          <table id=records class="table table-sm">
            <thead>
              <tr>
                <th>Name</th>
                <th>Type</th>
                <th>Content</th>
                <th>Priority</th>
                <th>TTL</th>
                <th>&nbsp;&nbsp;</th>
              </tr>
            </thead>
            <tbody>' . $buf . '
            </tbody>
          </table>
        </div>
        <div class="row">
          <div class="col-12 text-right">
            <div class="btn-group">
              <a class="btn btn-secondary" href="?o=domains&m=list">&laquo; Back</a>
              <a class="btn btn-danger" href="?o=domains&m=delete&i=' . $this->g->in['i'] . '" title="Remove this item" onClick="javascript: return confirm(\'Are you sure you want to remove ' . $domain . '?\')">Remove</a>
              <a class="btn btn-primary" href="?o=domains&m=update&i=' . $this->g->in['i'] . '">Update</a>
            </div>
          </div>
        </div>
        <script>$(document).ready(function() { $("#records").DataTable(); });</script>';

    }

    public function update(array $in) : string
    {
        return $this->editor($in);
    }

    private function editor(array $in) : string
    {

        $buf = '';
        $types = [
            ['A',          'A'],
            ['AAAA',       'AAAA'],
            ['AFSDB',      'AFSDB'],
            ['CERT',       'CERT'],
            ['CNAME',      'CNAME'],
            ['DHCID',      'DHCID'],
            ['DLV',        'DLV'],
            ['DNSKEY',     'DNSKEY'],
            ['DS',         'DS'],
            ['EUI48',      'EUI48'],
            ['EUI64',      'EUI64'],
            ['HINFO',      'HINFO'],
            ['IPSECKEY',   'IPSECKEY'],
            ['KEY',        'KEY'],
            ['KX',         'KX'],
            ['LOC',        'LOC'],
            ['MINFO',      'MINFO'],
            ['MR',         'MR'],
            ['MX',         'MX'],
            ['NAPTR',      'NAPTR'],
            ['NS',         'NS'],
            ['NSEC',       'NSEC'],
            ['NSEC3',      'NSEC3'],
            ['NSEC3PARAM', 'NSEC3PARAM'],
            ['OPT',        'OPT'],
            ['PTR',        'PTR'],
            ['RKEY',       'RKEY'],
            ['RP',         'RP'],
            ['RRSIG',      'RRSIG'],
            ['SPF',        'SPF'],
            ['SRV',        'SRV'],
            ['SSHFP',      'SSHFP'],
            ['TLSA',       'TLSA'],
            ['TSIG',       'TSIG'],
            ['TXT',        'TXT'],
            ['WKS',        'WKS'],
        ];
        $domain = $in['domain']; unset($in['domain']);
        $domain_id = $in['domain_id']; unset($in['domain_id']);
        $options = $this->dropdown(
            $types,
            'type',
            'A',
            '',
            'custom-select'
        );

        foreach ($in as $row) {
            extract($row);
            $active = $disabled == 0 ? 1 : 0;
            $active_buf = $active
                ? '<i class="fas fa-check fa-fw text-success"></i>'
                : '<i class="fas fa-times fa-fw text-danger"></i>';
            $buf .= '
                <tr class="editrow" data-rowid="' . $id . '" data-active="' . $active . '">
                  <td class="text-truncate"><b title="DNS record ID: ' . $id . '">' . $name . '</b></td>
                  <td>' . $type . '</td>
                  <td class="text-truncate">' . $content . '</td>
                  <td class="text-right">' . $priority . '</td>
                  <td class="text-right">' . $ttl . '</td>
                  <td class="text-right">' . $active_buf . '
                    <a class="editlink" href="#" title="Update DNS record ID: ' . $id . '">
                      <i class="fas fa-edit fa-fw cursor-pointer"></i></a>
                    <a href="?o=records&m=delete&i=' . $id . '&domain_id=' . $domain_id . '" title="Remove DNS record ID: ' . $id . '" onClick="javascript: return confirm(\'Are you sure you want to remove record ID: ' . $id . '?\')">
                      <i class="fas fa-trash fa-fw cursor-pointer text-danger"></i></a>
                  </td>
                </tr>';
        }

        $checked = '';
        return '
          <div class="col-12">
            <h3><a href="?o=domains&m=list">&laquo;</a> ' . $domain . '</h3>
          </div>
        </div><!-- END UPPER ROW -->
        <div class="row">
          <div class="table-responsive">
            <table id=records class="table table-sm" style="min-width:1000px;table-layout:fixed">
              <thead>
                <tr>
                  <th>Name</th>
                  <th>Type</th>
                  <th>Content</th>
                  <th>Priority</th>
                  <th>TTL</th>
                  <th data-sortable="false" class="text-right" style="width:4rem"></th>
                </tr>
              </thead>
              <tbody>' . $buf . '
          </table>
        </div>
      </div>
      <br>
      <form method="post" action="' . $this->g->cfg['self'] . '">
        <input type="hidden" name="c" value="' . $_SESSION['c'] . '">
        <input type="hidden" name="o" value="' . $this->g->in['o'] . '">
        <input type="hidden" name="i" value="0">
        <input type="hidden" name="domain_id" value="' . $this->g->in['i'] . '">
        <div class="row">
          <div class="col-3">
            <div class="form-group">
            <input type="text" class="form-control" id="name" name="name" data-regex="^([^.]+\.)*[^.]*$" value="">
            </div>
          </div>
          <div class="col-2">' .  $options. '
          </div>
          <div class="col-4">
            <input type="text" class="form-control" id="content" name="content" data-regex="^.+$" value="">
          </div>
          <div class="col-1">
            <input type="text" class="form-control" id="prio" name="prio" data-regex="^[0-9]*$" value="0">
          </div>
          <div class="col-2">
            <input type="text" class="form-control" id="ttl" name="ttl" data-regex="^[0-9]*$" value="300">
          </div>
        </div>
        <div class="row">
          <div class="col-2 offset-md-6">
            <div class="form-group">
              <div class="custom-control custom-checkbox">
                <input type="checkbox" class="custom-control-input" name="active" id="active"' . $checked . '>
                <label class="custom-control-label" for="active">Active</label>
              </div>
            </div>
          </div>
          <div class="col-4 text-right">
            <div class="btn-group">
              <button id="editor" name="m" value="create" class="btn btn-primary">Add</button>
            </div>
          </div>
        </div>
      </form>
      <script>
$("#records").DataTable();
$(".editlink").on("click", function() {
  var row = $(this).closest("tr");
  $("#i").val(row.attr("data-rowid"));
  $("#name").val(row.find("td:eq(0)").text());
  $("#type").val(row.find("td:eq(1)").text());
  $("#content").val(row.find("td:eq(2)").text());
  $("#prio").val(row.find("td:eq(3)").text());
  $("#ttl").val(row.find("td:eq(4)").text());

  if (row.data("active"))
    $("#active").attr("checked", "on");
  else $("#active").removeAttr("checked");

  $("#editor").val("update");
  $("#editor").text("Save");
  $("#editor").attr("class","btn btn-success");

  return false;
});
      </script>';
    }
}

// lib/php/themes/bootstrap/auth.php 20150101 - 20180512

class Themes_Bootstrap_Auth extends Themes_Bootstrap_Theme
{
    // forgotpw (create new pw)
    public function create(array $in) : string
    {
        extract($in);

        return '
        <div class="col-10 col-sm-8 col-md-6 col-lg-5 col-xl-4 mr-auto ml-auto">
          <h3><i class="fas fa-key fa-fw"></i> Forgot password</h3>
          <form action="' . $this->g->cfg['self'] . '" method="post">
            <input type="hidden" name="c" value="' . $_SESSION['c'] . '">
            <input type="hidden" name="o" value="' . $this->g->in['o'] . '">
            <div class="input-group mb-2 mr-sm-2">
            <div class="input-group-prepend">
              <div class="input-group-text"><i class="fas fa-envelope fa-fw"></i></div>
            </div>
              <input type="email" name="login" id="login" class="form-control" placeholder="Your Login Email Address" value="' . $login . '" autofocus required>
            </div>
            <small class="form-text text-muted text-center">
              You will receive an email with further instructions and please note that this only resets the password for this website interface.
            </small>
            <div class="form-group text-right">
              <div class="btn-group">
                <a class="btn btn-outline-primary" href="?o=auth">&laquo; Back</a>
                <button class="btn btn-primary" type="submit" name="m" value="create">Send</button>
              </div>
            </div>

          </form>
        </div>';
    }

    // signin (read current pw)
    public function list(array $in) : string
    {

        extract($in);

        return '
        <div class="col-10 col-sm-8 col-md-6 col-lg-5 col-xl-4 mr-auto ml-auto">
          <h3><i class="fas fa-sign-in-alt fa-fw"></i> Sign in</h3>
          <form action="' . $this->g->cfg['self'] . '" method="post">
            <input type="hidden" name="c" value="' . $_SESSION['c'] . '">
            <input type="hidden" name="o" value="auth">
            <label class="sr-only" for="login">Username</label>
            <div class="input-group mb-2 mr-sm-2">
              <div class="input-group-prepend">
                <div class="input-group-text"><i class="fas fa-user fa-fw"></i></div>
              </div>
              <input type="email" name="login" id="login" class="form-control" placeholder="Your Email Address" value="' . $login . '" required>
            </div>
            <label class="sr-only" for="webpw">Password</label>
            <div class="input-group mb-2 mr-sm-2">
              <div class="input-group-prepend">
                <div class="input-group-text"><i class="fas fa-key fa-fw"></i></div>
              </div>
              <input type="password" name="webpw" id="webpw" class="form-control" placeholder="Your Password" required>
            </div>
            <div class="form-group">
              <div class="custom-control custom-checkbox">
                <input type="checkbox" class="custom-control-input" name="remember" id="remember">
                <label class="custom-control-label" for="remember">Remember me on this computer</label>
              </div>
            </div>
            <div class="form-group text-right">
              <div class="btn-group">
                <a class="btn btn-outline-primary" href="?o=auth&m=create">Forgot password</a>
                <button class="btn btn-primary" type="submit" name="m" value="list">Sign in</button>
              </div>
            </div>
          </form>
        </div>';
    }

    // resetpw (update pw)
    public function update(array $in) : string
    {

        extract($in);

        return '
        <div class="col-10 col-sm-8 col-md-6 col-lg-5 col-xl-4 mr-auto ml-auto">
          <h3><i class="fas fa-key fa-fw"></i> Update Password</h3>
          <form action="' . $this->g->cfg['self'] . '" method="post">
            <input type="hidden" name="c" value="' . $_SESSION['c'] . '">
            <input type="hidden" name="o" value="auth">
            <input type="hidden" name="id" value="' . $id . '">
            <input type="hidden" name="login" value="' . $login . '">
            <p class="text-center"><b>For ' . $login . '</b></p>
            <label class="sr-only" for="passwd1">New Password</label>
            <div class="input-group mb-2 mr-sm-2">
              <div class="input-group-prepend">
                <div class="input-group-text"><i class="fas fa-key fa-fw"></i></div>
              </div>
                <input class="form-control" type="password" name="passwd1" id="passwd1" placeholder="New Password" value="" required>
            </div>
            <label class="sr-only" for="passwd2">Confirm Password</label>
            <div class="input-group mb-2 mr-sm-2">
              <div class="input-group-prepend">
                <div class="input-group-text"><i class="fas fa-key fa-fw"></i></div>
              </div>
                <input class="form-control" type="password" name="passwd2" id="passwd2" placeholder="Confirm Password" value="" required>
            </div>
            <div class="form-group text-right">
              <div class="btn-group">
                <button class="btn btn-primary" type="submit" name="m" value="update">Update my password</button>
              </div>
            </div>
          </form>
        </div>';
    }
}

// lib/php/themes/bootstrap/theme.php 20150101 - 20180503

class Themes_Bootstrap_Theme extends Theme
{
    public function css() : string
    {
        return '
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.16/css/dataTables.bootstrap4.min.css">
    <style>
body{min-height:75rem;padding-top:5rem;}
table,form{width:100%;}
table.dataTable{border-collapse: collapse !important;}

.media {
  flex-direction: column;
  align-items: center;
  margin-top: 1.5rem;
  margin-bottom: 1.5rem;
}
.media-img {
  display: flex;
  flex-direction: column;
  align-items: center;
  margin-bottom: 0.75rem;
}
.media img {
  max-width: 100%;
  height: auto;
  /*margin-bottom: 0.75rem;*/
}
.media-blank {
  width: 300px;
}
.media-title {
  margin-bottom: 0.75rem;
}
.alert pre {
  margin: 0;
}
  .columns {column-gap:1.5em;columns:1;}
/*body{ background:yellow; }*/

@media (min-width:576px) {
  /*body{ background:red; }*/
}
@media (min-width:768px) {
  /*body{ background:blue; }*/
  .columns {column-gap:1.5em;columns:2;}

  .media {
    flex-direction: row;
    align-items: flex-start;
  }
  .media-body {
    margin-left: 1.5rem;
  }
  .media-img, .media-blank, .media img {
    max-width: 200px;
  }
}
@media (min-width:992px) {
  /*body{ background: green; }*/
  .media-img, .media-blank, .media img {
    max-width: 100%;
  }
}
@media (min-width:1200px) {
  /*body{ background: white; }*/
  .columns {column-gap:1.5em;columns:3;}
  .media-title {
    display: flex;
    flex-direction: row;
    justify-content: space-between;
    align-items: flex-end;
  }
}
    </style>';
    }

    public function log() : string
    {
        list($lvl, $msg) = util::log();
        return $msg ? '
        <div class="col-12">
          <div class="alert alert-' . $lvl . ' alert-dismissible fade show" role="alert">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>' . $msg . '
          </div>
        </div>' : '';
    }

    public function head() : string
    {
        return '
    <nav class="navbar navbar-expand-md navbar-dark bg-dark fixed-top">
      <div class=container>
        <a class="navbar-brand" href="' . $this->g->cfg['self'] . '" title="Home Page">
          <b><i class="fa fa-server fa-fw"></i> ' . $this->g->out['head'] . '</b>
        </a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarsDefault" aria-controls="navbarsDefault" aria-expanded="false" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarsDefault">
          <ul class="navbar-nav mr-auto">' . $this->g->out['nav1'] . '
          </ul>
          <ul class="navbar-nav ml-auto">' . $this->g->out['nav3'] . '
          </ul>
        </div>
      </div>
    </nav>';
    }

    public function nav1(array $a = []) : string
    {
        $a = isset($a[0]) ? $a : util::get_nav($this->g->nav1);
        $o = '?o=' . $this->g->in['o'];
        $t = '?t=' . util::ses('t');
        return join('', array_map(function ($n) use ($o, $t) {
            if (is_array($n[1])) return $this->nav_dropdown($n);
            $c = $o === $n[1] || $t === $n[1] ? ' active' : '';
            $i = isset($n[2]) ? '<i class="' . $n[2] . '"></i> ' : '';
            return '
            <li class="nav-item' . $c . '"><a class="nav-link" href="' . $n[1] . '">' . $i . $n[0] . '</a></li>';
        }, $a));
    }

    public function nav2() : string
    {
        return $this->nav_dropdown(['Theme', $this->g->nav2, 'fa fa-th fa-fw']);
    }

    public function nav3() : string
    {
        if (util::is_usr()) {
            $usr[] = ['Change Profile', '?o=accounts&m=update&i=' . $_SESSION['usr']['id'], 'fas fa-user fa-fw'];
            $usr[] = ['Change Password', '?o=auth&m=update&i=' . $_SESSION['usr']['id'], 'fas fa-key fa-fw'];
            $usr[] = ['Sign out', '?o=auth&m=delete', 'fas fa-sign-out-alt fa-fw'];

            if (util::is_adm() && !util::is_acl(0)) $usr[] =
                ['Switch to sysadm', '?o=accounts&m=switch_user&i=' . $_SESSION['adm'], 'fas fa-user fa-fw'];

            return $this->nav_dropdown([$_SESSION['usr']['login'], $usr, 'fas fa-user fa-fw']);
        } else return '';
    }

    public function nav_dropdown(array $a = []) : string
    {
        $o = '?o=' . $this->g->in['o'];
        $i = isset($a[2]) ? '<i class="' . $a[2] . '"></i> ' : '';
        return '
            <li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle" href="#" id="dropdown01" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">' . $i . $a[0] . '</a>
              <div class="dropdown-menu" aria-labelledby="dropdown01">'.join('', array_map(function ($n) use ($o) {
            $c = $o === $n[1] ? ' active' : '';
            $i = isset($n[2]) ? '<i class="' . $n[2] . '"></i> ' : '';
            return '
                <a class="dropdown-item" href="' . $n[1] . '">' . $i . $n[0] . '</a>';
        }, $a[1])).'
              </div>
            </li>';
    }

    public function main() : string
    {
        return '
    <main class="container">
      <div class="row">' . $this->g->out['log'] . $this->g->out['main'] . '
      </div>
    </main>';
    }

    public function js() : string
    {
        return '
    <script src="https://code.jquery.com/jquery-1.12.4.js"></script>
    <script src="https://cdn.datatables.net/1.10.16/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.16/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
    <script src="https://use.fontawesome.com/releases/v5.0.11/js/solid.js" integrity="sha384-Y5YpSlHvzVV0DWYRcgkEu96v/nptti7XYp3D8l+PquwfpOnorjWA+dC7T6wRgZFI" crossorigin="anonymous"></script>
    <script src="https://use.fontawesome.com/releases/v5.0.11/js/fontawesome.js" integrity="sha384-KPnpIFJjPHLMZMALe0U04jClDmqlLhkBM6ZEkFvs9AiWRYwaDXPhn2D5lr8sypQ+" crossorigin="anonymous"></script>';
    }
/*
    protected function pager(array $ary) : string
    {
        extract($ary);

        $b = '';
        $o = util::ses('o');

        for($i = 1; $i <= $last; $i++) $b .= '
              <li class="page-item' . ($i === $curr ? ' active' : '') . '">
                <a class="page-link" href="?o=' . $o . '&m=list&p=' . $i . '">' . $i . '</a>
              </li>';

        return '
          <nav aria-label="Page navigation">
            <ul class="pagination pagination-sm pull-right">
              <li class="page-item' . ($curr === 1 ? ' disabled' : '') . '">
                <a class="page-link" href="?o=' . $o . '&m=list&p=' . $prev . '" aria-label="Previous">
                  <span aria-hidden="true">&laquo;</span>
                  <span class="sr-only">Previous</span>
                </a>
              </li>' . $b . '
              <li class="page-item' . ($curr === $last ? ' disabled' : '') . '">
                <a class="page-link" href="?o=' . $o . '&m=list&p=' . $next . '" aria-label="Next">
                  <span aria-hidden="true">&raquo;</span>
                  <span class="sr-only">Next</span>
                </a>
              </li>
            </ul>
          </nav>';
    }
*/
}

// lib/php/themes/bootstrap/accounts.php 20170225 - 20180512

class Themes_Bootstrap_Accounts extends Themes_Bootstrap_Theme
{
    public function create(array $in) : string
    {
        return $this->editor($in);
    }

    public function read(array $in) : string
    {
        return $this->editor($in);
    }

    public function update(array $in) : string
    {
        return $this->editor($in);
    }

    public function list(array $in) : string
    {
        $buf = '';
        $num = count($in);

        foreach ($in as $a) {
            extract($a);
            $buf .= '
        <tr>
          <td class="text-truncate">
            <a href="?o=accounts&m=read&i=' . $id . '" title="Show account: ' . $id . '">
              <strong>' . $login . '</strong>
            </a>
          </td>
          <td>' . $fname . '</td>
          <td>' . $lname . '</td>
          <td class="text-truncate">' . $altemail . '</td>
          <td>' . $this->g->acl[$acl] . '</td>
          <td>' . $grp . '</td>
        </tr>';
        }

        return '
          <div class="col-12">
            <h3>
              <i class="fas fa-users fa-fw"></i> Accounts
              <a href="?o=accounts&m=create" title="Add new account">
                <small><i class="fas fa-plus-circle fa-fw"></i></small>
              </a>
            </h3>
          </div>
        </div><!-- END UPPER ROW -->
        <div class="row">
          <div class="table-responsive">
            <table id=accounts class="table table-sm" style="min-width:1100px;table-layout:fixed">
              <thead class="nowrap">
                <tr>
                  <th class="w-25">User ID</th>
                  <th>First Name</th>
                  <th>Last Name</th>
                  <th class="w-25">Alt Email</th>
                  <th>ACL</th>
                  <th>Grp</th>
                </tr>
              </thead>
              <tbody>' . $buf . '
              </tbody>
            </table>
          </div>
          <script>$(document).ready(function() { $("#accounts").DataTable({"order": []}); });</script>';
    }

    private function editor(array $in) : string
    {
        extract($in);

        if ($this->g->in['m'] === 'create') {
            $header = 'Add Account';
            $switch = '';
            $submit = '
                <a class="btn btn-secondary" href="?o=accounts&m=list">&laquo; Back</a>
                <button type="submit" name="m" value="create" class="btn btn-primary">Add This Account</button>';
        } else {
            $header = 'Update Account';
//            $switch = util::is_adm() && (!util::is_usr(0) && !util::is_usr(1)) ? '
            $switch = !util::is_usr($id) && (util::is_acl(0) || util::is_acl(1)) ? '
                  <a class="btn btn-outline-primary" href="?o=accounts&m=switch_user&i=' . $id . '">Switch to ' . $login . '</a>' : '';
            $submit = '
                <a class="btn btn-secondary" href="?o=accounts&m=list">&laquo; Back</a>
                <a class="btn btn-danger" href="?o=accounts&m=delete&i=' . $id . '" title="Remove this account" onClick="javascript: return confirm(\'Are you sure you want to remove ' . $login . '?\')">Remove</a>
                <button type="submit" name="m" value="update" class="btn btn-primary">Update</button>';
        }

        if (util::is_adm()) {
            $acl_ary = $grp_ary = [];
            foreach($this->g->acl as $k => $v) $acl_ary[] = [$v, $k];
            $acl_buf = $this->dropdown($acl_ary, 'acl', "$acl", '', 'custom-select');
            $res = db::qry("
 SELECT login,id
   FROM `accounts`
  WHERE acl = :0 OR acl = :1", ['0' => 0, '1' => 1]);

            foreach($res as $k => $v) $grp_ary[] = [$v['login'], $v['id']];
            $grp_buf = $this->dropdown($grp_ary, 'grp', "$grp", '', 'custom-select');
            $aclgrp_buf = '
                <div class="form-group">
                  <label for="acl">ACL</label><br>' . $acl_buf . '
                </div>
                <div class="form-group">
                  <label for="grp">Group</label><br>' . $grp_buf . '
                </div>';
        } else {
            $aclgrp_buf = '';
            $anotes_buf = '';
        }

        return '
          <div class="col-12">
            <h3><a href="?o=accounts&m=list">&laquo;</a> ' . $header . '</h3>
          </div>
        </div><!-- END UPPER ROW -->
        <div class="row">
          <div class="col-12">
            <form method="post" action="' . $this->g->cfg['self'] . '">
              <input type="hidden" name="c" value="' . $_SESSION['c'] . '">
              <input type="hidden" name="o" value="' . $this->g->in['o'] . '">
              <input type="hidden" name="i" value="' . $id . '">
              <div class="row">
                <div class="col-12 col-sm-6 col-lg-4">
                  <div class="form-group">
                    <label for="login">UserID</label>
                    <input type="email" class="form-control" id="login" name="login" value="' . $login . '" required>
                  </div>
                  <div class="form-group">
                    <label for="altemail">Alt Email</label>
                    <input type="text" class="form-control" id="altemail" name="altemail" value="' . $altemail . '">
                  </div>
                </div>
                <div class="col-12 col-sm-6 col-lg-4">
                  <div class="form-group">
                    <label for="fname">First Name</label>
                    <input type="text" class="form-control" id="fname" name="fname" value="' . $fname . '" required>
                  </div>
                  <div class="form-group">
                    <label for="lname">Last Name</label>
                    <input type="text" class="form-control" id="lname" name="lname" value="' . $lname . '" required>
                  </div>
                </div>
                <div class="col-12 col-sm-6 col-lg-4">' . $aclgrp_buf . '
                </div>
              </div>
              <div class="row">
                <div class="col-12 col-sm-6">' . $switch . '
                </div>
                <div class="col-12 col-sm-6 text-right">
                  <div class="btn-group">' . $submit . '
                  </div>
                </div>
              </div>
            </form>
          </div>';
    }
}

// lib/php/themes/bootstrap/vmails.php 20170101 - 20180512

class Themes_Bootstrap_Vmails extends Themes_Bootstrap_Theme
{
    public function create(array $in) : string
    {
        return $this->editor($in);
    }

    public function update(array $in) : string
    {
        return $this->editor($in);
    }

    public function list(array $in) : string
    {
        return '
        <div class="col-12">
          <h3>
            <i class="fas fa-envelope fa-fw"></i> Mailboxes
            <a href="#" title="Add New Mailbox" data-toggle="modal" data-target="#createmodal">
              <small><i class="fas fa-plus-circle fa-fw"></i></small>
            </a>
          </h3>
        </div>
      </div><!-- END UPPER ROW -->
      <div class="row">
        <div class="table-responsive">
          <table id=vmails class="table table-sm" style="min-width:1100px;table-layout:fixed">
            <thead class="nowrap">
              <tr>
                <th>Email</th>
                <th>Domain</th>
                <th></th>
                <th>Usage&nbsp;</th>
                <th></th>
                <th>Quota</th>
                <th>Msg&nbsp;#&nbsp;</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
            </tbody>
          </table>
        </div>
        <div class="modal fade" id="createmodal" tabindex="-1" role="dialog" aria-labelledby="createmodal" aria-hidden="true">
          <div class="modal-dialog" role="document">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title">Mailboxes</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>
              <form method="post" action="' . $this->g->cfg['self'] . '">
                <input type="hidden" name="c" value="' . $_SESSION['c'] . '">
                <input type="hidden" name="o" value="' . $this->g->in['o'] . '">
                <input type="hidden" name="i" value="' . $this->g->in['i'] . '">
                <input type="hidden" name="m" value="create">
                <div class="modal-body">
                  <div class="form-group">
                    <label for="user" class="form-control-label">Mailbox</label>
                    <input type="text" class="form-control" id="user" name="user">
                  </div>
                  <div class="form-group">
                    <div class="custom-control custom-checkbox">
                      <input type="checkbox" class="custom-control-input" name="spamf" id="spamf" checked>
                      <label class="custom-control-label" for="spamf">Spam Filter</label>
                    </div>
                  </div>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                  <button type="submit" class="btn btn-primary">Add New Mailbox</button>
                </div>
              </form>
            </div>
          </div>
        </div>
        <script>
$(document).ready(function() {
  $("#vmails").DataTable({
    "processing": true,
    "serverSide": true,
    "ajax": "?x=json&o=vmails&m=list",
    "order": [[ 8, "desc" ]],
    "columnDefs": [
      {"targets":0, "className":"text-truncate", "width":"25%"},
      {"targets":1, "className":"text-truncate", "width":"20%"},
      {"targets":2, "className":"align-middle", "sortable": false},
      {"targets":3, "className":"text-right", "width":"4rem"},
      {"targets":4, "className":"text-center", "width":"0.5rem", "sortable": false},
      {"targets":5, "width":"4rem"},
      {"targets":6, "className":"text-right", "width":"3rem"},
      {"targets":7, "className":"text-right", "width":"4rem", "sortable": false},
      {"targets":8, "visible":false, "sortable": true}
    ]
  });
});
        </script>';
    }

    function editor(array $in) : string
    {
        extract($in);

        $active_checked = ($active ? 1 : 0) ? ' checked' : '';
        $filter_checked = ($spamf ? 1 : 0) ? ' checked' : '';

        $passwd1  = $passwd1 ?? '';
        $passwd2  = $passwd2 ?? '';

        $header   = $this->g->in['m'] === 'create' ? 'Add Mailbox' : 'Update Mailbox';
        $submit   = $this->g->in['m'] === 'create' ? '
                      <a class="btn btn-secondary" href="?o=vmails&m=list">&laquo; Back</a>
                      <button type="submit" name="m" value="create" class="btn btn-primary">Add Mailbox</button>' : '
                      <a class="btn btn-secondary" href="?o=vmails&m=list">&laquo; Back</a>
                      <a class="btn btn-danger" href="?o=vmails&m=delete&i=' . $this->g->in['i'] . '" title="Remove mailbox" onClick="javascript: return confirm(\'Are you sure you want to remove ' . $user . '?\')">Remove</a>
                      <button type="submit" name="m" value="update" class="btn btn-primary">Update</button>';
        $enable   = $this->g->in['m'] === 'create' ? '
                <input type="text" autocorrect="off" autocapitalize="none" class="form-control" name="user" id="user" value="' . $user . '">' : '
                <input type="text" class="form-control" value="' . $user . '" disabled>
                <input type="hidden" name="user" id="user" value="' . $user . '">';

        return '
          <div class="col-12">
            <h3><a href="?o=vmails&m=list">&laquo;</a> ' . $header . '</h3>
          </div>
        </div><!-- END UPPER ROW -->
        <div class="row">
          <div class="col-12">
            <form method="post" action="' . $this->g->cfg['self'] . '">
              <input type="hidden" name="c" value="' . $_SESSION['c'] . '">
              <input type="hidden" name="o" value="' . $this->g->in['o'] . '">
              <input type="hidden" name="i" value="' . $this->g->in['i'] . '">
              <div class="row">
                <div class="form-group col-4">
                  <label for="domain">Email Address</label>' . $enable . '
                </div>
                <div class="form-group col-2">
                  <label for="quota">Mailbox Quota</label>
                  <input type="number" class="form-control" name="quota" id="quota" value="' . intval($quota / 1000000) . '">
                </div>
                <div class="col-3">
                  <div class="form-group">
                    <label for="passwd1">Password</label>
                    <input type="password" class="form-control" name="passwd1" id="passwd1" value="' . $passwd1 . '">
                  </div>
                </div>
                <div class="col-3">
                  <div class="form-group">
                    <label for="passwd2">Confirm Password</label>
                    <input type="password" class="form-control" name="passwd2" id="passwd2" value="' . $passwd2 . '">
                  </div>
                </div>
              </div>
              <div class="row">
                <div class="col-4">
                  <div class="form-group">
                    <div class="custom-control custom-checkbox">
                      <input type="checkbox" class="custom-control-input" name="spamf" id="spamf"' . $filter_checked . '>
                      <label class="custom-control-label" for="spamf">Spam Filter</label>
                    </div>
                  </div>
                </div>
                <div class="col-4">
                  <div class="form-group">
                    <div class="custom-control custom-checkbox">
                      <input type="checkbox" class="custom-control-input" name="active" id="active"' . $active_checked . '>
                      <label class="custom-control-label" for="active">Active</label>
                    </div>
                  </div>
                </div>
                <div class="col-4 text-right">
                  <div class="btn-group">' . $submit . '
                  </div>
                </div>
              </div>
            </form>
          </div>';
    }
}

// lib/php/themes/bootstrap/infosys.php 20170225 - 20180512

class Themes_Bootstrap_InfoSys extends Themes_Bootstrap_Theme
{
    public function list(array $in) : string
    {
        extract($in);

        return '
          <div class="col-6">
            <h3><i class="fas fa-server fa-fw"></i> System Info</h3>
          </div>
          <div class="col-6">
            <form method="post" class="form-inline">
              <input type="hidden" name="c" value="' . $_SESSION['c'] . '">
              <input type="hidden" name="o" value="infosys">
              <div class="form-group ml-auto">
                <button type="submit" class="btn btn-primary"><i class="fas fa-sync-alt fa-fw" aria-hidden="true"></i> Refresh</button>
              </div>
            </form>
          </div>
        </div><!-- END UPPER ROW -->
        <div class="row">
          <div class="col-6">
            <br>
            <h5>RAM <small>' . $mem_used . ' / ' . $mem_total . ', ' . $mem_free . ' free</small></h5>
            <div class="progress">
              <div class="progress-bar bg-' . $mem_color . '" role="progressbar" aria-valuenow="' . $mem_pcnt . '"
              aria-valuemin="0" aria-valuemax="100" style="width:' . $mem_pcnt . '%" title="Used Memory">' . $mem_text . '
              </div>
            </div>
            <br>
            <h5>Disk <small>' . $dsk_used . ' / ' . $dsk_total . ', ' . $dsk_free . ' free</small></h5>
            <div class="progress">
              <div class="progress-bar bg-' . $dsk_color . '" role="progressbar" aria-valuenow="' . $dsk_pcnt . '"
              aria-valuemin="0" aria-valuemax="100" style="width:' . $dsk_pcnt . '%" title="Used Disk Space">' . $dsk_text . '
              </div>
            </div>
            <br>
            <h5>CPU <small>' .$cpu_all  . '</small></h5>
            <div class="progress">
              <div class="progress-bar bg-' . $cpu_color . '" role="progressbar" aria-valuenow="' . $cpu_pcnt . '"
              aria-valuemin="0" aria-valuemax="100" style="width:' . $cpu_pcnt . '%" title="Used Disk Space">' . $cpu_text . '
              </div>
            </div>
            <br>
          </div>
          <div class="col-6">
            <div class="table-responsive">
              <table class="table table-sm table-bordered">
                <tbody>
                  <tr>
                    <td><b>Hostname</b></td>
                    <td>' .$hostname  . '</td>
                  </tr>
                  <tr>
                    <td><b>Host IP</b></td>
                    <td>' . $host_ip . '</td>
                  </tr>
                  <tr>
                    <td><b>Distro</b></td>
                    <td>' . $os_name . '</td>
                  </tr>
                  <tr>
                    <td><b>Uptime</b></td>
                    <td>' . $uptime . '</td>
                  </tr>
                  <tr>
                    <td><b>CPU Load</b></td>
                    <td>' . $loadav . ' (' . $cpu_num . ' cpus)</td>
                  </tr>
                  <tr>
                    <td><b>CPU Model</b></td>
                    <td>' . $cpu_name . '</td>
                  </tr>
                  <tr>
                    <td><b>Kernel Version</b></td>
                    <td>' . $kernel . '</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>';
    }
}

// lib/php/themes/bootstrap/valias.php 20170101 - 20180512

class Themes_Bootstrap_Valias extends Themes_Bootstrap_Theme
{
    public function create(array $in) : string
    {
        return $this->editor($in);
    }

    public function update(array $in) : string
    {
        return $this->editor($in);
    }

    public function list(array $in) : string
    {
        return '
        <div class="col-12">
          <h3>
            <i class="fa fa-globe fa-fw"></i> Aliases
            <a href="?o=valias&m=create" title="Add Alias">
              <small><i class="fas fa-plus-circle fa-fw"></i></small>
            </a>
          </h3>
        </div>
      </div><!-- END UPPER ROW -->
      <div class="row">
        <div class="table-responsive">
          <table id=valias class="table table-sm" style="min-width:1100px;table-layout:fixed">
            <thead class="nowrap">
              <tr>
                <th>Alias</th>
                <th>Target Address</th>
                <th>Domain</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
            </tbody>
          </table>
        </div>
        <script>
$(document).ready(function() {
  $("#valias").DataTable({
    "processing": true,
    "serverSide": true,
    "ajax": "?x=json&o=valias&m=list",
    "order": [[ 5, "desc" ]],
    "columnDefs": [
      {"targets":0,   "className":"text-truncate", "width":"25%"},
      {"targets":3,   "className":"text-right", "width":"4rem", "sortable": false},
      {"targets":4,   "visible":false},
      {"targets":5,   "visible":false},
    ],
  });
});
        </script>';

    }

    private function editor(array $in) : string
    {
        extract($in);

        $active = $active ? 1 : 0;
        $header = $this->g->in['m'] === 'create' ? 'Add Alias' : 'Update Alias';
        $submit = $this->g->in['m'] === 'create' ? '
                <a class="btn btn-secondary" href="?o=valias&m=list">&laquo; Back</a>
                <button type="submit" name="m" value="create" class="btn btn-primary">Add Alias</button>' : '
                <a class="btn btn-secondary" href="?o=valias&m=list">&laquo; Back</a>
                <a class="btn btn-danger" href="?o=valias&m=delete&i=' . $id . '" title="Remove alias" onClick="javascript: return confirm(\'Are you sure you want to remove ' . $source . '?\')">Remove</a>
                <button type="submit" name="m" value="update" class="btn btn-primary">Update</button>';

        $checked = $active ? ' checked' : '';

        return '
          <div class="col-12">
            <h3><a href="?o=valias&m=list">&laquo;</a> ' . $header . '</h3>
          </div>
        </div><!-- END UPPER ROW -->
        <div class="row">
          <div class="col-12">
            <p><b>Note:</b> If your chosen destination address is an external mailbox, the <b>receiving mailserver</b> may reject your message due to an SPF failure.</p>
            <form method="post" action="' . $this->g->cfg['self'] . '">
              <input type="hidden" name="c" value="' . $_SESSION['c'] . '">
              <input type="hidden" name="o" value="' . $this->g->in['o'] . '">
              <input type="hidden" name="i" value="' . $this->g->in['i'] . '">
              <div class="row">
                <div class="form-group col-6">
                  <label class="control-label" for="source">Alias Address(es)</label>
                  <textarea autocorrect="off" autocapitalize="none" class="form-control" rows="4" name="source" id="source">' . $source . '</textarea>
                  <p>Full email address/es or @example.com, to catch all messages for a domain (comma-separated). <b>Locally hosted domains only</b>.</p>
                </div>
                <div class="form-group col-6">
                  <label class="control-label" for="target">Target Address(es)</label>
                  <textarea autocorrect="off" autocapitalize="none" class="form-control" rows="4" id="target" name="target">' . $target . '</textarea>
                  <p>Full email address/es (comma-separated).</p>
                </div>
              </div>
              <div class="row">
                <div class="col-2 offset-md-6">
                  <div class="form-group">
                    <div class="custom-control custom-checkbox">
                      <input type="checkbox" class="custom-control-input" name="active" id="active"' . $checked . '>
                      <label class="custom-control-label" for="active">Active</label>
                    </div>
                  </div>
                </div>
                <div class="col-4 text-right">
                  <div class="btn-group">' . $submit . '
                  </div>
                </div>
              </div>
            </form>
          </div>';
    }
}

// lib/php/util.php 20150225 - 20180512

class Util
{
    public static function log(string $msg = '', string $lvl = 'danger') : array
    {
        if ($msg) {
            if (isset($_SESSION['l']) and $_SESSION['l']) {
                list(, $m) = explode(':', $_SESSION['l'], 2);
                $msg = $m . '<br>' . $msg;
            }
            $_SESSION['l'] = $lvl . ':' . $msg;
        } elseif (isset($_SESSION['l']) and $_SESSION['l']) {
            $l = $_SESSION['l']; $_SESSION['l'] = '';
            return explode(':', $l, 2);
        }
        return ['', ''];
    }

    public static function esc(array $in) : array
    {
        foreach ($in as $k => $v)
            $in[$k] = isset($_REQUEST[$k]) && !is_array($_REQUEST[$k])
                ? htmlentities(trim($_REQUEST[$k]), ENT_QUOTES, 'UTF-8') : $v;
        return $in;
    }

    // TODO please document what $k, $v and $x are for?
    public static function ses(string $k, string $v = '', string $x = null) : string
    {
        return $_SESSION[$k] =
            (!is_null($x) && (!isset($_SESSION[$k]) || ($_SESSION[$k] != $x))) ? $x :
                (((isset($_REQUEST[$k]) && !isset($_SESSION[$k]))
                    || (isset($_REQUEST[$k]) && isset($_SESSION[$k])
                    && ($_REQUEST[$k] != $_SESSION[$k])))
                ? htmlentities(trim($_REQUEST[$k]), ENT_QUOTES, 'UTF-8')
                : ($_SESSION[$k] ?? $v));
    }

    public static function cfg(object $g) : void
    {
        if (file_exists($g->cfg['file'])) {
            foreach(include $g->cfg['file'] as $k => $v) {
               $g->$k = array_merge($g->$k, $v);
            }
        }
    }

    public static function now(string $date1, string $date2 = null) : string
    {
        if (!is_numeric($date1)) $date1 = strtotime($date1);
        if ($date2 and !is_numeric($date2)) $date2 = strtotime($date2);
        $date2 = $date2 ?? time();
        $diff = abs($date1 - $date2);
        if ($diff < 10) return ' just now';

        $blocks = [
            ['k' => 'year', 'v' => 31536000],
            ['k' => 'month','v' => 2678400],
            ['k' => 'week', 'v' => 604800],
            ['k' => 'day',  'v' => 86400],
            ['k' => 'hour', 'v' => 3600],
            ['k' => 'min',  'v' => 60],
            ['k' => 'sec',  'v' => 1],
        ];
        $levels = 2;
        $current_level = 1;
        $result = [];

        foreach ($blocks as $block) {
            if ($current_level > $levels) {
                break;
            }
            if ($diff / $block['v'] >= 1) {
                $amount = floor($diff / $block['v']);
                $plural = ($amount > 1) ? 's' : '';
                $result[] = $amount . ' ' . $block['k'] . $plural;
                $diff -= $amount * $block['v'];
                ++$current_level;
            }
        }
        return implode(' ', $result) . ' ago';
    }

/*
    // Not needed with Bootstrap4 DataTables, 20180303

    public static function pager(int $curr, int $perp, int $total) : array
    {
        $start = ($curr - 1) * $perp;
        $start = $start < 0 ? 0 : $start;
        $last  = intval(ceil($total / $perp));
        $curr  = $curr < 1 ? 1 : ($curr > $last ? $last : $curr);
        $prev  = $curr < 2 ? 1 : $curr - 1;
        $next  = $curr > ($last - 1) ? $last : $curr + 1;

        return [
            'start' => $start,
            'prev'  => $prev,
            'curr'  => $curr,
            'next'  => $next,
            'last'  => $last,
            'perp'  => $perp,
            'total' => $total
        ];
    }
*/
    public static function is_adm() : bool
    {
        return isset($_SESSION['adm']);
    }

    public static function is_usr(int $id = null) : bool
    {
        return (is_null($id))
            ? isset($_SESSION['usr'])
            : isset($_SESSION['usr']['id']) && $_SESSION['usr']['id'] == $id;
    }

    public static function is_acl(int $acl) : bool
    {
        return isset($_SESSION['usr']['acl']) && $_SESSION['usr']['acl'] == $acl;
    }

    // 09-Auth

    public static function genpw() : string
    {
        return str_replace('.', '_',
            substr(password_hash((string)time(), PASSWORD_DEFAULT),
                rand(10, 50), 10));
    }

    public static function get_nav(array $nav = []) : array
    {
        return isset($_SESSION['usr'])
            ? (isset($_SESSION['adm']) ? $nav['adm'] : $nav['usr'])
            : $nav['non'];
    }

    public static function get_cookie(string $name, string $default='') : string
    {
        return $_COOKIE[$name] ?? $default;
    }

    public static function put_cookie(string $name, string $value, int $expiry=604800) : string
    {
        return setcookie($name, $value, time() + $expiry, '/') ? $value : '';
    }

    public static function del_cookie(string $name) : string
    {
        return self::put_cookie($name, '', time() - 1);
    }

    public static function chkpw(string $pw, string $pw2) : bool
    {
        if (strlen($pw) > 9) {
            if (preg_match('/[0-9]+/', $pw)) {
                if (preg_match('/[A-Z]+/', $pw)) {
                    if (preg_match('/[a-z]+/', $pw)) {
                        if ($pw === $pw2) {
                            return true;
                        } else util::log('Passwords do not match, please try again');
                    } else util::log('Password must contains at least one lower case letter');
                } else util::log('Password must contains at least one captital letter');
            } else util::log('Password must contains at least one number');
        } else util::log('Passwords must be at least 10 characters');
        return false;
    }

    public static function remember(object $g) : void
    {
        if (!self::is_usr()) {
            if ($c = self::get_cookie('remember')) {
                if (is_null(db::$dbh)) db::$dbh = new db($g->db);
                db::$tbl = 'accounts';
                if ($usr = db::read('id,grp,acl,login,fname,lname,cookie', 'cookie', $c, '', 'one')) {
                    extract($usr);
                    $_SESSION['usr'] = $usr;
                    if ($acl == 0) $_SESSION['adm'] = $id;
                    self::log($login . ' is remembered and logged back in', 'success');
                    self::ses('o', '', $g->in['o']);
                    self::ses('m', '', $g->in['m']);
                }
            }
        }
    }

    // shell utilities

    public static function redirect(string $url, int $ttl = 5, string $msg = '') : void
    {
        header('refresh:' . $ttl . '; url=' . $url);
        if ($ttl) echo '<!DOCTYPE html>
<title>Redirect...</title>
<h2 style="text-align:center">Redirecting in ' . $ttl . ' seconds...</h2>
<pre style="width:50em;margin:0 auto;">' . $msg . '</pre>';
        exit;
    }

    public static function numfmt(float $size, int $precision = null) : string
    {
        if ($size == 0) return '0';
        if ($size >= 1000000000000) return round(($size / 1000000000000), $precision??3) . ' TB';
        else if ($size >= 1000000000) return round(($size / 1000000000), $precision??2) . ' GB';
        else if ($size >= 1000000) return round(($size / 1000000), $precision??1) . ' MB';
        else if ($size >= 1000) return round(($size / 1000), $precision??0) . ' KB';
        else return $size . ' Bytes';
    }

    // numfmt() was wrong, we want MB not MiB
    public static function numfmtsi(float $size, int $precision = 2) : string
    {
        if ($size == 0) return "0";
        $base = log($size, 1024);
        $suffixes = [' Bytes', ' KiB', ' MiB', ' GiB', ' TiB'];
        return round(pow(1024, $base - floor($base)), $precision) . $suffixes[floor($base)];
    }

    public static function is_valid_domain_name(string $domainname) : string
    {
        $domainname = idn_to_ascii($domainname);
        return (preg_match("/^([a-z\d](-*[a-z\d])*)(\.([a-z\d](-*[a-z\d])*))*$/i", $domainname)
              && preg_match("/^.{1,253}$/", $domainname)
              && preg_match("/^[^\.]{1,63}(\.[^\.]{1,63})*$/", $domainname));
    }

    public static function mail_password(string $pw, string $hash = 'SHA512-CRYPT') : string
    {
        $salt_str = bin2hex(openssl_random_pseudo_bytes(8));
        return $hash === 'SHA512-CRYPT'
            ? '{SHA512-CRYPT}' . crypt($pw, '$6$' . $salt_str . '$')
            : '{SSHA256}' . base64_encode(hash('sha256', $pw . $salt_str, true) . $salt_str);
    }

    public static function sec2time(int $seconds) : string
    {
        $dtF = new \DateTime('@0');
        $dtT = new \DateTime("@$seconds");
        return $dtF->diff($dtT)->format('%a days, %h hours, %i mins and %s secs');
    }

    public static function is_post() : bool
    {
        if ($_POST) {
            if (!isset($_POST['c']) || $_SESSION['c'] !== $_POST['c']) {
                util::log('Possible CSRF attack');
                util::redirect('?o=' . $_SESSION['o'] . '&m=list', 0);
            }
            return true;
        }
        return false;
    }
}

// lib/php/db.php 20150225 - 20180512

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

    public static function create(array $ary)
    {
        $fields = $values = '';
        foreach($ary as $k =>$v) {
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
        $set_str = '';
        foreach($set as $k =>$v) $set_str .= "
        $k = :$k,";
        $set_str = rtrim($set_str, ',');

        $where_str = '';
        $where_ary = [];
        foreach($where as $k =>$v) {
            $where_str .= " " . $v[0] . " " . $v[1] . " :" . $v[0];
            $where_ary[$v[0]] = $v[2] ;
        }
        $ary = array_merge($set, $where_ary);

        $sql = "
 UPDATE `" . self::$tbl . "` SET$set_str
  WHERE$where_str";

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
        $where_str = '';
        $where_ary = [];
        foreach($where as $k =>$v) {
            $where_str .= " " . $v[0] . " " . $v[1] . " :" . $v[0];
            $where_ary[$v[0]] = $v[2] ;
        }

        $sql = "
 DELETE FROM `" . self::$tbl . "`
  WHERE $where_str";

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
        try {
            if ($type !==  'all') $sql .= ' LIMIT 1';
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
        if (is_object($stm) && ($stm instanceof \PDOStatement)) {
            foreach($ary as $k => $v) {
                if (is_numeric($v))     $p = \PDO::PARAM_INT;
                elseif (is_bool($v))    $p = \PDO::PARAM_BOOL;
                elseif (is_null($v))    $p = \PDO::PARAM_NULL;
                elseif (is_string($v))  $p = \PDO::PARAM_STR;
                else $p = false;
                if ($p !==  false) $stm->bindValue(":$k", $v, $p);
            }
        }
    }





    // See http://datatables.net/usage/server-side

    public static function simple($request, $table, $primaryKey, $columns)
    {
        $db     = self::$dbh;
        $cols   = '`' . implode("`, `", self::pluck($columns, 'db')) . '`';
        $bind   = [];

        $limit  = self::limit($request, $columns);
        $order  = self::order($request, $columns);
        $where  = self::filter($request, $columns, $bind);
        $query  = "
 SELECT $cols
   FROM `$table` $where $order $limit";

        $data   = self::sql_exec($db, $bind, $query);

        $recordsFiltered = self::sql_exec($db, $bind, "
 SELECT COUNT(`$primaryKey`)
   FROM `$table` $where", 'col');

        $recordsTotal = self::qry("
 SELECT COUNT(`$primaryKey`)
   FROM `$table`", [], 'col');

        return [
            "draw"            => isset($request['draw']) ? intval($request['draw']) : 0,
            "recordsTotal"    => intval($recordsTotal),
            "recordsFiltered" => intval($recordsFiltered),
            "data"            => self::data_output($columns, $data)
        ];
    }

    public static function data_output($columns, $data)
    {
        $out = array();

        for($i = 0, $ien = count($data); $i < $ien ; $i++) {
            $row = [];

            for($j = 0, $jen = count($columns); $j < $jen ; $j++) {
                $column = $columns[$j];

                // Is there a formatter?
                if (isset($column['formatter'])) {
                    $row[$column['dt']] = $column['formatter'](($data[$i][$column['db']] ?? ''), $data[$i]);
                } else {
                    if ($column['dt'] !== null) $row[$column['dt']] = $data[$i][$columns[$j]['db']];
                }
            }

            $out[] = $row;
        }

        return $out;
    }

    public static function limit($request, $columns)
    {
        $limit = '';

        if (isset($request['start']) && $request['length'] != -1) {
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

            for($i = 0, $ien = count($request['order']) ; $i < $ien ; $i++) {
                $columnIdx = intval($request['order'][$i]['column']);
                $requestColumn = $request['columns'][$columnIdx];
//                $columnIdx = array_search($requestColumn['data'], $dtColumns); // don't use $dtColumns
                $columnIdx = array_search($requestColumn['data'], array_column($columns, 'dt'));
                $column = $columns[$columnIdx];

                if ($requestColumn['orderable'] == 'true') {
                    $dir = $request['order'][$i]['dir'] === 'asc' ? 'ASC' : 'DESC';
                    $orderBy[] = '`'.$column['db'].'` '.$dir;
                }
            }

            if (count($orderBy)) $order = 'ORDER BY ' . implode(', ', $orderBy);
        }
        return $order;
    }

    public static function filter($request, $columns, &$bindings)
    {
        $globalSearch = $columnSearch = [];
        $dtColumns = self::pluck($columns, 'dt');

        if (isset($request['search']) && $request['search']['value'] != '') {
            $str = $request['search']['value'];

            for($i = 0, $ien = count($request['columns']) ; $i < $ien ; $i++) {
                $requestColumn = $request['columns'][$i];
                $columnIdx = array_search($requestColumn['data'], $dtColumns);
                $column = $columns[ $columnIdx ];

                if ($requestColumn['searchable'] == 'true' && $column['db']) {
                    $binding = self::bind($bindings, '%'.$str.'%', PDO::PARAM_STR);
                    $globalSearch[] = '`' . $column['db'] . '` LIKE ' . $binding;
                }
            }
        }

        // Individual column filtering
        if (isset($request['columns'])) {
            for($i = 0, $ien = count($request['columns']) ; $i < $ien ; $i++) {
                $requestColumn = $request['columns'][$i];
                $columnIdx = array_search($requestColumn['data'], $dtColumns);
                $column = $columns[$columnIdx];

                $str = $requestColumn['search']['value'];

                if ($requestColumn['searchable'] == 'true' && $str != '' && $column['db'] !== null) {
                    $binding = self::bind($bindings, '%' . $str . '%', PDO::PARAM_STR);
                    if ($column['db']) $columnSearch[] = '`' . $column['db'] . '` LIKE ' . $binding;
                }
            }
        }

        // Combine the filters into a single string
        $where = '';

        if (count($globalSearch)) {
            $where = '(' . implode(' OR ', $globalSearch) . ')';
        }

        if (count($columnSearch)) {
            $where = $where === '' ?
                implode(' AND ', $columnSearch) :
                $where .' AND '. implode(' AND ', $columnSearch);
        }

        if ($where !== '') {
            $where = 'WHERE ' . $where;
        }
        return $where;
    }


    public static function sql_exec($db, $bindings, $sql = null, string $type = 'all')
    {
        // Argument shifting
        if ($sql === null) {
            $sql = $bindings;
        }

        $stmt = $db->prepare($sql);

        // Bind parameters
        if (is_array($bindings)) {
            for($i = 0, $ien = count($bindings) ; $i < $ien ; $i++) {
                $binding = $bindings[$i];
                $stmt->bindValue($binding['key'], $binding['val'], $binding['type']);
            }
        }

        // Execute
        try {
            $stmt->execute();
        } catch (PDOException $e) {
            self::fatal("An SQL error occurred: ".$e->getMessage());
        }

        if ($type === 'all')      return $stmt->fetchAll();
        elseif ($type === 'both') return $stmt->fetchAll(PDO::FETCH_BOTH);
        elseif ($type === 'one')  return $stmt->fetch();
        elseif ($type === 'col')  return $stmt->fetchColumn();
    }

    private static function fatal($msg)
    {
        echo json_encode(["error" => $msg]);
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
        for($i = 0, $len = count($a) ; $i < $len ; $i++) {
            if ($a[$i][$prop]) $out[] = $a[$i][$prop];
        }
        return $out;
    }

    private static function _flatten($a, $join = ' AND ')
    {
        if (! $a) {
            return '';
        } elseif ($a && is_array($a)) {
            return implode($join, $a);
        }
        return $a;
    }
}

// index.php 20150101 - 20180511

const DS = DIRECTORY_SEPARATOR;
const INC = __DIR__ . DS . 'lib' . DS . 'php' . DS;

spl_autoload_register(function ($c) {
    $f = INC . str_replace(['\\', '_'], [DS, DS], strtolower($c)) . '.php';
    if (file_exists($f)) include $f;
    else error_log("!!! $f does not exist");
});

echo new Init(new class
{
    public
    $cfg = [
        'email' => 'markc@renta.net',
        'file'  => 'lib' . DS . '.ht_conf.php', // settings override
        'hash'  => 'SHA512-CRYPT',
        'host'  => '',
        'perp'  => 25,
        'self'  => '',
    ],
    $in = [
        'd'     => '',          // Domain (current)
        'g'     => null,        // Group/Category
        'i'     => null,        // Item or ID
        'l'     => '',          // Log (message)
        'm'     => 'list',      // Method (action)
        'o'     => 'home',      // Object (content)
        't'     => 'bootstrap', // Theme
        'x'     => '',          // XHR (request)
        'search'=> '',
        'sort'  => '',
        'order' => 'desc',
        'offset'=> '0',
        'limit' => '10',
    ],
    $out = [
        'doc'   => 'NetServa',
        'css'   => '',
        'log'   => '',
        'nav1'  => '',
        'nav2'  => '',
        'nav3'  => '',
        'head'  => 'NetServa',
        'main'  => 'Error: missing page!',
        'foot'  => 'Copyright (C) 2015-2018 Mark Constable (AGPL-3.0)',
        'js'   => '',
        'end'   => '',
    ],
    $db = [
        'host'  => '127.0.0.1', // DB site
        'name'  => 'sysadm',    // DB name
        'pass'  => 'lib' . DS . '.ht_pw', // MySQL password override
        'path'  => '/var/lib/sqlite/sysadm/sysadm.db', // SQLite DB
        'port'  => '3306',      // DB port
        'sock'  => '',          // '/run/mysqld/mysqld.sock',
        'type'  => 'mysql',     // mysql | sqlite
        'user'  => 'sysadm',    // DB user
    ],
    $nav1 = [
        'non' => [
            ['About',       '?o=about',     'fas fa-info-circle fa-fw'],
            ['News',        '?o=news&p=1',  'fas fa-newspaper fa-fw'],
            ['Contact',     '?o=contact',   'fas fa-envelope fa-fw'],
            ['Sign in',     '?o=auth',      'fas fa-sign-in-alt fa-fw'],
        ],
        'usr' => [
            ['News',        '?o=news&p=1',  'fas fa-newspaper fa-fw'],
            ['Webmail',     'webmail/',     'fas fa-envelope fa-fw'],
            ['Phpmyadmin',  'phpmyadmin/',  'fas fa-globe fa-fw'],
        ],
        'adm' => [
            ['Menu',        [
                ['News',        '?o=news&p=1',  'fas fa-newspaper fa-fw'],
                ['Webmail',     'webmail/',     'fas fa-envelope fa-fw'],
                ['Phpmyadmin',  'phpmyadmin/',  'fas fa-globe fa-fw'],
            ], 'fas fa-bars fa-fw'],
            ['Admin',       [
                ['Accounts',    '?o=accounts',  'fas fa-users fa-fw'],
                ['Vhosts',      '?o=vhosts',    'fas fa-globe fa-fw'],
                ['Mailboxes',   '?o=vmails',    'fas fa-envelope fa-fw'],
                ['Aliases',     '?o=valias',    'fas fa-envelope-square fa-fw'],
                ['DKIM',        '?o=dkim',      'fas fa-address-card fa-fw'],
                ['Domains',     '?o=domains',   'fas fa-server fa-fw'],
            ], 'fas fa-user-cog fa-fw'],
            ['Stats',       [
                ['Sys Info',    '?o=infosys',   'fas fa-tachometer-alt fa-fw'],
                ['Processes',   '?o=processes', 'fas fa-code-branch fa-fw'],
                ['Mail Info',   '?o=infomail',  'fas fa-envelope-square fa-fw'],
                ['Mail Graph',  '?o=mailgraph', 'fas fa-envelope fa-fw'],
            ], 'fas fa-chart-line fa-fw'],
        ],
    ],
    $nav2 = [
    ],
    $dns = [
        'a'     => '127.0.0.1',
        'mx'    => '',
        'ns1'   => 'ns1.',
        'ns2'   => 'ns2.',
        'prio'  => 0,
        'ttl'   => 300,
        'soa'   => [
            'primary' => 'ns1.',
            'email'   => 'admin.',
            'refresh' => 7200,
            'retry'   => 540,
            'expire'  => 604800,
            'ttl'     => 3600,
        ],
        'db' => [
            'host'  => '127.0.0.1', // Alt DNS DB site
            'name'  => 'pdns',      // Alt DNS DB name
            'pass'  => 'lib' . DS . '.ht_dns_pw', // MySQL DNS password override
            'path'  => '/var/lib/sqlite/sysadm/pdns.db', // DNS SQLite DB
            'port'  => '3306',      // Alt DNS DB port
            'sock'  => '',          // '/run/mysqld/mysqld.sock',
            'type'  => '',          // mysql | sqlite | '' to disable
            'user'  => 'pdns',      // Alt DNS DB user
        ],
    ],
    $acl = [
        0 => 'SuperAdmin',
        1 => 'Administrator',
        2 => 'User',
        3 => 'Suspended',
        9 => 'Anonymous',
    ];
});

