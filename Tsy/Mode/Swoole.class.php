<?php
/**
 * Created by PhpStorm.
 * User: Castle
 * Date: 2016/4/11
 * Time: 22:58
 */

namespace Tsy\Mode;


use Tsy\Mode;

/**
 * swoole模式
 * @package Tsy\Mode
 */
class Swoole implements Mode
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
//        读取配置文件、启动服务器
        $Listen = C('SWOOLE.LISTEN');
        $Conf = C('SWOOLE.CONF');
        if($Listen){
            foreach ($Listen as $Type=>$Config){
                if(isset($Config['HOST'])&&isset($Config['PORT'])&&is_numeric($Config['PORT'])&&$Config['PORT']>0&&$Config['PORT']<65536&&long2ip(ip2long($Config['HOST']))==$Config['HOST']){
                    if(isset($Server)){
                        //添加监听
                        $Server->addListener($Config['HOST'],$Config['PORT']);
                    }else{
                        //初次启动服务
                        $Server=new \swoole_server($Config['HOST'],$Config['PORT']);
                    }
                }else{
                    die('SWOOLE的Listen配置不正确，请确认配置是否正确.');
                }
            }
//            检测_WS是否创建成功，如果创建成功则继续
            if(isset($Server)){
                if($Conf){
                    $Server->set($Conf);
                }
                $Swoole = new \Tsy\Library\Server();
                $Server->on('receive',[$Swoole,'onReceive']);
                $Server->on('connect',[$Swoole,'onConnect']);
                $Server->on('close',[$Swoole,'onClose']);
                $Server->on('start',[$Swoole,'onStart']);
                $Server->on('shutdown',[$Swoole,'onShutdown']);
                $Server->on('WorkerStop',[$Swoole,'onWorkerStop']);
                $Server->on('WorkerStart',[$Swoole,'onWorkerStart']);
                $Server->on('timer',[$Swoole,'onTimer']);
                $Server->on('packet',[$Swoole,'onPacket']);
                $Server->on('task',[$Swoole,'onTask']);
                $Server->on('finish',[$Swoole,'onFinish']);
                $Server->on('PipeMessage',[$Swoole,'onPipeMessage']);
                $Server->on('WorkerError',[$Swoole,'onWorkerError']);
                $Server->on('ManagerStart',[$Swoole,'onManagerStart']);
                $Server->on('ManagerStop',[$Swoole,'onManagerStop']);
                $GLOBALS['_SWOOLE']=&$Server;
                $Server->start();
            }else{
                die('SWOOLE创建失败');
            }
        }else{
            die('SWOOLE配置不存在或不正确，请正确配置SWOOLE下面的信息');
        }
    }
}