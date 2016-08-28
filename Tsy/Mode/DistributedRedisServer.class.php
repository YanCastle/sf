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
    static $Clients=[];//All the Redis channels can publish
    function __construct()
    {
        self::$Redis = new \swoole_redis();
        self::$Redis->on('message',[$this,'onMessage']);
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

        self::$Redis->connect('127.0.0.1',6379,[$this,'onConnect']);

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

    /**
     * Message Received
     * @param \swoole_redis $redis
     * @param $result
     */
    function onMessage(\swoole_redis $redis,$result){
//        var_dump($result);
        $data = json_decode($result[2],true);
        switch ($result[1]){
            case 'Distribute.Manage':
                self::$Clients[]=$data['Channel'];
                break;
            case 'Distribute.Receive':
                var_dump($data);
                break;
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
        $redis->subscribe('Distribute.Manage');
        $redis->subscribe('Distribute.Receive');
        $redis=new \Redis();
        $redis->connect('127.0.0.1',6379);
        $Inotify = new \Inotify();
        $Inotify->watch('d');
        $Inotify->start(function($path,$msk)use($redis){
            foreach (self::$Clients as $Channel){
                $redis->publish('ss',json_encode(['i'=>'Application/Index/check','d'=>['path'=>$path]]));
            }
        });
    }
}