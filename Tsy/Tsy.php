<?php
/**
 * Created by PhpStorm.
 * User: Castle
 * Date: 2016/4/11
 * Time: 22:20
 */

//开始各种define检测
defined('NEED_PHP_VERSION') or define('NEED_PHP_VERSION','5.5.16');
defined('APP_PATH') or define('APP_PATH',realpath('./'));
defined('APP_DEBUG') or define('APP_DEBUG',false);

define('TSY_PATH',__DIR__);
define('CONF_PATH',APP_PATH.DIRECTORY_SEPARATOR.'Conf');
define('CONTROLLER_PATH',APP_PATH.DIRECTORY_SEPARATOR.'Controller');
define('MODEL_PATH',APP_PATH.DIRECTORY_SEPARATOR.'Model');
define('PLUGS_PATH',APP_PATH.DIRECTORY_SEPARATOR.'Plugs');
//检测是否存在swoole组件，如果存在且未定义APP_MODEL为swoole则自动定义成为SWOOLE
if(extension_loaded('swoole')&&!defined('APP_MODEL')){
    define('APP_MODEL','SWOOLE');
}
//结束Define检测
if(version_compare(PHP_VERSION,'5.5.16','<')) {
    die('需要5.5.16以上的PHP版本');
}
include_once TSY_PATH.DIRECTORY_SEPARATOR.'Tsy.class.php';
$Tsy = new Tsy\Tsy();
$Tsy->start();