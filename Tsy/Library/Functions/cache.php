<?php
/**
 * Created by PhpStorm.
 * User: Castle
 * Date: 2016/4/13
 * Time: 21:37
 */
function S($key,$value=false,$expire=false,$type='Default'){
    return cache($key,$value,$expire,$type);
}

/**
 * 缓存
 * @param $key
 * @param bool $value
 * @param bool $expire
 */
function cache($key,$value=false,$expire=false,$type='Default'){
    if(empty($type))  $type = C('DATA_CACHE_TYPE');
    $class  =   strpos($type,'\\')? $type : 'Tsy\\Library\\Cache\\Driver\\'.ucwords(strtolower($type));
    if(class_exists($class))
        $cache = new $class($class);
    else
//        E(L('_CACHE_TYPE_INVALID_').':'.$type);
        return false;
    return $cache;
}

/**
 * 队列读写
 * @param $key
 * @param bool $value
 * @param int $order 1表示先进先出 0 先进后出
 */
function queue($key,$value=false,$order=1){

}