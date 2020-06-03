<?php

class Multitask {

    private $tasks = [];
    private $commands = [];
    public $output = false;

    public function getTasks() {
        shell_exec("screen -wipe");

        foreach ($this->tasks as $pid => $proccess) {
            $whoami = exec("whoami");
            $proccessList = array_filter(explode("\n", shell_exec("ls /var/run/screen/S-{$whoami}/ | grep {$proccess['proccessName']}")));

            if (!count($proccessList)) {
                unset($this->tasks[$pid]);
                unset($this->commands[$pid]);
            }
        }
        return $this->tasks;
    }

    public function setTasks($tasks) {
        $this->tasks = $tasks;
    }

    public function kill($pid) {
        shell_exec("kill -30 {$pid}");
        shell_exec("screen -wipe");
        unset($this->tasks[$pid]);
        return true;
    }

    public function removerCharSpecial($realname, $strip_spaces = true, $tolower = true, $bad_chars_separate = "_") {
        $bad_chars = array("'", "\\", ' ', '/', ':', '*', '?', '"', '<', '>', '|', '.');
        $realname = preg_replace('/[àáâãåäæ]/iu', 'a', $realname);
        $realname = preg_replace('/[èéêë]/iu', 'e', $realname);
        $realname = preg_replace('/[ìíîï]/iu', 'i', $realname);
        $realname = preg_replace('/[òóôõöø]/iu', 'o', $realname);
        $realname = preg_replace('/[ùúûü]/iu', 'u', $realname);
        $realname = preg_replace('/[ç]/iu', 'c', $realname);
        $realname = rawurlencode(str_replace($bad_chars, $bad_chars_separate, $realname));
        $realname = preg_replace("/%(\w{2})/", '_', $realname);
        while (strpos($realname, '__') !== false) {
            $realname = str_replace("__", "_", $realname);
        }
        if ($strip_spaces === false) {
            $realname = str_replace('_', ' ', $realname);
        }

        if (strlen($realname) > 0 && $realname[strlen($realname) - 1] == "_") {
            $realname = substr($realname, 0, -1);
        }

        return ($tolower === true) ? strtolower($realname) : $realname;
    }

    public function start(String $command, $extraInfo = [], $force = false, $name = false) {
        if (!$force && in_array($command, $this->commands)) {
            sleep(1);
            return false;
        }
        if (!$name) {
            $name = $this->removerCharSpecial($command) . uniqid();
        }

        $process = array_merge($extraInfo, ["proccessName" => $name]);
        if (!$this->output) {
            $screen_cmd = "screen -dm -S  MultiTask-{$name} {$command} 2>&1 & echo $!;";
        } else {
            $process['output_file'] = OPENMVC_TMP_FOLDER . "/" . uniqid() . "_multitask.log";
            $screen_cmd = "screen -L -Logfile {$process['output_file']} -dm -S  MultiTask-{$name} {$command} 2>&1 & echo $!;";
        }
        $pid = exec($screen_cmd);
        $pid = $pid + 1;

        $this->tasks[$pid] = $process;
        $this->commands[$pid] = $command;
        return $pid;
    }

}
