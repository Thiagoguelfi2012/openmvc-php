<?php

class modelObject {

    protected $table;
    protected $model;
    protected $fields;
    protected $functions;
    protected $object;

    function __construct($fields) {
        if (!empty($fields)) {
            foreach ((array) $fields as $field => $value) {
                $this->fields[] = $field;
//            Set Propriety
                $this->object[$field] = $value;
//            Create Setter
                $this->functions[] = $funcName = "set" . ucfirst($field);
                $this->{$funcName} = function($method, $arg) {
                    $field = lcfirst(substr($method, 3));
                    $this->object[$field] = $arg;
                };
//            Create Getter
                $this->functions[] = $funcName = "get" . ucfirst($field);
                $this->{$funcName} = function($method) {
                    $field = lcfirst(substr($method, 3));
                    return $this->object[$field];
                };
            }
        } else {
            return (OBJECT) array();
        }
    }

    public function internalObject() {
        return (object) $this->object;
    }

    public function save() {
        if (empty($this->model)) {
            global $db;
            $this->model = new Model($db, $this->table);
        }
        return $this->model->save($this);
    }

    public function __call($method, $arguments) {
        if (in_array($method, $this->functions)) {
            return call_user_func_array(Closure::bind($this->$method, $this, get_called_class()), array_merge([$method], $arguments));
        } else {
            echo_error("Function \"{$method}()\" not found on {$this->table} model!<br/>Getters and Setters for {$this->table} model.<pre>" . implode("()<br/>", $this->functions) . "()</pre>", 500);
        }
    }

}
