<?php
/**
 * Created by PhpStorm.
 * User: Castle
 * Date: 2016/8/25
 * Time: 22:05
 */

namespace Tsy\Mode;


use Tsy\Mode;

/**
 * 分布式调度程序，接受协议处理
 * Class DistributedRedisServer
 * @package Tsy\Mode
 */
class DistributedRedisServer implements Mode
{
    static $Redis;
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
}