<?php
/**
 * Created by PhpStorm.
 * User: Castle
 * Date: 2016/4/11
 * Time: 22:56
 */

namespace Tsy\Mode;


use Tsy\Mode;

class Http implements Mode
{
    /**
     * 执行体
     * @return mixed
     */
    function exec(){}

    /**
     * 调度
     * @return mixed
     */
    function dispatch(){}

    /**
     * 启动函数
     * @return mixed
     */
    function start(){
//        if($_SERVER['HTTP_ORIGIN']){
            header("Access-Control-Allow-Origin: " . isset($_SERVER['HTTP_ORIGIN'])?$_SERVER['HTTP_ORIGIN']:'*');
            header("Access-Control-Allow-Credentials: true");
            header('Access-Control-Request-Method: GET,POST,OPTIONS');
            header('Access-Control-Allow-Headers: X-Requested-With,Cookie');
//        }
        $HttpDispatch = http_in_check();
        http_out_check(controller($HttpDispatch['i'],$HttpDispatch['d']));
    }
}