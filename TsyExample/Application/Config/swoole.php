<?php
/**
 * Created by PhpStorm.
 * User: Castle
 * Date: 2016/4/11
 * Time: 22:52
 */
return [
    'SWOOLE'=>[
        //监听配置
        'LISTEN'=>[
            '类型'=>[
                'HOST'=>'',
                'PORT'=>'',

            ],
        ],
        //SWOOLE 配置
        'CONF'=>[
            'daemonize' => 0, //自动进入守护进程
            'log_file' => 'swoole.log',
            'task_worker_num' => 1,//开启task功能，
            'dispatch_mode '=>3,//轮询模式
            'worker_num'=>2,
            'open_eof_check'=>true,
        ]
    ]
];