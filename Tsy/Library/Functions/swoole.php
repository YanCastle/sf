<?php
/**
 * Created by PhpStorm.
 * User: Castle
 * Date: 2016/4/27
 * Time: 20:59
 */
/**
 * 异步任务投递功能完成
 * @param $data
 * @param bool $wait
 * @return mixed
 */
function task($data,$wait=false){
    if($wait){
        return $GLOBALS['_SWOOLE']->taskwait($data,is_numeric($wait)?$wait:60);
    }else{
        $GLOBALS['_SWOOLE']->task($data);
    }
}
function task_controller(){}

/**
 * 异步任务
 * @param callable $callback
 * @param array $params
 */
function async($config,array $params=[]){}

/**
 * 进程间通信的给指定进程发送消息
 * @param $to
 * @param $message
 */
function pipe_message($to,$message){
    if(is_numeric($to)){
        //按目标worker id 发送，在此处检测是否是用户自定义进程，如果是则调用process的write方法，否则调用swoole_server的sendmessage方法
        if($to>=$GLOBALS['_TASK_WORKER_SUM']){
            //调用process的write方法
            $GLOBALS['_PROCESS'][$to][0]->write($message);
        }else{
            $GLOBALS['_SWOOLE']->sendMessage($message,$to);
        }
    }elseif (is_array($to)){
//        解析指令
//        switch ()
    }
}

function swoole_get_process_type($id=null){
    if($id==null){
        $id=$GLOBALS['_SWOOLE']->worker_id;
    }
    //TODO 返回id是什么类型的进程
}

/**
 * 创建或获取SwooleClient
 * @param string $ip
 * @param int $port
 * @param callable $receive
 * @param callable $new
 * @param callable $connect
 * @param callable $close
 * @param callable $error
 * @return mixed
 */
function swoole_get_client($ip,$port,$receive,$new=false,$connect=null,$close=null,$error=null){
    static $clients=[];
    $host = $ip.$port;
    if(isset($clients[$host])&&count($clients[$host])&&false==$new){
        return $clients[$host][0];
    }
    $receiveCallback = function(\swoole_client $client,$data)use($clients,$receive){
        if(is_callable($receive)){
            call_user_func_array($receive,[$client,$data]);
        }
    };
    $closeCallback=function(\swoole_client $client)use($close){
        if(is_callable($close)){
            call_user_func($close,[$client]);
        }
    };
    $errorCallback=function(\swoole_client $client)use($error){
        if(is_callable($error)){
            call_user_func($error,[$client]);
        }
    };
    $connectCallback = function(\swoole_client $client)use($connect){
        if(is_callable($connect)){
            call_user_func($connect,[$client]);
        }
    };
    $client = new swoole_client(SWOOLE_SOCK_TCP,SWOOLE_SOCK_ASYNC);
    $client->on('receive',$receiveCallback);
    $client->on('error',$errorCallback);
    $client->on('connect',$connectCallback);
    $client->on('close',$closeCallback);
    $client->connect($ip,$port);
    if(!isset($clients[$host])){
        $clients[$host]=[];
    }
    $clients[$host][]=$client;
    return $client;
}