<?php
/**
 * Created by PhpStorm.
 * User: Castle
 * Date: 2016/4/11
 * Time: 22:20
 */
function is_first_receive($fd){
    static $_type=[];
    if(is_array($fd)){
        //Store the Map of Port To Type
        $_type=array_merge($_type,$fd);
    }else{

    }
}

function session($name,$value=false){}

/**
 * 缓存
 * @param $key
 * @param bool $value
 * @param bool $expire
 */
function cache($key,$value=false,$expire=false){

}
/**
 * 获取和设置配置参数 支持批量定义
 * @param string|array $name 配置变量
 * @param mixed $value 配置值
 * @param mixed $default 默认值
 * @return mixed
 */
function C($name=null, $value=null,$default=null) {
    static $_config=[];
    // 无参数时获取所有
//    if(!isset($_config)){$_config=[];}
    if (empty($name)) {
        return $_config;
    }
    // 优先执行设置获取或赋值
    if (is_string($name)) {
        if (!strpos($name, '.')) {
            $name = strtoupper($name);
            if (is_null($value))
                return isset($_config[$name]) ? $_config[$name] : $default;
            $_config[$name] = $value;
            return null;
        }
        // 二维数组设置和获取支持
        $name = explode('.', $name);
        $name[0]   =  strtoupper($name[0]);
        if (is_null($value))
            return isset($_config[$name[0]][$name[1]]) ? $_config[$name[0]][$name[1]] : $default;
        $_config[$name[0]][$name[1]] = $value;
        return null;
    }
    // 批量设置
    if (is_array($name)){
        $_config = array_merge($_config, array_change_key_case($name,CASE_UPPER));
        return null;
    }
    return null; // 避免非法参数
}

/**
 * 加载配置文件 支持格式转换 仅支持一级配置
 * @param string $file 配置文件名
 * @param string $parse 配置解析方法 有些格式需要用户自己解析
 * @return array
 */
function load_config($file,$parse='php'){
    $ext  = pathinfo($file,PATHINFO_EXTENSION);
    switch($ext){
        case 'php':
            return include $file;
        case 'ini':
            return parse_ini_file($file);
        case 'yaml':
            return yaml_parse_file($file);
        case 'xml':
            return (array)simplexml_load_file($file);
        case 'json':
            return json_decode(file_get_contents($file), true);
        default:
            if(function_exists($parse)){
                return $parse($file);
            }else{
                E(L('_NOT_SUPPORT_').':'.$ext);
            }
    }
}
