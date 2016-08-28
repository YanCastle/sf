<?php
/**
 * Created by PhpStorm.
 * User: castle
 * Date: 8/28/16
 * Time: 3:37 PM
 */
return [
    'DistributedRedisServer'=>[
        'REDIS'=>[
            'HOST'=>'127.0.0.1',
            'PORT'=>'6379',
            'AUTH'=>''
        ],
        'SUBSCRIBE'=>[
            //All Subscribe Channel Config,This is Server
            [
                'Type'=>''
            ]
        ],
        'PUBLISH'=>[
            // Send The Notice To DistributeRedisClient
        ]
    ]
];