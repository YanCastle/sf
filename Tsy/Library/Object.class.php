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
    public $table='';//定义主表名称
    public $link=[];//多对多关联表
    public $property=[

    ];
    public $map=[
//        自动生成
    ];//字段=》表名 映射
    function add(){
//        此处自动读取属性并判断是否是必填属性，如果是必填属性且无。。。则。。。
    }
    function get(){}
    function search(){}
    function del(){}
    function gets(){}
    function save(){}
}