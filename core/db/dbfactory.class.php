<?php
/**
 * 定义 数据库工厂类
 *
 * @author jim guo<guojf@ucweb.com>
 */

//这是为ci定义的，否则无法直接通过脚本访问数据库
define('BASEPATH', dirname(__FILE__));

require_once(BASEPATH . '/../public/config.php');
require_once(BASEPATH . '/database/DB_driver.php');
require_once(BASEPATH . '/database/DB_result.php');
require_once(BASEPATH . '/database/DB_active_rec.php');

class CI_DB extends CI_DB_active_record {
}

require_once(BASEPATH . "/../public/logfactory.class.php");
require_once(BASEPATH . '/database/drivers/mysql/mysql_driver.php');
require_once(BASEPATH . '/database/drivers/mysql/mysql_result.php');

/**
 * 数据库工厂类
 *
 * @author jim guo<guojf@ucweb.com>
 */
class DbFactory {
    var $_dbCache;
    static $_instance;

    private function __construct() {
        $this->_dbCache = array();
    }

    public static function getDbMng($dbcode) {
        if (!(self::$_instance instanceof self)) {
            self::$_instance = new self();
        }
        return self::$_instance->getOrCreateDbMng($dbcode);
    }

    private function getOrCreateDbMng($dbcode) {
        $dbConfig = Configuration::db($dbcode);
        if (!isset($this->_dbCache[$dbcode]) && $dbConfig) {
            $this->_dbCache[$dbcode] = new CI_DB_mysql_driver($dbConfig);
            $this->_dbCache[$dbcode]->initialize();
        }
        return $this->_dbCache[$dbcode];
    }
}

