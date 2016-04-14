<?php
/**
 * Created by PhpStorm.
 * User: Castle
 * Date: 2016/4/11
 * Time: 22:19
 */
return [
    'DATA_CACHE_TYPE'=>'Redis',//默认缓存工具类型
    'REDIS_HOST'=>'10.10.13.182',//redis主机ip
    'REDIS_PORT'=>6379,//端口
    'DATA_CACHE_TIMEOUT'=>false,//过期时间
    'DATA_CACHE_TIME'=>0,//缓存有效期，0是永久
    'DATA_CACHE_PREFIX'=>''//前缀
];