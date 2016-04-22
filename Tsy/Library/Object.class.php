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
    
    const PROPERTY_OBJECT='OBJECT';

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
        //检测是否存在属性映射，如果存在则直接读取属性映射，没有则从数据库加载属性映射
        if(!$this->map){
            
        }
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

    /**
     * 获取一个对象属性
     * @param int $ID 对象唯一标示
     * @return array|bool|mixed
     */
    function get($ID){
        if(!is_numeric($ID)){return false;}
        $Object = $this->gets([$ID]);
        return is_array($Object)?$Object[$ID]:[];
    }

    /**
     * @param string $Keyword
     * @param array $W
     * @param string $Sort
     */
    function search($Keyword='',$W=[],$Sort=''){

    }

    /**
     * 删除方法
     * @param $IDs
     * @return bool
     */
    function del($IDs){
        if(is_numeric($IDs)&&$IDs>0){
            $IDs=[$IDs];
        }
        if(!is_array($IDs)){
            L($this->main.'删除失败',LOG_ERR);
            return false;
        }
        return M($this->main)->where([$this->pk=>['IN',$IDs]])->delete();
    }

    /**
     * 获取多个对象属性
     * @param array|int $IDs 主键字段编号值
     * @return array|bool
     */
    function gets($IDs){
        if(is_numeric($IDs)&&$IDs>0){
            $IDs=[$IDs];
        }
        if(!$this->main||!$this->pk||!$IDs||!is_array($IDs)||count($IDs)<1){
            return false;
        }
        $Objects = [];
        $Model = M($this->main);
        $UpperMainTable = strtoupper(parse_name($this->main));
        $ArrayProperties=[];
        foreach ($this->property as $PropertyName=>$Config){
            if(isset($Config[self::RELATION_TABLE_PROPERTY])&&isset($Config[self::RELATION_TABLE_NAME])&&isset($Config[self::RELATION_TABLE_COLUMN]))
            {
                if($Config[self::RELATION_TABLE_PROPERTY]==self::PROPERTY_ONE){
                    //一对一属性
//                    TODO 字段映射
                    $TableName = strtoupper(parse_name($Config[self::RELATION_TABLE_NAME]));
                    $TableColumn = $Config[self::RELATION_TABLE_COLUMN];
                    $Model->join("__{$TableName}__ ON __{$UpperMainTable}__.{$TableColumn} = __{$TableName}__.{$TableColumn}",'LEFT');
                }else{
                    //一对多
                    $ArrayProperties[$PropertyName]=$Config;
                }
            }
        }
        $Objects = $Model->where([$this->pk=>['IN',$IDs]])->select();
        if(!$Objects){return [];}
        //处理一对多的情况
        $ArrayPropertyValues=[];
        foreach ($ArrayProperties as $PropertyName=>$Config){
            $ArrayPropertyValues[$PropertyName]=array_key_set(M($Config[self::RELATION_TABLE_NAME])->where([$Config[self::RELATION_TABLE_COLUMN]=>['IN',array_column($Objects,$Config[self::RELATION_TABLE_COLUMN])]])->select(),$Config[self::RELATION_TABLE_COLUMN],true);
        }
        //处理多对多属性
        $LinkPropertyValues=[];
        foreach ($this->link as $PropertyName=>$Config){
            if(
                isset($Config[self::RELATION_TABLE_NAME])&&
                isset($Config[self::RELATION_TABLE_COLUMN])&&
                isset($Config[self::RELATION_TABLE_LINK_HAS_PROPERTY])&&
                isset($Config[self::RELATION_TABLE_LINK_TABLES])&&
                is_array($Config[self::RELATION_TABLE_LINK_TABLES])&&
                count($Config[self::RELATION_TABLE_LINK_TABLES])>0
            ){
                $LinkModel = M($Config[self::RELATION_TABLE_NAME])->where(
                    [
                        $Config[self::RELATION_TABLE_COLUMN]=>['IN',array_column($Objects,$Config[self::RELATION_TABLE_COLUMN])]
                    ]
                );
                $UpperJoinTable = strtoupper(parse_name($Config[self::RELATION_TABLE_NAME]));
//                TODO Link表中的多对多关系先忽略不计
                foreach ($Config[self::RELATION_TABLE_LINK_TABLES] as $TableName=>$Conf){
                    $TableName = strtoupper(parse_name($TableName));
                    $TableColumn = $Conf[self::RELATION_TABLE_COLUMN];
                    $LinkModel->join("__{$TableName}__ ON __{$UpperJoinTable}__.{$TableColumn} = __{$TableName}__.{$TableColumn}",'LEFT');
                }
                $LinkPropertyValues[$PropertyName] = array_key_set($LinkModel->select(),$Config[self::RELATION_TABLE_COLUMN],true);
            }else{
                L('Obj配置有问题');
            }
        }
//         组合生成最终的Object对象
        $Objects = array_key_set($Objects,$this->pk);
        foreach ($Objects as $ID=>$Object){
            foreach ($ArrayProperties as $PropertyName => $PropertyConfig){
                $Objects[$ID][$PropertyName]=isset($ArrayPropertyValues[$PropertyName][$Object[$PropertyConfig[self::RELATION_TABLE_COLUMN]]])?$ArrayPropertyValues[$PropertyName][$Object[$PropertyConfig[self::RELATION_TABLE_COLUMN]]]:[];
            }
            foreach ($this->link as $PropertyName=>$PropertyConfig){
                $Objects[$ID][$PropertyName]=isset($LinkPropertyValues[$PropertyName][$Object[$PropertyConfig[self::RELATION_TABLE_COLUMN]]])?$LinkPropertyValues[$PropertyName][$Object[$PropertyConfig[self::RELATION_TABLE_COLUMN]]]:[];
            }
        }
        return $Objects;
    }
    function save(){}
}