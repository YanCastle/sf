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
                if(isset($Config['HOST'])&&isset($Config['PORT'])){
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
                $Swoole = new \Tsy\Library\Swoole();
                $Server->on('receive',[$Swoole,'onReceive']);
                $Server->on('connect',[$Swoole,'onConnect']);
                $Server->on('close',[$Swoole,'onClose']);
                $GLOBALS['_SWOOLE']=$Server;
            }else{
                die('SWOOLE创建失败');
            }
        }else{
            die('SWOOLE配置不存在或不正确，请正确配置SWOOLE下面的信息');
        }
    }
}