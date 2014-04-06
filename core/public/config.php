<?php
///Author: zhangzj<zhangzj@ucweb.com>
///Date: 2012-11-15
///Description: 配置读取类，用于读取同目录下的web.xml文件，注意，该文件必须与web.xml在同一目录下
//              web.xml是框架唯一的配置文件，才有单一配置文件的方式对后台开发利大于弊，避免了配置和
//              部署的复杂性，采用xml格式进行配置，一则格式化文本更易于识别配置，二则在编码过程中，
//              更容易起到约束作用，能够清晰划分出哪些属于配置，哪些属于代码

//--------------------------------------------------------
//该文件用于读取web.xml配置文件

class Configuration{
    static $root = "";
    static $sys_cfg = array();
    static $db_cfg = array();
    static $user_cfg = array();

    //初始化配置
    static function init(){
        if(!defined("WEB_XML_FILE")){
            syslog(LOG_NOTICE, "no WEB_XML_FILE configuration!");
            return;
        }

        $cfg = simplexml_load_file(WEB_XML_FILE);

        self::initEmbededVal();
        self::parseDbCfg($cfg);
        self::parseUserCfg($cfg);
        self::parseSysCfg($cfg);
    }

    private static function initEmbededVal(){
        //XXX 千万别画蛇添足加个 / 到末尾
        self::$root = realpath(dirname(__FILE__)."../../..");
    }

    private static function parseSysCfg($cfg){
        self::$sys_cfg["log_path"] = self::replEmbedded((string)$cfg->system->log_path);
        self::$sys_cfg["log_level"] = (string)$cfg->system->log_level;
    }

    private static function parseDbCfg($cfg){
        foreach($cfg->db as $db){
            $e = array();
            $e["dbcode"] = (string)$db->dbcode;
            $e["hostname"] = (string)$db->hostname;
            $e["port"] = (int)$db->port;
            $e["username"] = (string)$db->username;
            $e["password"] = (string)$db->password;
            $e["database"] = (string)$db->database;
            $e["db_debug"] = (bool)$db->db_debug;
            $e["save_queries"] = (bool)$db->save_queries;

            self::$db_cfg[$e["dbcode"]] = $e;
        }
    }

    private static function parseUserCfg($cfg){
        $user_arr = @$cfg->user;
        foreach($user_arr as $user){
            if(!@(string)$user->key){
                continue;
            }

            $key = (string)$user->key;
            if(!array_key_exists($key, self::$user_cfg)){
                self::$user_cfg[$key] = array();
            }

            foreach($user->children() as $n){
                //如果是直接值
                if($n->getName() == "value"){
                    self::$user_cfg[$key][] = self::replEmbedded((string)$n);
                }

                //如果是map
                if($n->getName() == "map"){
                    $arr = array();
                    foreach($n->item as $item){
                        if(!$item["key"])
                            continue;

                        $k = (string)$item["key"];
                        $v = (string)$item["value"];
                        $arr[$k] = $v;
                    }
                    self::$user_cfg[$key][] = $arr;
                }
            }
        }
    }

    //系统配置, 仅获取<system>块内的配置
    static function system($key){
        return @self::$sys_cfg[$key];
    }

    //数据库配置, 仅获取<db>块内的配置
    static function db($db_code){
        return @self::$db_cfg[$db_code];
    }

    //自定义的配置, 获取所有<user>块的配置集
    static function user($key){
        $val = @self::$user_cfg[$key];
        //如果只有一个元素，则不要返回数据，返回变量值即可
        if(count($val) == 1){
            return $val[0];
        }
        return $val;
    }

    //替换变量
    private static function replEmbedded($val){
        $val = str_replace("{root}", self::$root, $val);
        return $val;
    }

}

//初始化配置
Configuration::init();

