<?php
/**
 * Created by PhpStorm.
 * User: Castle
 * Date: 2016/4/11
 * Time: 22:56
 */

namespace Tsy\Mode;


use Tsy\Mode;
use Tsy\Tsy;

class Http implements Mode
{
    static $Out=false;
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
    function output($data){
        header('Content-Type:application/json; charset=utf-8');
        return str_replace(':null',':""',json_encode([
            'G'=>session('G'),
            'UN'=>session('UN'),
            'UID'=>session('UID'),
            'c'=>Tsy::$c,
            'd'=>is_string($data)?false:$data,
            'err'=>is_string($data)?$data:'',
            'tsy'=>session('[id]')
        ],JSON_UNESCAPED_UNICODE));
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
            header('Access-Control-Request-Method: *');
            header('Access-Control-Allow-Headers: *');
        }
        if(isset($_SERVER['REQUEST_METHOD'])&&$_SERVER['REQUEST_METHOD']=='OPTIONS'){
            exit();
        }
        if(isset($_COOKIE['tsy'])&&$_COOKIE['tsy']){
            session('[id]',$_COOKIE['tsy']);
        }elseif(isset($_GET['tsy'])&&$_GET['tsy']){
            session('[id]',$_GET['tsy']);
        }
        else{
            $session_id = uniqid();
            session('[id]',$session_id);
            setcookie('tsy',$session_id);
        }
        if(isset($_SERVER['CONTENT_TYPE'])){
            list($Type,$Charset)=explode('; ',$_SERVER['CONTENT_TYPE']);
            switch ($Type){
                case 'application/json':
                    $_POST = array_merge($_POST,json_decode(file_get_contents('php://input'),true));
                    $_REQUEST = array_merge($_GET,$_POST);
                    break;
            }
        }
        $HttpDispatch = http_in_check();
        $this->out(controller($HttpDispatch['i'],$HttpDispatch['d']));
    }
    function stop($Code=0)
    {
        exit();
    }
    function out($Data=null){
        if(Tsy::$Out){
            $Out = C('HTTP.OUT');
            $Out = is_callable($Out)?$Out:[$this,'output'];
            $OutData=call_user_func($Out,$Data);
            if(Tsy::$Out&&is_string($OutData)&&strlen($OutData)>0&&!($Data===null&&$OutData==='null')){
                self::$Out=true;
                echo $OutData;
            }
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
    function __destruct()
    {
        if(!self::$Out){
            $this->out();
        }
    }
}