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
            [
                'HOST'=>'0.0.0.0',
                'PORT'=>'65502',
                'TYPE'=>'Http',
                'ALLOW_IP'=>[
                    '127.0.0.1',
                    ['10.10.13.1','10.10.13.2']
                ],
//                'DENY_IP'=>[
//                    '127.0.0.1'
//                ],
                'DISPATCH'=>function($data){
                    return[
                        'i'=>'Application/Index/index',
                        'd'=>''
                    ];
                },
                'OUT'=>'json_encode'
            ],
        ],
        //SWOOLE 配置
        'CONF'=>[
            'daemonize' => 0, //自动进入守护进程
            'task_worker_num' => 1,//开启task功能，
            'dispatch_mode '=>3,//轮询模式
            'worker_num'=>2,
            'open_eof_check'=>true,
        ],
        //定时器配置
        'TIMER'=>[

        ],
        
    ]
];