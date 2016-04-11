<?php
/**
 * Created by PhpStorm.
 * User: Castle
 * Date: 2016/4/11
 * Time: 22:19
 */

namespace Tsy;


class Tsy
{
    protected static $class_map = [];
    function __construct()
    {
//        加载框架function函数库
        include_once TSY_PATH.DIRECTORY_SEPARATOR.'function.php';
        spl_autoload_register('Tsy::autoload');
        register_shutdown_function('Tsy::fatalError');
        set_error_handler('Tsy::appError');
        set_exception_handler('Tsy::appException');
    }
    function start(){
//        加载配置文件
        $this->loadConfig();
//        分析配置，决定是http模式还是swoole模式
////        如果是http模式则实例化http类，如果是swoole模式则实例化swoole类
        if(file_exists_case(TSY_PATH.DIRECTORY_SEPARATOR.'Mode'.DIRECTORY_SEPARATOR.APP_MODE.'.class.php')){
            include_once TSY_PATH.DIRECTORY_SEPARATOR.'Mode'.DIRECTORY_SEPARATOR.APP_MODE.'.class.php';
        }
//        开始实例化Mode类，进行初始化操作
        $ModeClassName = 'Tsy\\Mode\\'.APP_MODE;
        if(class_exists($ModeClassName)){
            $ModeClass = new $ModeClassName();
        }else{
            die(APP_MODE.':模式不存在');
        }
    }
    function loadConfig(){
        //因为涉及到多线程竞争同步的问题，所以C函数的内容必须是共享式的，
//        加载框架配置文件
        C(load_config(TSY_PATH.DIRECTORY_SEPARATOR.'Config/config.php'));
//        加载调试配置
        !APP_DEBUG or C(load_config(TSY_PATH.DIRECTORY_SEPARATOR.'Config/debug.php'));
//        加载项目配置文件,http模式则加载http.php,swoole模式则加载swoole.php
        C(load_config(CONF_PATH.DIRECTORY_SEPARATOR.'config.php'));
        !APP_DEBUG or C(load_config(CONF_PATH.DIRECTORY_SEPARATOR.'debug.php'));
        C(load_config(CONF_PATH.DIRECTORY_SEPARATOR.strtolower(APP_MODE).'.php'));
        C(load_config(CONF_PATH.DIRECTORY_SEPARATOR.strtolower(APP_MODE).'_debug.php'));
    }
    static function autoload($class){
        if(isset(self::$class_map[$class])){
            include_once self::$class_map[$class];
        }elseif(false !== strpos($class,'\\')){
            //带命名空间的类
            if(file_exists_case($class.'.class.php')){
                include($class.'.class.php');
            }
        }

    }
    static function appError(){}
    static function appException(){}
    static function fatalError(){}
}