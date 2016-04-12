<?php
/**
 * Created by PhpStorm.
 * User: Castle
 * Date: 2016/4/12
 * Time: 12:08
 */

namespace Tsy\Library;


class Server
{

    /**
     * 接收处理
     * @return int
     */
    function onReceive(){

    }

    /**
     * 断开连接的触发
     */
    function onClose(){

    }

    /**
     * 连接处理
     */
    function onConnect(){

    }


    function onTask(){}
    function onFinish(){}

    /**
     * UDP回调
     */
    function onPacket(\swoole_server $server,$data,array $client_info){}
    /**
     * 在主线程回调
     * @param \swoole_server $server
     */
    function onStart(\swoole_server $server){}
    /**
     * Server结束时
     * 已关闭所有线程
    已关闭所有worker进程
    已close所有TCP/UDP监听端口
    已关闭主Rector
     * @param \swoole_server $server
     */
    function onShutdown(\swoole_server $server){

    }

    /**
     * 此事件在worker进程/task进程启动时发生。这里创建的对象可以在进程生命周期内使用。
     * @param \swoole_server $server
     * @param $worker_id
     */
    function onWorkerStart(\swoole_server $server, $worker_id){}

    /**
     * 此事件在worker进程终止时发生。在此函数中可以回收worker进程申请的各类资源。
     * @param \swoole_server $server
     * @param $worker_id
     */
    function onWorkerStop(\swoole_server $server,$worker_id){}

    /**
     * 定时器触发
     * @param \swoole_server $server
     * @param $interval
     */
    function onTimer(\swoole_server $server,$interval){}

    /**
     * 当工作进程收到由sendMessage发送的管道消息时会触发onPipeMessage事件。
     * worker/task进程都可能会触发onPipeMessage事件。
     * @param \swoole_server $server
     * @param $from_worker_id
     * @param $message
     */
    function onPipeMessage(\swoole_server $server,$from_worker_id,$message){}

    /**
     * 当worker/task_worker进程发生异常后会在Manager进程内回调此函数。
     * @param \swoole_server $server
     * @param $worker_id
     * @param $worker_pid
     * @param $exit_code
     */
    function onWorkerError(\swoole_server $server,$worker_id,$worker_pid,$exit_code){}

    /**
     * 当管理进程启动时调用它
     * @param \swoole_server $server
     */
    function onManagerStart(\swoole_server $server){}

    /**
     * 当管理进程结束时调用它
     * @param \swoole_server $server
     */
    function onManagerStop(\swoole_server $server){}
}