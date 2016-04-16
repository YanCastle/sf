<?php
/**
 * Created by PhpStorm.
 * User: Castle
 * Date: 2016/4/11
 * Time: 22:52
 */
return [
    'HTTP'=>[
        'DISPATCH'=>function($data){
            return [
                'i'=>'User/login',
                'd'=>[
                    'UN'=>1,
                    'PWD'=>1
                ],
            ];
        },
        'OUT'=>function($data){
            return http_build_query($data);
        }
    ]
];