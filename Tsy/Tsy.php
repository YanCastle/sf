<?php
/**
 * Created by PhpStorm.
 * User: Castle
 * Date: 2016/4/11
 * Time: 22:20
 */
ignore_user_abort(true);
defined('APP_MODE') or define('APP_MODE','Http' );
if(defined('CURL')&&defined('APP_DEBUG')&&APP_DEBUG&&CURL){
    $cmd = explode(' ',str_replace("\"",'',CURL));
    $url = $cmd[1];
    $query  = parse_url(trim($url,'\'"'),PHP_URL_QUERY);
    parse_str($query,$GET);
    $_GET = array_merge($_GET,$GET);
    parse_str(trim($cmd[count($cmd)-2],'\'"'),$POST);
    $_POST = array_merge($_POST,$POST);
    unset($GET,$POST);
}
if(file_exists(dirname($APP_PATH).'/vendor/autoload.php')){
    include dirname($APP_PATH).'/vendor/autoload.php';
}
include_once __DIR__.'/Tsy.class.php';
$Tsy = new Tsy\Tsy();
$Tsy->start();