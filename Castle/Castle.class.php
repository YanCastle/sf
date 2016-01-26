<?php

/**
 * Created by PhpStorm.
 * User: castle
 * Date: 2015/12/17
 * Time: 10:29
 */
class Castle
{
    private static $_map=[];
    private static $_instance = array();
    function __construct(){
        C('CASTLE_PATH',__DIR__);
        $this->configLoad(__DIR__.'/Config/config.php');
        spl_autoload_register('Castle::autoload');
        register_shutdown_function('Castle::fatalError');
        set_error_handler('Castle::appError');
        set_exception_handler('Castle::appException');
    }
    function start(){
        C('APP',true);//标记已有应用
//        加载模块配置文件
//        实例化控制器，
        if(!defined('METHOD')){
            define('METHOD','HTTP');
        }
        defined('APP_DEBUG') or define('APP_DEBUG',false);
        if(file_exists(implode('/',[APP_PATH,MODULE_NAME,'Conf','config.php']))){
            C(load_config(implode('/',[APP_PATH,MODULE_NAME,'Conf','config.php'])));
        }
        switch(METHOD){
            case 'HTTP':break;
            case 'SWOOLE':
                if(file_exists(APP_PATH.'/Controller/SwooleController.class.php')){
                    $swoole_path = '\\'.MODULE_NAME.'\\SwooleController';
                }else{
                    $swoole_path = '\\Core\\SwooleController';
                }
                $swoole = new $swoole_path();
                $Hosts = explode(',',str_replace(':',',',HOST));
                if(count($Hosts)%2==0){
                    //TODO 多个监听地址和端口处理
//                    foreach($Hosts as $k=>$v){
//                        if($k%2==0&&is_numeric($v)){continue;}elseif(long2ip(ip2long($v))==$v){continue;}else{exit(false);}
//                    }
//                    $GLOBALS['TABLE']=new swoole_table();
                    $server = new swoole_server($Hosts[0],$Hosts[1]);

                    $server->set([
                        'daemonize' => 0, //自动进入守护进程
                        'log_file' => 'swoole.log',
                        'task_worker_num' => 1,//开启task功能，
                        'dispatch_mode '=>3,//轮询模式
                        'worker_num'=>2,
                        'open_eof_check'=>true,
//                        'package_eof'=>"\r\n"
                    ]);
                    if(count($Hosts)>2){
                        for($i=2;$i<count($Hosts);$i+=2){
                            $server->addlistener($Hosts[$i],$Hosts[$i+1],SWOOLE_SOCK_TCP);
                        }
                    }
                    $server->on('Start',[$swoole,'Start']);
                    $server->on('Shutdown',[$swoole,'Shutdown']);
                    $server->on('WorkerStart',[$swoole,'WorkerStart']);
                    $server->on('WorkerStop',[$swoole,'WorkerStop']);
//                    $server->on('Timer',[$swoole,'Timer']);
                    $server->on('Connect',[$swoole,'Connect']);
                    $server->on('Receive',[$swoole,'Receive']);
                    $server->on('Packet',[$swoole,'Packet']);
                    $server->on('Close',[$swoole,'Close']);
                    $server->on('Task',[$swoole,'Task']);
                    $server->on('Finish',[$swoole,'Finish']);
                    $server->on('PipeMessage',[$swoole,'PipeMessage']);
                    $server->on('WorkerError',[$swoole,'WorkerError']);
                    $server->on('ManagerStart',[$swoole,'ManagerStart']);
                    $server->on('ManagerStop',[$swoole,'ManagerStop']);
                    $server->start();
                }else{
                    exit(false);
                }
//                $server = new swoole_server()
                break;
        }
    }
    static function configLoad($file){
        C(load_config($file));
    }
    static function autoload($class){
        if(isset(self::$_map[$class])) {
            include self::$_map[$class];
        }elseif(false !== strpos($class,'\\')){
            $name           =   strstr($class, '\\', true);
            if(in_array($name,array('Core','Plugs')) || is_dir(C('LIB_PATH').$name)){
                $path       =   C('LIB_PATH');
            }else{
                $namespace  =   C('AUTOLOAD_NAMESPACE');
                $path       =   isset($namespace[$name])? dirname($namespace[$name]).'/' : C('APP_PATH');
            }
            $filename       =   $path .'/'. str_replace('\\', '/', $class) . C('EXT');
            if(is_file($filename)) {
                if (C('IS_WIN') && false === strpos(str_replace('/', '\\', realpath($filename)), $class . C('EXT'))){
                    return ;
                }
                include $filename;
            }
        }elseif (!C('APP_USE_NAMESPACE')) {
            foreach(explode(',',C('APP_AUTOLOAD_LAYER')) as $layer){
                if(substr($class,-strlen($layer))==$layer){
                    if(require_cache(C('MODULE_PATH').$layer.'/'.$class.C('EXT'))) {
                        return ;
                    }
                }
            }
            foreach (explode(',',C('APP_AUTOLOAD_PATH')) as $path){
                if(import($path.'.'.$class))
                    return ;
            }
        }
    }
    static function fatalError(){

    }
    static function appError($errno, $errstr, $errfile, $errline){
        $a =1;
    }
    static function appException($e){
//        php_
    }
    static function core(){

    }
}