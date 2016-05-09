<?php
/**
 * Created by PhpStorm.
 * User: Castle
 * Date: 2016/4/11
 * Time: 22:20
 */


//开始各种define检测
defined('NEED_PHP_VERSION') or define('NEED_PHP_VERSION','5.5.16');
defined('APP_DEBUG') or define('APP_DEBUG',false);
define('APP_PATH',realpath($APP_PATH));
define('RUNTIME_PATH',$RUNTIME_PATH?$RUNTIME_PATH:APP_PATH.DIRECTORY_SEPARATOR.'Runtime');
define('TEMP_PATH',RUNTIME_PATH.DIRECTORY_SEPARATOR.'Temp');

//定义配置文件后缀
defined('CONFIG_SUFFIX') or define('CONFIG_SUFFIX','');


define('HTTP_COMMENT',"\x01");

if(!is_dir(RUNTIME_PATH)){
    if(is_writable(dirname(RUNTIME_PATH)))
        mkdir(RUNTIME_PATH,0777,true);
    else
        die("临时目录不可写");
}

define('TSY_PATH',__DIR__);
define('CONF_PATH',APP_PATH.DIRECTORY_SEPARATOR.'Common/Config');
//检测是否存在swoole组件，如果存在且未定义APP_MODE为swoole则自动定义成为SWOOLE
if(extension_loaded('swoole')&&!defined('APP_MODE')){
    define('APP_MODE','SWOOLE');
}
//结束Define检测
if(version_compare(PHP_VERSION,'5.5.0','<')) {
    die('需要5.5.0以上的PHP版本');
}
include_once TSY_PATH.DIRECTORY_SEPARATOR.'Tsy.class.php';
$Tsy = new Tsy\Tsy();
$Tsy->start();