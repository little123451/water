<?php
define("WEB_XML_FILE",dirname(__FILE__).'../../config/web.xml');
define("APP_ROOT",dirname(__FILE__).'/../');

//加载core
require_once("public/utils.php");

//加载disp配置
require_once("../config/disp.config.php");

$act_lst = load_actions();
$action = $_GET['action'];
$result = $act_lst[$action]->event();

echo $result;
