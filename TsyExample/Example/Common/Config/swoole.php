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
            'Socket'=>[
                'HOST'=>'0.0.0.0',
                'PORT'=>'65502',
//                'TYPE'=>SWOOLE_SOCK_TCP 暂时只支持TCP连接
                'DISPATCH'=>function($data){
                    list($i,$d)=explode(',',$data);
                    $d = explode('&',$d);
                    $Data = [];
                    foreach ($d as $val){
                        list($k,$v) = explode('=',$val);
                        $Data[$k]=$v;
                    }
                    return [
                        'i'=>$i,//要被调用的类和方法
                        'd'=>$Data,//前端发送的消息参数体
                        't'=>'fwafaw',//这个是前端生成的消息唯一值
                    ];
                }
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
        ],
        //定时器配置
        'TIMER'=>[],
        
    ]
];