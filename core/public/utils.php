<?php

import("db.CommonDataAccessor");

function getIp()
{
    if (getenv("HTTP_CLIENT_IP") && strcasecmp(getenv("HTTP_CLIENT_IP"), "unknown"))
        $ip = getenv("HTTP_CLIENT_IP");
    else if (getenv("HTTP_X_FORWARDED_FOR") && strcasecmp(getenv("HTTP_X_FORWARDED_FOR"), "unknown"))
        $ip = getenv("HTTP_X_FORWARDED_FOR");
    else if (getenv("REMOTE_ADDR") && strcasecmp(getenv("REMOTE_ADDR"), "unknown"))
        $ip = getenv("REMOTE_ADDR");
    else if (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], "unknown"))
        $ip = $_SERVER['REMOTE_ADDR'];
    else
        $ip = "unknown";
    return($ip);
}

function now(){
    return date("Y-m-d H:i:s");
}

function build_resp($success, $msg, $data = ""){
    $ret = new stdclass();
    $ret->success = $success;
    $ret->message = $msg;
    $ret->msg = $msg;
    if($data){
        $ret->data = $data;
    }
    return json_encode($ret);
}

function build_lst($total, $data){
    $ret = new stdclass();
    if($total >= 0){
        $ret->total = $total;
    }
    $ret->data = $data;
    return json_encode($ret);
}

function http_post($url, $post){
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_POST, 1); 
    curl_setopt($curl, CURLOPT_POSTFIELDS, $post);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); 
    curl_setopt ($curl, CURLOPT_CONNECTTIMEOUT, 10);
    $contents = curl_exec($curl);
    curl_close($curl); 
    return $contents;
}

function http_get($url){
    $curl = curl_init();
    curl_setopt ($curl, CURLOPT_URL, $url);
    curl_setopt ($curl, CURLOPT_RETURNTRANSFER, 1); 
    curl_setopt ($curl, CURLOPT_CONNECTTIMEOUT, 10);
    $contents = curl_exec($curl); 
    curl_close($curl);
    return $contents;
}

function require_cache($file){
    static $cache = array();

    if(!array_key_exists($file, $cache)){
        $cache[$file] = 1;
        require_once($file);
    }
}

function import($path){

    $root = APP_ROOT;

    $p_arr = array_filter(explode(".", $path));
    if (!$p_arr) {
        throw new Exception("import no file!");
    }

    $file = "";
    if ($p_arr[0] == "db") {
        $file .= $root."core/db";
    } else if ($p_arr[0] == "public") {
        $file .= $root."core/public";
    } else if ($p_arr[0] == "@") {
        $file .= realpath($root);
    } else {
        $file .= "";
    }

    for($i=1; $i<count($p_arr); ++$i){
        $file .= "/".$p_arr[$i];
    }

    //加上php后缀名
    $filepath = $file.".php";
    if(!is_file($filepath)){
        $filepath = $file.".class.php";
    }
    if(!is_file($filepath)){
        throw new Exception("not exists file: {$file}.php or {$file}.class.php");
    }

    require_cache($filepath);
}

function get_val($param,$key,$default = ''){
    if (!array_key_exists($key,$param)){
        return $default;
    } else return $param[$key];
}

