<?php
/**
 * Created by PhpStorm.
 * User: Castle
 * Date: 2016/4/11
 * Time: 22:58
 */

namespace Tsy\Mode;


use Tsy\Library\Aop;
use Tsy\Mode;
use Tsy\Tsy;

/**
 * swoole模式
 * @package Tsy\Mode
 */
class Swoole implements Mode
{
    public static $Swoole;
    /**
     * 执行体
     * @return mixed
     */
    function exec(){}

    /**
     * 调度
     * @return mixed
     */
    function dispatch($data=null){}

    /**
     * 启动函数
     * @return mixed
     */
    function start(){
//        读取配置文件、启动服务器
//        清楚缓存
//        Aop::exec(__METHOD__,Aop::$AOP_BEFORE);
        cache('[cleartmp]');
        if($SwooleConfig = swoole_load_config()){
            $Server=null;
            $Processes=[];
            $ProcessesConf=[];
            foreach ($SwooleConfig['LISTEN'] as $Listen){
                port_group($Listen[0],null);
                if($Server){
//                    $port = call_user_func_array([$Server,'addListener'],$Listen);
                    $port = $Server->listen($Listen[0],$Listen[1],$Listen[2]);
                    if(isset($Listen['ON']))
                        foreach ($Listen['ON'] as $method=>$func){
                            $port->on($method,$func);
                        }
                    if(isset($Listen['SET'])&&is_array($Listen['SET'])){
                        $port->set($Listen['SET']);
                    }
                }else{
                    switch (strtolower($Listen['TYPE'])){
                        case 'http':
                            $Server=new \swoole_http_server($Listen[0],$Listen[1]);
                            $Server->on('request',function(\swoole_http_request $request,\swoole_http_response $response){
//                                ob_start();
                                $_GET = $request->get;
                                $_POST=$request->post;
                                $_REQUEST=array_merge($_GET,$_POST);
                                $Data = swoole_in_check($response->fd,$_REQUEST);
                                $return = controller($Data['i'],$Data['d'],isset($Data['m'])?$Data['m']:'');
                                $data=swoole_out_check($response->fd,$return);
//                                $data = ob_get_clean();
                                if(Tsy::$Out){
                                    $data = '';//TODO Fix Http Date
                                }
//                                $response->end($data);
                            });
                            break;
                        case 'websocket':
                            $Server=new \swoole_websocket_server($Listen[0],$Listen[1]);
                            break;
                        case 'socket':
                            $Server=new \swoole_server($Listen[0],$Listen[1]);
                            break;
                        default:
                            L('Type Error');
                            break;
                    }

                }
            }
            if(null===$Server){
                die('创建Swool对象失败');
            }
//            开始创建共享table
            foreach ($SwooleConfig['TABLE'] as $table){

            }
            swoole_get_callback(C('SWOOLE.CALLBACK'));
            if(isset($Server)&&$Server){
                $Server->set($SwooleConfig['CONF']);
                $GLOBALS['_TASK_WORKER_SUM']=$Server->setting['worker_num']+$Server->setting['task_worker_num'];
                $Swoole = new \Tsy\Library\Server($SwooleConfig['PortModeMap']);
                $GLOBALS['_PortModeMap']=$SwooleConfig['PortModeMap'];
                if(!($Server instanceof \swoole_websocket_server))
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
                if($Server instanceof \swoole_websocket_server)
                    $Server->on('message',[$Swoole,'OnMessage']);
                $Server->on('PipeMessage',[$Swoole,'onPipeMessage']);
                $Server->on('WorkerError',[$Swoole,'onWorkerError']);
                $Server->on('ManagerStart',[$Swoole,'onManagerStart']);
                $Server->on('ManagerStop',[$Swoole,'onManagerStop']);
                $Processes=[];
                $GLOBALS['_SWOOLE']=&$Server;
//                $SwooleConfig = swoole_load_config();
                if($SwooleConfig['PROCESS']){
                    foreach ($SwooleConfig['PROCESS'] as $k=>$Process){
                        if(isset($Process['CALLBACK'])&&is_callable($Process['CALLBACK'])){
                            if(!isset($Process['NUMBER'])){
                                $Process['NUMBER']=1;
                            }
                            for($i=0;$i<$Process['NUMBER'];$i++){
                                $ProcessObject = new \swoole_process(function(\swoole_process $process)use($Process,$Server){
                                    //框架中套启动函数并启动用户定义函数
//                                    检测是否是需要实例化的类，如果是需要实例化的类则先实例化再传递到回调结构中
                                    if(isset($Process['PIPE'])&&is_callable($Process['PIPE'])){
//                                        加载用户定义的进程pipe回调函数
                                        swoole_event_add($process->pipe,function($pipe)use($process,$Server,$Process){
                                            $buffer = $process->read();
                                            if(strlen($buffer)==8192){
                                                static_keep('+receive',$buffer);
                                                return ;
                                            }else{
                                                $buffer .= static_keep('receive');
                                                static_keep('receive','');
                                            }
                                            pipe_message_dispatch($Server,$buffer,0,$process,$Process);
                                        });
                                    }
                                    call_user_func_array($Process['CALLBACK'],[$process,$Server]);
                                },isset($Process['REDIRECT_STDIN_STDOUT'])?$Process['REDIRECT_STDIN_STDOUT']:true,2);
                                $Processes[$GLOBALS['_TASK_WORKER_SUM']+$i]=[$ProcessObject,$Process];
                            }
                        }else{
                            die('SwooleProcess配置不正确');
                        }
                    }
                }
                $GLOBALS['_PROCESS'] = &$Processes;
                foreach ($Processes as $process){
                    if($Server->addProcess($process[0])){
                        L('线程创建成功');
                    }else{
                        echo swoole_strerror(swoole_errno());
                    }
                }
                $GLOBALS['_SWOOLE']=&$Server;
                self::$Swoole=$Server;
                L('启动Swoole');
                fd_name([]);
                $Server->start();
            }else{
                die('SWOOLE创建失败');
            }
        }else{
            die('SWOOLE配置不存在或不正确，请正确配置SWOOLE下面的信息');
        }
    }
    function stop($Code=0)
    {
        self::$Swoole->stop();
    }
    function out($Data=null){

    }
    function in($Data=null){

    }
}