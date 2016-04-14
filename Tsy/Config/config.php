<?php
/**
 * Created by PhpStorm.
 * User: Castle
 * Date: 2016/4/11
 * Time: 22:19
 */
return [
//    CLI模式下fd分组的配置，仅用于SwooleServer的情况下
    'CLI_FD_GROUP'=>'cli_fd_group',
    'DATA_CACHE_TYPE'=>'File',//默认缓存工具类型
    'DATA_CACHE_TIMEOUT'=>false,//过期时间
    'DATA_CACHE_TIME'=>0,//缓存有效期，0是永久
    'DATA_CACHE_PREFIX'=>''//前缀
];