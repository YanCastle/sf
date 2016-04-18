<?php
/**
 * Created by PhpStorm.
 * User: Castle
 * Date: 2016/4/12
 * Time: 11:46
 */
return [
    'SWOOLE'=>[
        'CONF'=>[
            'daemonize' => !APP_DEBUG, //自动进入守护进程
            'task_worker_num' => 5,//开启task功能，
            'dispatch_mode '=>3,//轮询模式
            'worker_num'=>5,
        ]
    ]
];