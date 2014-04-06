<?php
/**
 * 定义 日志处理工厂
 *
 * @author jim guo<guojf@ucweb.com>
 */
require_once(dirname(__FILE__) . '/config.php');

/**
 * 日志处理工厂
 *
 * @author jim guo<guojf@ucweb.com>
 */
class LogFactory {
    var $_logFile;
    static $_instance;

    private function __construct(){
        $log_path = Configuration::system('log_path');
        if ($log_path) {
            $this->_logFile = fopen($log_path.'.'.date('Y-m-d'), 'a');
        } else {
            $this->_logFile = NULL;
        }
    }

    public static function getLogInstance() {
        if ( !(self::$_instance instanceof self)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    function logMessage($level = 'error', $message) {
        if ($this->_logFile != NULL) {
            $line = '[' . date('Y-m-d H:i:s') .
                '][' . $level .
                ']  ' . $message .
                "\n";
            fwrite($this->_logFile, $line);
        }
    }
}

class Logger
{
    static function logMessage($level, $message) 
    {
        $log_level = Configuration::system("log_level");
        if(self::needLog($log_level, $level))
        {
            $log = LogFactory::getLogInstance();
            $log->logMessage($level, $message);
        }
    }

    private static function needLog($compared, $level)
    {
        return self::convert($level) >= self::convert($compared);
    }

    private static function convert($level)
    {
        switch($level)
        {
        case "debug":return 1;
        case "info":return 2;
        case "warn":return 3;
        case "error":return 4;
        default:return 0;
        }
    }

    static function debug($msg)
    {
        self::logMessage("debug", $msg);
    }

    static function info($msg)
    {
        self::logMessage("info", $msg);
    }

    static function warn($msg)
    {
        self::logMessage("warn", $msg);
    }

    static function error($msg)
    {
        self::logMessage("error", $msg);
    }
}

