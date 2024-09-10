<?php

// lib/php/init.php 20150101 - 20240907
// Copyright (C) 2015-2024 Mark Constable <markc@renta.net> (AGPL-3.0)

declare(strict_types=1);

class Init
{
    private Theme $theme;

    public function __construct(private object $g)
    {
elog(__METHOD__);

        $this->initializeSession();
        $this->configureEnvironment();
        $this->initializeTheme();
        $this->loadPlugin();
        $this->processOutput();
    }

    public function __toString(): string
    {
elog(__METHOD__);

        $g = $this->theme->g;
        $x = $g->in['x'];

        return match ($x) {
            //'html' => $g->out['main'], ??
            'text' => $this->stripAll($g->out['main']),
            'json' => $this->jsonValidate($g->out['main']),
            default => $this->theme->html(),
        };
    }

    public function __destruct()
    {
        $executionTime = round(microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'], 4);
        elog("{$_SERVER['REMOTE_ADDR']} - Execution time: {$executionTime}s");
    }

    private function initializeSession(): void
    {
elog(__METHOD__);

        session_start();
        $_SESSION['c'] ??= Util::random_token(32);

        // $this->logSessionData();
    }

    private function configureEnvironment(): void
    {
elog(__METHOD__);

        $this->g->cfg['host'] ??= getenv('HOSTNAME');
        util::cfg($this->g);
        $this->g->in = util::esc($this->g->in);
        $this->g->cfg['self'] = str_replace('index.php', '', $_SERVER['PHP_SELF']);
    }

    private function initializeTheme(): void
    {
elog(__METHOD__);

        $t = util::ses('t', '', $this->g->in['t']);
        $themeClass = $this->getThemeClass($t);
        $this->theme = new $themeClass($this->g);
    }

    private function loadPlugin(): void
    {
elog(__METHOD__);

        $pluginClass = 'plugins_' . $this->g->in['o'];
        if (class_exists($pluginClass)) {
            $this->g->in['a'] ? util::chkapi($this->g) : util::remember($this->g);
            $this->g->out['main'] = (string) new $pluginClass($this->theme);
        } else {
            $this->g->out['main'] = "Error: no plugin object!";
        }
    }

    private function processOutput(): void
    {
elog(__METHOD__);

        if (empty($this->g->in['x'])) {
            foreach ($this->g->out as $k => $v) {
                $this->g->out[$k] = method_exists($this->theme, $k) ? $this->theme->$k() : $v;
            }
        }
    }

    private function stripAll(string $content): string
    {
elog(__METHOD__);

        return trim(preg_replace('/^\h*\v+/m', '', strip_tags($content)));
    }

    private function jsonValidate($data): string
    {
elog(__METHOD__);

        if (json_validate($data)) {
            header('Content-Type: application/json');
            return $data;
        } else {
            return json_last_error_msg();
        }
    }

    private function getThemeClass(string $theme): string
    {
elog(__METHOD__);

        $t1 = 'themes_' . $theme . '_' . $this->g->in['o'];
        $t2 = 'themes_' . $theme . '_theme';
        //return class_exists($t1) ? $t1 : (class_exists($t2) ? $t2 : 'Theme');
        $defaultTheme = Theme::class;

        if (class_exists($t1) && is_subclass_of($t1, Theme::class)) {
            return $t1;
        }
    
        if (class_exists($t2) && is_subclass_of($t2, Theme::class)) {
            return $t2;
        }
    
        assert(class_exists($defaultTheme), "Default theme class does not exist");
        return $defaultTheme;
    }

    private function logSessionData(): void
    {
elog(__METHOD__);

        elog('GET=' . var_export($_GET, true));
        elog('POST=' . var_export($_POST, true));
        elog('SESSION=' . var_export($_SESSION, true));
    }
}

function dbg($var = null): void
{
    if (is_object($var)) {
        $reflection = new ReflectionObject($var);
        error_log($reflection->getName() . " Object:\n" . print_r($reflection->getProperties(), true));
    } else {
        error_log(print_r($var, true));
    }
}
function elog(string $content): void
{
    if (DBG) {
        error_log($content);
    }
}
