<?php
/**
 * Created by PhpStorm.
 * User: Castle
 * Date: 2016/4/11
 * Time: 22:24
 */
error_reporting(E_ALL);
$APP_PATH = 'Example';
//$RUNTIME_PATH = 'Runtime';
define('APP_DEBUG',true);
define('APP_MODE','Http');
define('DEFAULT_MODULE','Abc');//这个版本中必须定义默认模块，其值与APP_PATH的最后一个目录相同
include '../Tsy/Tsy.php';