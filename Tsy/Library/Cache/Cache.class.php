<?php
/**
 * Created by PhpStorm.
 * User: Castle
 * Date: 2016/4/14
 * Time: 10:57
 */

namespace Tsy\Library\Cache;


abstract class Cache
{
    public $handler;
    public $options=[];
    //读取缓存
    public function get($name){}
    //写入缓存
    public function set($name, $value, $expire = null) {}
    //删除缓存
    public function rm($name){}
    //清除缓存
    public function clear(){}
}