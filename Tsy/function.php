<?php
/**
 * Created by PhpStorm.
 * User: Castle
 * Date: 2016/4/11
 * Time: 22:20
 */
function is_first_receive($fd){
    return true;
}

function session($name,$value=false){

}


/**
 * 缓存
 * @param $key
 * @param bool $value
 * @param bool $expire
 */
function cache($key,$value=false,$expire=false,$type='Default'){

}

/**
 * 队列读写
 * @param $key
 * @param bool $value
 * @param int $order 1表示先进先出 0 先进后出
 */
function queue($key,$value=false,$order=1){

}

/**
 * 任务投递
 */
function task(){}

/**
 * 异步任务
 * @param callable $callback
 * @param array $params
 */
function async($config,array $params=[]){}


