<?php
/**
 * Created by PhpStorm.
 * User: Castle
 * Date: 2016/4/12
 * Time: 11:36
 */

namespace Tsy\Library\Swoole;


use Tsy\Library\Swoole;

class Http extends Swoole
{
    function code($str){

//GET / HTTP/1.1
//Host: 127.0.0.1:60000
//Connection: keep-alive
//Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8
//Upgrade-Insecure-Requests: 1
//User-Agent: Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/49.0.2623.112 Safari/537.36
//DNT: 1
//Accept-Encoding: gzip, deflate, sdch
//Accept-Language: zh-CN,zh;q=0.8

//        A=1212&f=232
        $_SERVER = array_merge($_SERVER,[
            'REQUEST_TIME'=>time(),

        ]);
    }
    function uncode($str)
    {
        $HTTP = http_parse($str);
        if(is_array($HTTP)){
            if(isset($HTTP['Header'])){

            }
            if(isset($HTTP['Method'])){

            }
            if(isset($HTTP['POST'])){
                $_POST = array_merge($_POST,$HTTP['POST']);
            }
            if(isset($HTTP['GET'])){
                $_GET = array_merge($_GET,$HTTP['GET']);
            }
            if(isset($HTTP['FILES'])){
                $_FILES = array_merge($_FILES,$HTTP['FILES']);
            }
            if(isset($HTTP['SERVER'])){
                $_SERVER=array_merge($_SERVER,$HTTP['SERVER']);
            }
            $_REQUEST = array_merge($_GET,$_POST);
        }
    }
}