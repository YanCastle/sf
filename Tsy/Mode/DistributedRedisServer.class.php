<?php
/**
 * Created by PhpStorm.
 * User: Castle
 * Date: 2016/8/25
 * Time: 22:05
 */

namespace Tsy\Mode;


use Tsy\Library\Cache\Driver\Redis;
use Tsy\Mode;

/**
 * 分布式调度程序，接受协议处理
 * Class DistributedRedisServer
 * @package Tsy\Mode
 */
class DistributedRedisServer implements Mode
{

    const RETURN_SUBSCRIBE_CHANNEL='RSC';//One Node Want to send some message to Client
    const NODE_SUBSCRIBE_CHANNEL='NSC';//While Node On Line

    static $Redis;
    static $SwooleRedis;
    static $Clients=[];//All the Redis channels can publish

    static $Config=[];
    function __construct()
    {
//        self::$SwooleRedis = new \swoole_redis();
//        self::$SwooleRedis->on('message',[$this,'onMessage']);
        self::$Config = C('DRS');
        self::$Redis = new \Redis();
    }

    /**
     * 执行体
     * @return mixed
     */
    function exec()
    {
        // TODO: Implement exec() method.
    }

    /**
     * 调度
     * @return mixed
     */
    function dispatch($data = null)
    {
        
//        解析从Redis订阅接受到的数据，并执行
    }

    /**
     * 启动函数
     * @return mixed
     */
    function start()
    {
//        1、连接到Redis服务器，
//
//        2、订阅指定频道，将消息接受绑定到dispatch函数中
//        3、检测Swoole配置，启动Swoole服务
        foreach (self::$Config as $Key=>$Value){
            switch ($Key){
                case 'REDIS':
                    if(!(isset($Value['HOST'])&&ip2long($Value)&&isset($Value['PORT'])&&is_numeric($Value['PORT']))){
                        exit('错误的Redis配置');
                    }
//                    $Value['Auth'] = isset($Value['Auth'])
                    break;
                case 'SUBSCRIBE':
                    if(!(isset($Value[self::NODE_SUBSCRIBE_CHANNEL])&&isset($Value[self::RETURN_SUBSCRIBE_CHANNEL]))){
                        exit('错误的订阅配置');
                    }
                    break;
                case 'PUBLISH':

                    break;
            }
        }
        $host=self::$Config['REDIS']['HOST'];
        $port=self::$Config['REDIS']['PORT'];
//        self::$SwooleRedis->connect($host,$port,[$this,'onConnect']);
        self::$Redis->connect($host,$port);
        self::$Redis->subscribe([self::$Config['SUBSCRIBE'][self::RETURN_SUBSCRIBE_CHANNEL],self::$Config['SUBSCRIBE'][self::NODE_SUBSCRIBE_CHANNEL]],[$this,'onRedisSubscribe']);
    }

    /**
     * 停止继续执行
     * @return mixed
     */
    function stop($Code = "0")
    {
        // TODO: Implement stop() method.
    }

    function out($Data = null)
    {
        // TODO: Implement out() method.
    }

    function in($Data = null)
    {
        // TODO: Implement in() method.
    }
    function onRedisSubscribe(\Redis $redis,string $channel,string $msg){

    }
    /**
     * Message Received
     * @param \swoole_redis $redis
     * @param $result
     */
    function onMessage(\swoole_redis $redis,$result){
        if(strlen($result)>0){
            $data = json_decode($result[2],true);
            switch ($result[1]){
                case self::$Config['SUBSCRIBE'][self::NODE_SUBSCRIBE_CHANNEL]:
                    self::$Clients[]=$data['Channel'];
                    break;
                case self::$Config['SUBSCRIBE'][self::RETURN_SUBSCRIBE_CHANNEL]:
                    var_dump($data);
                    break;
            }
        }

    }

    /**
     * Success
     * @param \swoole_redis $redis
     * @param bool $result
     */
    function onConnect(\swoole_redis $redis,bool $result){
        //Read subscribe Config ,and subscribe the channel
//        $redis->subscribe('castle');
        //subscribe 2 channels
        $redis->subscribe(self::$Config['SUBSCRIBE'][self::RETURN_SUBSCRIBE_CHANNEL]);
        $redis->subscribe(self::$Config['SUBSCRIBE'][self::NODE_SUBSCRIBE_CHANNEL]);
//        $redis=new \Redis();
//        $redis->connect('127.0.0.1',6379);
        $Inotify = new \Inotify();
        $Inotify->watch('d');
        $Inotify->start(function($path,$msk)use($redis){
            foreach (self::$Clients as $Channel){
                self::$Redis->publish('ss',json_encode(['i'=>'Application/Index/check','d'=>['path'=>$path]]));
            }
        });
    }
}