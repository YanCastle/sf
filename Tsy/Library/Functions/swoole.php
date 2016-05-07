<?php
/**
 * Created by PhpStorm.
 * User: Castle
 * Date: 2016/4/27
 * Time: 20:59
 */
/**
 * 任务投递
 */
function task(){}
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
    }else{
//        解析指令
        
    }
}