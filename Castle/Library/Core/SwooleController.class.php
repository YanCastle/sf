<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 1/14/16
 * Time: 4:34 PM
 */

namespace Core;


class SwooleController
{
    function Task(\swoole_server $server, int $task_id, int $from_id, string $data){}
    function Start(\swoole_server $server){}
    function Shutdown(\swoole_server $server){}
    function WorkerStart(\swoole_server $server,$worker_id){}
    function WorkerStop(\swoole_server $server,$worker_id){}
    function Timer(\swoole_server $server,$interval){}
    function Finish(\swoole_server $server, $task_id, $data){}
    function PipeMessage(\swoole_server $server, int $from_worker_id, string $message){}
    function ManagerStart(\swoole_server $server){}
    function ManagerStop(\swoole_server $server){}
    function WorkerError(\swoole_server $server){}
    function Connect(\swoole_server $server,int $fd,int $from_id){}
    function Close(\swoole_server $server,int $fd,int $from_id){}
    function Packet(\swoole_server $server,$data,$client_info){}
    /**
     * 收到的回调，存在则调，不存在则直接按照协议解析到Controller中，
     * @param \swoole_server $server
     * @param int $fd
     * @param int $form_id
     * @param string $data
     */
    function Receive(\swoole_server $server,int $fd,int $form_id,string $data){
        $str = recv_buffer($fd,$data);
        if($str){
            //进入缓存队列
            $data=$str;
        }else{
            return false;
        }
        $_GET['FD']=$fd;$_GET['INFO']=$server->connection_info($fd);$_GET['IP']=$_GET['INFO']['remote_ip'];$_GET['PORT']=$_GET['INFO']['server_port'];
        $_REQUEST['server']=$server;
        //解压json
        $data = json_decode($data,true);
        if(is_array($data))
            $_GET=array_merge($_GET,$data);
        isset($data['tsy'])&&$data['tsy']?session_id($data['tsy']):session_id(uniqid());
        session_start();
        if($data&&isset($data['i'])&&$data['t']){
            list($c,$a)=explode('/',$data['i']);
            $controller = controller($c);
            if($controller){
                //需要对参数进行顺序处理，还是需要用到反射
                $_POST=json_decode($data['d'],true);
                $rs = controller_exec($controller,$a,$_POST);
                if($rs){
                    swoole_send($fd,$rs);
                }
                //TODO 检测是否有异步操作请求
            }
        }else{
//            $server->send($fd,json_encode(['UID'=>0,'E'=>'错误的数据','d'=>false]));
        }
        session_destroy();
    }
}