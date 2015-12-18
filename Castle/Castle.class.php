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
    // 实例化对象
    private static $_instance = array();
    function __construct(){
        // 注册AUTOLOAD方法
        spl_autoload_register('Castle::autoload');
        // 设定错误和异常处理
        register_shutdown_function('Castle::fatalError');
        set_error_handler('Castle::appError');
        set_exception_handler('Castle::appException');
    }
    static function configLoad($file){
        C(load_config($file));
    }
    static function autoload($class){
        // 检查是否存在映射
        if(isset(self::$_map[$class])) {
            include self::$_map[$class];
        }elseif(false !== strpos($class,'\\')){
            $name           =   strstr($class, '\\', true);
            if(in_array($name,array('Think','Org','Behavior','Com','Vendor')) || is_dir(LIB_PATH.$name)){
                // Library目录下面的命名空间自动定位
                $path       =   LIB_PATH;
            }else{
                // 检测自定义命名空间 否则就以模块为命名空间
                $namespace  =   C('AUTOLOAD_NAMESPACE');
                $path       =   isset($namespace[$name])? dirname($namespace[$name]).'/' : APP_PATH;
            }
            $filename       =   $path . str_replace('\\', '/', $class) . EXT;
            if(is_file($filename)) {
                // Win环境下面严格区分大小写
                if (IS_WIN && false === strpos(str_replace('/', '\\', realpath($filename)), $class . EXT)){
                    return ;
                }
                include $filename;
            }
        }elseif (!C('APP_USE_NAMESPACE')) {
            // 自动加载的类库层
            foreach(explode(',',C('APP_AUTOLOAD_LAYER')) as $layer){
                if(substr($class,-strlen($layer))==$layer){
                    if(require_cache(MODULE_PATH.$layer.'/'.$class.EXT)) {
                        return ;
                    }
                }
            }
            // 根据自动加载路径设置进行尝试搜索
            foreach (explode(',',C('APP_AUTOLOAD_PATH')) as $path){
                if(import($path.'.'.$class))
                    // 如果加载类成功则返回
                    return ;
            }
        }
    }
    static function fatalError(){

    }
    static function appError(){

    }
    static function appException(){

    }
    static function core(){

    }
}