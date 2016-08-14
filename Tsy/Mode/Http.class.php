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
    function dispatch($data=[]){
        $i = I(C('HTTP.I'));
        return [
            'i'=>$i?$i:'Empty/_empty',
            'd'=>I(C('HTTP.D'))
        ];
    }

    /**
     * 启动函数
     * @return mixed
     */
    function start(){
        if(isset($_SERVER['HTTP_ORIGIN'])){
//            $URI = parse_url($_SERVER['HTTP_ORIGIN']);
//            $URL = $URI['host'];if($URI['port']!=80){$URL.=":{$URI['port']}";}
            header("Access-Control-Allow-Origin: " .$_SERVER['HTTP_ORIGIN']);
//            header("Access-Control-Allow-Origin: *");
            header("Access-Control-Allow-Credentials: true");
            header('Access-Control-Request-Method: GET,POST,PUT,DELETE');
            header('Access-Control-Allow-Headers: X-Requested-With,Cookie,Content-Type');
        }
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