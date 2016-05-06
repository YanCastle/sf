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
        'PROCESS'=>[
            [
                'NAME'=>'Router',
                'NUMBER'=>1,//进程数量
                'CALLBACK'=>function(\swoole_process $process){
                    while(true){
                        $process->write('ss');
                        sleep(1);
                    }
                },
                'REDIRECT_STDIN_STDOUT'=>false,//开启时echo不会输出到屏幕而是进入到可读队列
                'PIPE'=>function(\swoole_process $process,$data){
                    echo $data;
                }
            ],
        ],
        'CALLBACK'=>[
            'PIPE_MESSAGE'=>function(\swoole_server $server,$from_worker_id,$data){
                file_put_contents('PIPE_MAIN',$data);
            }
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