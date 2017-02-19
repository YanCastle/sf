<?php
/**
 * Created by PhpStorm.
 * User: Castle
 * Date: 2016/4/11
 * Time: 22:20
 */
if('options'==strtolower($_SERVER['REQUEST_METHOD'])){
    exit();
}
if(file_exists(dirname($APP_PATH).'/vendor/autoload.php')){
    include dirname($APP_PATH).'/vendor/autoload.php';
}
include_once __DIR__.'/Tsy.class.php';
$Tsy = new Tsy\Tsy();
$Tsy->start();