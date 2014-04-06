<?php

require_once dirname(__FILE__) . "/AbstractDao.php";

/**
 * 一个通用的数据访问对象
 * @author zhangzj@ucweb.com
 */
class CommonDataAccessor extends AbstractDao{
    function __construct($tb_name, $dbCode){
        parent::__construct($tb_name, $dbCode);
    }
}

/**
 * 一个获取通用数据访问对象的简洁方法，函数命名仿造ThinkPHP
 * @param $tb_name 数据表名
 * @param $dbCode 不解释！
 * @return CommonDataAccessor
 */
function M($tb_name, $dbCode = ""){
    static $cache = array();
    //默认获取配置中第一个dbcode
    if(!$dbCode){
        $dbcfg = @current(Configuration::$db_cfg);
        $dbCode = @$dbcfg["dbcode"];
    }

    $key = $tb_name."#".$dbCode;
    if(!array_key_exists($key, $cache)){
        $cache[$key] = new CommonDataAccessor($tb_name, $dbCode);
    }
    return $cache[$key];
}
