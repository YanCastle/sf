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
//            header("Access-Control-Allow-Origin: " . isset($_SERVER['HTTP_ORIGIN'])?$_SERVER['HTTP_ORIGIN']:'*');
            header("Access-Control-Allow-Origin: *");
            header("Access-Control-Allow-Credentials: false");
            header('Access-Control-Request-Method: GET,POST,PUT,DELETE');
            header('Access-Control-Allow-Headers: X-Requested-With,Cookie,Content-Type');
//        }
        if(isset($_SERVER['REQUEST_METHOD'])&&$_SERVER['REQUEST_METHOD']=='OPTIONS'){
            exit();
        }
        $HttpDispatch = http_in_check();
        http_out_check(controller($HttpDispatch['i'],$HttpDispatch['d']));
    }
    function stop($Code=0)
    {
        exit();
    }
    function out($Data=null){
        $Out = C('HTTP.OUT');
        $OutData=is_callable($Out)?call_user_func($Out,$Data):C('DEFAULT_OUT');
        if(is_string($OutData)&&strlen($OutData)>0){
            echo $OutData;
        }
    }
    function in($Data=null){
        //    调用HTTP模式的DISPATCH，然后调用Controller
        $Data=[
            'i'=>isset($_GET['i'])?'Empty/_empty':$_GET['i'],
            'd'=>$_POST?$_POST:[],
        ];
        $Dispatch = C('HTTP.DISPATCH');
        if(is_callable($Dispatch)){
            $tmpData = call_user_func($Dispatch);
            $Data = is_array($tmpData)?array_merge($Data,$tmpData):$Data;
        }
        return $Data;
    }
}