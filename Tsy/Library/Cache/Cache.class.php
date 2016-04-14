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
    public function connect($type='',$options=array()) {
        if(empty($type))  $type = C('DATA_CACHE_TYPE');
        $class  =   strpos($type,'\\')? $type : 'Think\\Cache\\Driver\\'.ucwords(strtolower($type));
        if(class_exists($class))
            $cache = new $class($options);
        else
            E(L('_CACHE_TYPE_INVALID_').':'.$type);
        return $cache;
    }
    //读取缓存
    public function get($name){}
    //写入缓存
    public function set($name, $value, $expire = null) {}
    //删除缓存
    public function rm($name){}
    //清除缓存
    public function clear(){}
}