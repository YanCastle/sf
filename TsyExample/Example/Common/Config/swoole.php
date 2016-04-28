<?php
/**
 * Created by PhpStorm.
 * User: Castle
 * Date: 2016/4/11
 * Time: 22:52
 */
return [
    'SWOOLE'=>[
        'AUTO_RELOAD'=>true,
        //监听配置
        'LISTEN'=>[
            [
                'HOST'=>'0.0.0.0',
                'PORT'=>'65400',
                'TYPE'=>'SOCKET',
//                'ALLOW_IP'=>[
//                    '127.0.0.1',
//                    ['10.10.13.1','10.10.13.2']
//                ],
//                'DENY_IP'=>[
//                    '127.0.0.1'
//                ],
                'DISPATCH'=>function($data){
                    return[
                        'i'=>'Application/Index/sleep',
                        'd'=>''
                    ];
                },
                'OUT'=>function($d){
                    return $d;
                }
            ],
            [
                'HOST'=>'0.0.0.0',
                'PORT'=>'65401',
                'TYPE'=>'SOCKET',
//                'ALLOW_IP'=>[
//                    '127.0.0.1',
//                    ['10.10.13.1','10.10.13.2']
//                ],
//                'DENY_IP'=>[
//                    '127.0.0.1'
//                ],
                'DISPATCH'=>function($data){
                    return[
                        'i'=>'Application/Index/check',
                        'd'=>''
                    ];
                },
                'OUT'=>function($d){
                    return $d;
                }
            ],
        ],
        //SWOOLE 配置
        'CONF'=>[
            'daemonize' => 0, //自动进入守护进程
            'task_worker_num' => 1,//开启task功能，
            'dispatch_mode '=>3,//轮询模式
            'worker_num'=>4,
        ],
        //定时器配置
        'TIMER'=>[

        ],
        
    ]
];