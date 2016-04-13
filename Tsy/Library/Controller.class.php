<?php
/**
 * Created by PhpStorm.
 * User: Castle
 * Date: 2016/4/11
 * Time: 23:11
 */

namespace Tsy\Library;


class Controller
{
    protected $swoole;
    function __construct()
    {
    }
    function set_swoole($swoole){
        $this->swoole=$swoole;
    }
    protected function send($UID,$data){
        //TODO 需要建立UID跟fd的连接信息，如果不是在swoole模式下还需要放到队列中去
    }
}