<?php

class Threads extends Loader {

    private $tasks = [];
    private $executor = 'openmvc_run_thread';
    private $tmp_folder = OPENMVC_TMP_FOLDER;
    private $structure;

    public function __construct() {
        $this->load_structure();
    }

    public function setTasks($tasks) {
        $this->tasks = $tasks;
    }

    public function getTasks() {
        return $this->tasks;
    }

    public function update_tasks() {
        if (!empty($this->tasks)) {
            foreach ($this->tasks as $pid => $value) {
                $tmp = trim(shell_exec("ps -u -p {$pid}"));
                if (strstr($tmp, 'openmvc_run_thread') === false) {
                    unlink($this->tasks[$pid]['output_file']);
                    unset($this->tasks[$pid]);
                }
            }
        }
    }

    public function observe($pid, $controller_callback, $action_callback) {
        $this->executor = 'openmvc_observe_thread';
        $observerPid = $this->execute($controller_callback, $action_callback, ["pid" => $pid, "tasks" => $this->tasks]);
        unlink($this->tasks[$observerPid]['output_file']);
        unset($this->tasks[$observerPid]);
        $this->executor = 'openmvc_run_thread';
        return $observerPid;
    }

    public function execute($controller, $action, $params = []) {
        $output_file = "{$this->tmp_folder}/" . uniqid() . "_openmvc.thread";
        touch($output_file);
        if (!file_exists($output_file)) {
            echo_error("Sem permissão para escrever no arquivo \"{$output_file}\"", 500);
        }
        if (empty($this->structure[$controller])) {
            echo_error("Controller \"$controller\" não encontrado", 404);
        }
        if (!in_array($action, $this->structure[$controller])) {
            echo_error("Action \"{$action}\" não encontrada no controller \"$controller\"", 404);
        }
        $cmd = "php " . OPENMVC_DOCUMENT_ROOT . "/../openmvc common {$this->executor} \"{$output_file}\" {$controller} {$action}" . (!empty($params) ? " '" . base64_encode(serialize($params)) . "'" : "") . " > /dev/null & echo $!";
        $pid = trim(shell_exec($cmd));
        $this->tasks[$pid] = ["output_file" => $output_file];
        return $pid;
    }

    public function kill($pid) {
        shell_exec("kill -30 {$pid}");
        unset($this->tasks[$pid]);
        return true;
    }

    public function output($wait_for = "all", $clock = 2) {
        $return = [];
        $finished = false;
        while (!$finished) {
            if (empty($this->tasks)) {
                $finished = true;
            }
            foreach ($this->tasks as $pid => $task) {
                $check = $this->process_output($pid);
                if ($check["status"]) {
                    $return[$pid] = $check['output'];
                    unset($this->tasks[$pid]);
                    if ($wait_for == "first") {
                        $finished = true;
                        break;
                    } else if (is_numeric($wait_for) && $wait_for == $pid) {
                        $finished = true;
                        break;
                    }
                }
            }
            sleep((1 / $clock));
        }
        return $return;
    }

    private function process_output($pid) {
        if (!file_exists($this->tasks[$pid]['output_file'])) {
            return ["status" => false, "output" => null];
        } else {
            $output = file_get_contents($this->tasks[$pid]['output_file']);
            if (strlen($output) == 0) {
                return ["status" => false, "output" => null];
            }
            unlink($this->tasks[$pid]['output_file']);
            return ["status" => true, "output" => unserialize($output)];
        }
    }

    private function load_structure() {
        if (empty($this->structure)) {
            foreach (glob(__DIR__ . "/../../../*.php") as $filepath) {
                $tmp = explode("/", $filepath);
                $classname = str_replace(".php", "", end($tmp));
                if (!class_exists(ucfirst($classname))) {
                    include_once $filepath;
                }
                $this->structure[$classname] = get_class_methods($classname);
            }
            unset($tmp);
        }
    }

}
