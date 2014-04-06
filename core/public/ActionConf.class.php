<?php
class ActionConf{

    private $class;
    private $ac;

    public function __construct($class_name,$ac_name){
        $this->class = $class_name;
        $this->ac = $ac_name;
    }

    public function event(){
        $this->logBeg();

        $t1 = microtime();

        $param = $_REQUEST;
        $obj = new $this->class;
        $ac = $this->ac;
        $result = $obj->$ac($param);
        echo $result;

        $t2 = microtime();

        $this->logFin($t2-$t1);
    }

    private function logBeg(){
        logger::debug("call action => {$this->class}.{$this->ac}");
    }

    private function logFin($t){
        logger::debug("finished action => {$this->class}.{$this->ac}, cost time = {$t}");
        logger::debug("");
    }
}