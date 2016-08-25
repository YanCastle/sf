<?php
/**
 * Created by PhpStorm.
 * User: Castle
 * Date: 2016/8/25
 * Time: 22:05
 */

namespace Tsy\Mode;


use Tsy\Mode;

class DistributedRedisClient implements Mode
{

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
        // TODO: Implement dispatch() method.
    }

    /**
     * 启动函数
     * @return mixed
     */
    function start()
    {
        // TODO: Implement start() method.
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