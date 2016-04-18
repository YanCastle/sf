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
    const PROPERTY_ONE='ONE';
    const PROPERTY_ARRAY='ARRAY';

    const RELATION_TABLE_NAME='TBN';
    const RELATION_TABLE_COLUMN='TBC';
    const RELATION_TABLE_PROPERTY='TBP';
    const RELATION_TABLE_LINK_HAS_PROPERTY='TBLHP';
    const RELATION_TABLE_LINK_TABLES='TBLT';

    protected $main='';
    protected $pk='';
    protected $link=[];
    protected $property=[];
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
    function __set($name, $value)
    {
        // TODO: Implement __set() method.
        $this->$name=$value;
    }
    function __get($name)
    {
        // TODO: Implement __get() method.
        return $this->$name;
    }

    function add(){
//        此处自动读取属性并判断是否是必填属性，如果是必填属性且无。。。则。。。
    }
    function get($ID){

    }
    function search(){}
    function del(){}
    function gets($IDs){
        if(is_numeric($IDs)&&$IDs>0){
            $IDs=[$IDs];
        }
        if(!$this->main||!$this->pk||!$IDs||!is_array($IDs)||count($IDs)<1){
            return false;
        }
        $Objects = [];
        $Model = M($this->main);
        $UpperMainTable = strtoupper($this->main);
        $ArrayProperties=[];
        foreach ($this->property as $PropertyName=>$Config){
            if(isset($Config[self::RELATION_TABLE_PROPERTY])&&isset($Config[self::RELATION_TABLE_NAME])&&isset($Config[self::RELATION_TABLE_COLUMN]))
            {
                if($Config[self::RELATION_TABLE_PROPERTY]==self::PROPERTY_ONE){
                    //一对一属性
//                    TODO 字段映射
                    $TableName = strtoupper($Config[self::RELATION_TABLE_NAME]);
                    $TableColumn = $Config[self::RELATION_TABLE_COLUMN];
                    $Model->join("__{$TableName}__ ON __{$UpperMainTable}__.{$TableColumn} = __{$TableName}__.{$TableColumn}",'LEFT');
                }else{
                    //一对多
                    $ArrayProperties[$PropertyName]=$Config;
                }
            }
        }
        $Objects = $Model->where([$this->pk=>['IN',$IDs]])->select();
        //TODO 处理一对多的情况
        return array_key_set($Objects,$this->pk);
    }
    function save(){}
}