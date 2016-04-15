<?php
/**
 * Created by PhpStorm.
 * User: Castle
 * Date: 2016/4/12
 * Time: 12:08
 */

namespace Tsy\Library;

/**
 * Server类，用来处理SwooleServer的各种回调
 * @package Tsy\Library
 */
class Server
{
    protected $_swoole=[];
    protected $first=[];
    protected $port_mode_map=[];
    function __construct($modes=[])
    {
        $this->port_mode_map=$modes;
        $this->init();
    }

    /**
     * Server启动时的初始化方法
     */
    function init(){

    }
    /**
     * 收到消息
     * @param \swoole_server $server
     * @param $fd
     * @param $from_id
     * @param $data
     */
    function onReceive(\swoole_server $server,$fd,$from_id,$data){
//        标记变量，是否是第一次接受请求
        $_POST['_fd']=$fd;
        $_GET['_fd']=$fd;
//        接受数据次数统计
        swoole_receive();
        $Data = swoole_in_check($fd,$data);
        if($Data){
            swoole_bridge_check($fd,$Data);
            if($return = controller($Data['i'],$Data['d'],$Data['m']))
                swoole_out_check($fd,$return);
        }
        session('[id]',null);//删除session_id标识
    }

    /**
     * 连接断开
     * @param \swoole_server $server
     * @param $fd
     * @param $from_id
     */
    function onClose(\swoole_server $server,$fd,$from_id){
        unset($this->first[$fd]);
        $_GET['_fd']=$fd;
        L("连接断开：{$fd}");
        fd_name(null);
        swoole_receive(null);
        port_group(swoole_connect_info($fd)['server_port'],null);
    }

    /**
     * 连接建立
     * @param \swoole_server $server
     * @param $fd
     * @param $from_id
     */
    function onConnect(\swoole_server $server,$fd,$from_id){
        $_GET['_fd']=$fd;
//        TODO 检测该链接是否在允许的IP范围内或者是否在禁止的IP范围内
        L("新连接：{$fd}");
        fd_name($fd);
        port_group(swoole_connect_info($fd)['server_port'],$fd);
    }

    /**
     * 异步任务触发回调
     * @param \swoole_server $server
     * @param $task_id
     * @param $from_id
     * @param $data
     */
    function onTask(\swoole_server $server,$task_id,$from_id,$data){}

    /**
     * 异步任务完成回调
     * @param \swoole_server $server
     * @param $task_id
     * @param $data
     */
    function onFinish(\swoole_server $server,$task_id,$data){}

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
    function onWorkerStart(\swoole_server $server, $worker_id){
        $_GET['_server']=$server;
    }

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
    function onTimer(\swoole_server $server,$interval){
        $Timer = C('SWOOLE.TIMER');
        if(isset($Timer[$interval])&&is_callable($Timer[$interval])){
            call_user_func_array($Timer[$interval],[$server,$interval]);
        }
    }

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
    protected function getModeClass($mode){
        if(!isset($this->_swoole[$mode])){
            $class='Tsy\\Library\\Swoole\\'.$mode;
            $this->_swoole[$mode]=new $class();
        }
        return $this->_swoole[$mode];
    }
}