<?php
/**
 * Created by PhpStorm.
 * User: Castle
 * Date: 2016/4/11
 * Time: 22:24
 */
error_reporting(E_ALL);
$APP_PATH = __DIR__.'/Example';
$RUNTIME_PATH = '../Runtime';
define('DEFAULT_MODULE','Application');//这个版本中必须定义默认模块，其值与APP_PATH的最后一个目录相同
include __DIR__.'/../Tsy/Tsy.php';