<?php
/**
 * Created by PhpStorm.
 * User: Castle
 * Date: 2016/4/11
 * Time: 22:19
 */

namespace Tsy;


use Tsy\Library\Session;

class Tsy
{
    protected static $class_map = [];
    function __construct()
    {
//        加载框架function函数库
        include_once TSY_PATH.DIRECTORY_SEPARATOR.'function.php';
        spl_autoload_register('Tsy\Tsy::autoload');
        register_shutdown_function('Tsy\Tsy::fatalError');
        set_error_handler('Tsy\Tsy::appError');
        set_exception_handler('Tsy\Tsy::appException');
    }
    function start(){
//        加载配置文件
        $this->loadFunctions();//加载框架function和项目function
        
        $this->loadConfig();
        $GLOBALS['Config']=C();
        if(defined('APP_BUILD')&&APP_BUILD)
            build_cache();
//        分析配置，决定是http模式还是swoole模式
////        如果是http模式则实例化http类，如果是swoole模式则实例化swoole类
        if(file_exists(TSY_PATH.DIRECTORY_SEPARATOR.'Mode'.DIRECTORY_SEPARATOR.ucfirst(strtolower(APP_MODE)).'.class.php')){
            include_once TSY_PATH.DIRECTORY_SEPARATOR.'Mode'.DIRECTORY_SEPARATOR.ucfirst(strtolower(APP_MODE)).'.class.php';
        }else{
            die('MODE:'.APP_MODE.'不存在');
        }
//        $Session = new Session();
//        session_set_save_handler($Session,true);
//        开始实例化Mode类，进行初始化操作
        $ModeClassName = 'Tsy\\Mode\\'.ucfirst(strtolower(APP_MODE));
        if(class_exists($ModeClassName)){
            $ModeClass = new $ModeClassName();
        }else{
            die(APP_MODE.':模式不存在');
        }
//        加载模式处理类，开始模式处理
        $ModeClass->start();
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
        !APP_DEBUG or C(load_config(CONF_PATH.DIRECTORY_SEPARATOR.strtolower(APP_MODE).'_debug.php'));
        !defined('CONFIG_MODE') or C(load_config(CONF_PATH.DIRECTORY_SEPARATOR.strtolower(CONFIG_MODE).'.php'));
    }
    function loadFunctions(){
        $FunctionPath = TSY_PATH.'/Library/Functions';
        foreach (scandir($FunctionPath) as $path){
            if(!in_array($path,['.','..'])&&'php'==substr($path,-3)){
                include ($FunctionPath.'/'.$path);
            }
        }
        $FunctionPath = APP_PATH.'/Common/Functions';
        foreach (scandir($FunctionPath) as $path){
            if(!in_array($path,['.','..'])&&'php'==substr($path,-3)){
                include ($FunctionPath.'/'.$path);
            }
        }
    }
    static function autoload($class){
        if(isset(self::$class_map[$class])){
            include_once self::$class_map[$class];
        }elseif(false !== strpos($class,'\\')) {
            //带命名空间的类
            if ('Tsy' == substr($class, 0, 3)){
                $file_path = dirname(TSY_PATH) . DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, $class) . '.class.php';
            }else{
                $file_path = APP_PATH.DIRECTORY_SEPARATOR.str_replace('\\', DIRECTORY_SEPARATOR, $class) . '.class.php';
            }
            if(file_exists($file_path)){
                include($file_path);
            }
        }

    }
    /**
     * 自定义异常处理
     * @access public
     * @param mixed $e 异常对象
     */
    static public function appException($e) {
        $error = array();
        $error['message']   =   $e->getMessage();
        $trace              =   $e->getTrace();
        if('E'==$trace[0]['function']) {
            $error['file']  =   $trace[0]['file'];
            $error['line']  =   $trace[0]['line'];
        }else{
            $error['file']  =   $e->getFile();
            $error['line']  =   $e->getLine();
        }
        $error['trace']     =   $e->getTraceAsString();
    }

    /**
     * 自定义错误处理
     * @access public
     * @param int $errno 错误类型
     * @param string $errstr 错误信息
     * @param string $errfile 错误文件
     * @param int $errline 错误行数
     * @return void
     */
    static public function appError($errno, $errstr, $errfile, $errline) {
        switch ($errno) {
            case E_ERROR:
            case E_PARSE:
            case E_CORE_ERROR:
            case E_COMPILE_ERROR:
            case E_USER_ERROR:
                ob_end_clean();
                $errorStr = "$errstr ".$errfile." 第 $errline 行.";
                break;
            default:
                $errorStr = "[$errno] $errstr ".$errfile." 第 $errline 行.";
                break;
        }
    }

    // 致命错误捕获
    static public function fatalError() {
        if ($e = error_get_last()) {
            switch($e['type']){
                case E_ERROR:
                case E_PARSE:
                case E_CORE_ERROR:
                case E_COMPILE_ERROR:
                case E_USER_ERROR:
                    ob_end_clean();
                    break;
            }
        }
    }
}
