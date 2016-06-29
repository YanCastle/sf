<?php

/**
 * Created by PhpStorm.
 * User: castle
 * Date: 2016/6/29
 * Time: 16:17
 */
class Document
{
    static protected $docs=[
        'classes'=>[],
        'functions'=>[],
    ];

    /**
     * 获取文档信息
     * 这儿是描述信息
     * @login true 需要登录
     * @param $Class
     * @author castle<castle@tansuyun.cn>
     * @return bool
     * @link http://www.baidu.com?
     *
     */
    function getDoc($Class){
        self::$docs=[
            'classes'=>[
                '完整类名'=>[
                    'memo'=>'类说明',
                    'type'=>'',//这个类是什么类型，控制器？Model？Object？其他？
                    'properties'=>[
                        '属性名称'=>[
                            'name'=>'属性名称',
                            'zh'=>'中文名称',
                            'access'=>'public/protected/private',//三选一
                            'memo'=>'属性备注',
                            'type'=>'属性类型'
                        ]
                    ],
                    'methods'=>[
                        '方法名称'=>[
                            'params'=>[ //参数列表
                                '参数名称'=>[
                                    'name'=>'参数名称',
                                    'zh'=>'中文名称',
                                    'memo'=>'参数备注',
                                    'type'=>'参数类型',
                                    'default'=>'参数默认值'
                                ]
                            ],
                            'login'=>true,//是否需要登录
                            'name'=>'方法名称',
                            'zh'=>'方法中文名',
                            'access'=>'访问性',
                            'memo'=>'注释',
                            'author'=>'作者信息',
                            'link'=>'帮助信息链接地址',
                            'return'=>[//返回类型

                            ]
                        ]
                    ]
                ]
            ]
        ];
        if(is_string($Class)){
            if(class_exists($Class)){

            }elseif(function_exists($Class)){

            }elseif(is_dir($Class)){
//                each_dir() 遍历循环
            }elseif(is_file($Class)){

            }else{
                return false;
            }
        }
        return false;
    }

    /**
     * @login true
     *
     */
    function renderMD(){
        
    }
    function renderHTML(){}
    function renderDOC(){}
    function renderUML(){}
    function renderXLS(){}
    function renderCSV(){}
}