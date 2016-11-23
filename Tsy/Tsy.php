<?php
/**
 * Created by PhpStorm.
 * User: Castle
 * Date: 2016/4/11
 * Time: 22:20
 */

date_default_timezone_set('Asia/Shanghai');
define('LOG_SQL','SQL');
define('LOG_MSG','MSG');
define('LOG_TIP','TIP');
//开始各种define检测
defined('NEED_PHP_VERSION') or define('NEED_PHP_VERSION','5.5.16');
defined('APP_DEBUG') or define('APP_DEBUG',false);
defined('DB_DEBUG') or define('DB_DEBUG',APP_DEBUG);
defined('APP_MODE') or define('APP_MODE','Http');
defined('APP_NAME') or define('APP_NAME',defined('DEFAULT_MODULE')?DEFAULT_MODULE:md5($_SERVER['PHP_SELF']));
//defined('PACKAGE_EOF') or define('PACKAGE_EOF',"\r\n\r\n");
isset($APP_PATH) or $APP_PATH='.';
if(isset($APP_PATH)&&!is_dir($APP_PATH)){
    mkdir($APP_PATH);
}
define('IS_WIN',strstr(PHP_OS, 'WIN') ? 1 : 0 );
define('TEMP_DIR',IS_WIN?$_SERVER['TEMP']:'/tmp');
define('APP_PATH',isset($APP_PATH)?realpath($APP_PATH):realpath('.'));
define('RUNTIME_PATH',isset($RUNTIME_PATH)?$RUNTIME_PATH:TEMP_DIR.DIRECTORY_SEPARATOR.APP_NAME.DIRECTORY_SEPARATOR.'Runtime');
define('TEMP_PATH',RUNTIME_PATH.DIRECTORY_SEPARATOR.'Temp');
defined('UPLOAD_PATH') or define('UPLOAD_PATH',dirname($_SERVER['SCRIPT_FILENAME']).DIRECTORY_SEPARATOR.'Upload');
//定义配置文件后缀
defined('CONFIG_SUFFIX') or define('CONFIG_SUFFIX','');

defined('MODULES') or define('MODULES','' );

define('HTTP_COMMENT',"\x01");

if(!is_dir(RUNTIME_PATH)){
    mkdir(RUNTIME_PATH,0777,true) or die("临时目录不可写");
}

define('TSY_PATH',__DIR__);
define('CONF_PATH',APP_PATH.DIRECTORY_SEPARATOR.'Common/Config');
//检测是否存在swoole组件，如果存在且未定义APP_MODE为swoole则自动定义成为SWOOLE
if(extension_loaded('swoole')&&!defined('APP_MODE')){
    /** @noinspection PhpConstantReassignmentInspection */
    define('APP_MODE','Swoole');
}
//结束Define检测
if(version_compare(PHP_VERSION,'5.5.0','<')) {
    die('需要5.5.0以上的PHP版本');
}
define('APP_MODE_LOW',strtolower(APP_MODE));
if('http'==strtolower(APP_MODE)&&isset($_SERVER['REQUEST_METHOD'])&&'OPTIONS'==$_SERVER['REQUEST_METHOD']){
    if(isset($_SERVER['HTTP_ORIGIN'])) {
        define('Domain', $_SERVER['HTTP_ORIGIN']);
        header('Access-Control-Allow-Origin:' . $_SERVER['HTTP_ORIGIN']);
    }
    header('Access-Control-Allow-Credentials:true');
    header('Access-Control-Request-Method:GET,POST');
    header('Access-Control-Allow-Headers:X-Requested-With,Cookie,ContentType');
}
include_once TSY_PATH.DIRECTORY_SEPARATOR.'Tsy.class.php';
$Tsy = new Tsy\Tsy();
$Tsy->start();