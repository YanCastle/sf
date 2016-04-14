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
        $IsFirst=false;
        $Port = $server->connection_info($fd)['server_port'];
        if(!isset($this->first[$fd])){
            $IsFirst=is_first_receive($fd);
        }
        $mode = $this->port_mode_map[$Port][0];
        $Class = $this->getModeClass($mode);
        if($IsFirst){
            $this->first[$fd]=1;
            if($HandData = $Class->handshake($data)){
                //响应握手协议
                $server->send($fd,$HandData);
                return ;
            }
        }
        //            解码协议，
        $data = $Class->uncode($data);
        $Data=[
            'i'=>'Empty/_empty',
            'd'=>$data,
            't'=>''
        ];
//            实例化Controller
        if(is_callable($this->port_mode_map[$Port][1])){
            $tmpData = call_user_func($this->port_mode_map[$Port][1],$data);
            $Data = is_array($tmpData)?array_merge($Data,$tmpData):$Data;
        }
        //-----------------------------------------
        //开始进行t值检测，做桥链接处理
        //        生成mid
        $_POST['_mid']=uniqid();
        $Data['m']=$_POST['_mid'];
        if($Data['t']){
//            链接桥响应,此处要应用通道编码,通道编码之前要有协议编码
            $SendData = [
                't'=>$Data['t'],
                'm'=>$Data['m']
            ];
//            响应桥请求
            $server->send($fd,$Class->code(is_callable($this->port_mode_map[$Port][2])?call_user_func($this->port_mode_map[$Port][2],$SendData):\json_encode($SendData,true)));
        }
//            响应检测
        $_POST['_i']=$Data['i'];
        $return = controller($Data['i'],$Data['d'],$Data['m']);
        //返回内容检测
        $sendStr = call_user_func($this->port_mode_map[$Port][2],$return);
        if(is_string($sendStr)&&strlen($sendStr)>0){
            $server->send($fd,$Class->code($sendStr));
        }
    }

    /**
     * 连接断开
     * @param \swoole_server $server
     * @param $fd
     * @param $from_id
     */
    function onClose(\swoole_server $server,$fd,$from_id){
        unset($this->first[$fd]);
    }

    /**
     * 连接建立
     * @param \swoole_server $server
     * @param $fd
     * @param $from_id
     */
    function onConnect(\swoole_server $server,$fd,$from_id){

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
    protected function getModeClass($mode){
        if(!isset($this->_swoole[$mode])){
            $class='Tsy\\Library\\Swoole\\'.$mode;
            $this->_swoole[$mode]=new $class();
        }
        return $this->_swoole[$mode];
    }
}