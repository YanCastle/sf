<?php
/**
 * Created by PhpStorm.
 * User: castle
 * Date: 2016/4/18
 * Time: 8:05
 */

namespace Tsy\Library;


abstract class Object
{
    public $object=[

    ];
    public $map=[
//        自动生成
    ];//字段=》表名 映射
    function __construct()
    {
        if(!$this->table){
            $this->table = substr(__CLASS__,0,strlen(__CLASS__)-6);
        }
        //检测是否存在属性映射，如果存在则直接读取属性映射，没有则从数据库加载属性映射
    }

    function add(){
//        此处自动读取属性并判断是否是必填属性，如果是必填属性且无。。。则。。。
    }
    function get(){}
    function search(){}
    function del(){}
    function gets(){}
    function save(){}
}