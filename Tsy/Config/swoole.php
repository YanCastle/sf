<?php
/**
 * Created by PhpStorm.
 * User: Castle
 * Date: 2016/4/12
 * Time: 11:46
 */
return [
    'CONF'=>[
        'daemonize' => 0, //自动进入守护进程
        'log_file' => 'swoole.log',
        'task_worker_num' => 1,//开启task功能，
        'dispatch_mode '=>3,//轮询模式
        'worker_num'=>2,
        'open_eof_check'=>true,
    ]
];