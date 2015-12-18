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
    // ʵ��������
    private static $_instance = array();
    function __construct(){
        C('CASTLE_PATH',__DIR__);
        $this->configLoad(__DIR__.'/Config/config.php');
        // ע��AUTOLOAD����
        spl_autoload_register('Castle::autoload');
        // �趨������쳣����
        register_shutdown_function('Castle::fatalError');
        set_error_handler('Castle::appError');
        set_exception_handler('Castle::appException');
    }
    static function configLoad($file){
        C(load_config($file));
    }
    static function autoload($class){
        // ����Ƿ����ӳ��
        if(isset(self::$_map[$class])) {
            include self::$_map[$class];
        }elseif(false !== strpos($class,'\\')){
            $name           =   strstr($class, '\\', true);
            if(in_array($name,array('Think','Org','Behavior','Com','Vendor')) || is_dir(C('LIB_PATH').$name)){
                // LibraryĿ¼����������ռ��Զ���λ
                $path       =   C('LIB_PATH');
            }else{
                // ����Զ��������ռ� �������ģ��Ϊ�����ռ�
                $namespace  =   C('AUTOLOAD_NAMESPACE');
                $path       =   isset($namespace[$name])? dirname($namespace[$name]).'/' : C('APP_PATH');
            }
            $filename       =   $path . str_replace('\\', '/', $class) . C('EXT');
            if(is_file($filename)) {
                // Win���������ϸ����ִ�Сд
                if (C('IS_WIN') && false === strpos(str_replace('/', '\\', realpath($filename)), $class . C('EXT'))){
                    return ;
                }
                include $filename;
            }
        }elseif (!C('APP_USE_NAMESPACE')) {
            // �Զ����ص�����
            foreach(explode(',',C('APP_AUTOLOAD_LAYER')) as $layer){
                if(substr($class,-strlen($layer))==$layer){
                    if(require_cache(C('MODULE_PATH').$layer.'/'.$class.C('EXT'))) {
                        return ;
                    }
                }
            }
            // �����Զ�����·�����ý��г�������
            foreach (explode(',',C('APP_AUTOLOAD_PATH')) as $path){
                if(import($path.'.'.$class))
                    // ���������ɹ��򷵻�
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