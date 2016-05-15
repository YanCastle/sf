<?php
/**
 * Created by PhpStorm.
 * User: castle
 * Date: 2016/4/18
 * Time: 8:05
 */

namespace Tsy\Library;


use Finance\Object\AccountObject;
use Store\Model\GoodsPriceModel;
use Tsy\Plugs\Db\Db;

class Object
{
    const PROPERTY_ONE="\x00";
    const PROPERTY_ARRAY="\x01";
    
    const PROPERTY_OBJECT="\x02";

    const RELATION_TABLE_NAME="\x03";
    const RELATION_TABLE_COLUMN="\x04";
    const RELATION_TABLE_PROPERTY="\x05";
    const RELATION_TABLE_LINK_HAS_PROPERTY="\x06";
    const RELATION_TABLE_LINK_TABLES="\x07";
    const RELATION_OBJECT="\x08";
    const RELATION_OBJECT_NAME="\x09";
    const RELATION_OBJECT_COLUMN="\x10";

    protected $main='';//主表名称，默认为类名部分
    protected $pk='';//表主键，默认自动获取
    protected $link=[];//多对多属性配置
    protected $property=[];//一对一或一对多属性配置
    protected $data=[];//添加、修改时的数据
    protected $searchFields=[];//参与Keywords搜索的字段列表
    protected $propertyMap=[];//属性配置反向映射
    protected $object=[];//对象化属性配置，一个对象中嵌套另一个属性的配置情况
    protected $_write_filter=[];//输入写入过滤配置
    protected $_read_filter=[];//输入读取过滤配置
    public $map=[
//        自动生成
    ];//字段=》类型 表名 映射
    function __construct()
    {
        //检测是否存在属性映射，如果存在则直接读取属性映射，没有则从数据库加载属性映射
//        提取数据库字段，合并到map中
        if(!$this->main){
            $this->main = $this->getObjectName();
        }
        if(APP_DEBUG){
            $this->setMapByColumns();
        }else{
            if($CachedMap = cache('ObjectMap'.$this->main)){
                //有缓存存在的情况下
                $this->map = array_merge($CachedMap,$this->map);
            }else{
//                没有缓存存在的情况下先获取缓存然后再缓存
                $this->setMapByColumns();
            }
        }
        $this->setPropertyMap();
    }

    /**
     * 设置属性映射
     */
    private function setPropertyMap(){
        foreach ($this->link as $name=>$item){
            $this->propertyMap[$name]=array_merge(['Type'=>'LINK',],$item);
        }
        foreach ($this->property as $name=>$item){
            $this->propertyMap[$name]=array_merge(['Type'=>'Property',],$item);
        }
    }
    /**
     * 设置字段过滤配置相关信息
     */
    private function setMapByColumns(){
        //        生成需要字段缓存的表列表
        $tables = [$this->main];
        if($PropertyTables = array_column($this->property,self::RELATION_TABLE_NAME)){
            $tables = array_merge($tables,$PropertyTables);
        }
        if($LinkTables = array_keys(call_user_func_array('array_merge',array_values(array_column($this->link,self::RELATION_TABLE_LINK_TABLES))))){
            $tables = array_merge($tables,$LinkTables);
        }
        $tables = array_map(function($data){
            return parse_name($data);
        },$tables);
        $Model = new Db();
        $Columns = $Model->getColumns($tables,true);
        //生成map结构并缓存
        foreach ($Columns as $TableName=>$column){
//            解析并生成格式限制和转化配置
            foreach ($column as $item){
                $type = explode(',',str_replace(['(',')',' '],',',$item['type']));
                $this->map[$TableName.'.'.$item['field']]=[
                    'U'=>strpos($item['type'],'unsigned')>0,//是否无符号
                    'T'=>count($type)==1?$type:[$type[0],$type[1]],//数据库类型
                    'D'=>$item['default'],//默认值
                    'P'=>'PRI'==$item['key'],//是否主键
                    'N'=>'YES'==$item['null'],//是否为null
                    'A'=>'auto_increment'==$item['extra']//是否自增
                ];
                if(!$this->pk&&'PRI'==$item['key']){
                    $this->pk=$item['field'];
                }
            }
        }
        cache('ObjectMap'.$this->main,$this->map);
    }
    /**
     * 得到当前的数据对象名称
     * @access public
     * @return string
     */
    public function getObjectName() {
        if(empty($this->name)){
            $name = substr(get_class($this),0,-strlen('Object'));
            if ( $pos = strrpos($name,'\\') ) {//有命名空间
                $this->name = substr($name,$pos+1);
            }else{
                $this->name = $name;
            }
        }
        return $this->name;
    }

    /**
     * 设置属性值
     * @param $name
     * @param $value
     */
    function __set($name, $value)
    {
        if(property_exists($this,$name)){
            $this->$name=$value;
        }
        else{
            $this->data[$name]=$value;
        }
    }

    /**
     * 获取属性值
     * @param $name
     * @return mixed
     */
    function __get($name)
    {
        if(property_exists($this,$name)){
            return $this->$name;
        }
        else{
            return $this->data[$name];
        }
    }

    function add(){
//        此处自动读取属性并判断是否是必填属性，如果是必填属性且无。。。则。。。
        $data=[];
        foreach ($this->map as $key=>$map){
            $TableName=explode('.',$key)[0];
            $column=explode('.',$key)[1];
            if (!$data[$TableName]['PK']){
                $data[$TableName]['PK']=$map['P']===true?$column:'';
            }
            if (isset($_POST[$column])){
                if (!call_user_func($map['T'][0],$_POST[$column])){
                    return $column.'参数类型错误';
                }
                if (count($_POST[$column])>=$map['T'][1]){
                    return $column.'参数过长';
                }
                $data[$TableName]['data'][$column]=$_POST[$column];
            }else{
                if (true===$map['N']&&$map['P']===false){
                    //TODO 有外键时，数据初始化的问题
//                    return $column.'参数不完整';
                }
                if (true===$map['P']){
                    continue;
                }
                $data[$TableName]['data'][$column]=$_POST[$column];
            }
        }
        if (!$data){
            return '没有传参数或参数错误';
        }
        foreach ($data as $d){
            if (array_filter($d)==[]){
                return $d.'未传入任何参数';
            }
        }
        foreach ($data as $key=>$Data){
            $Model=M($key);
            $$Data['PK']=$Model->add($Data['data']);
        }
        return $$this->pk?$this->get($$this->pk):false;
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
    function search($Keyword='',$W=[],$Sort='',$P=1,$N=20){
        $Model = new Model($this->main);
        $DB_PREFIX = C('DB_PREFIX');
        $ObjectIDs=[];
        $FieldPrefix = $DB_PREFIX.strtolower($this->main).'.';
        $Tables=['__'.strtoupper($this->main).'__'];
        $ObjectSearchConfig=[];
        $Where = [];
        if(is_string($Keyword)&&strlen($Keyword)>0){
            foreach ($this->searchFields as $Filed){
                $Where[$Filed]=['LIKE','%'.str_replace([' ',';',"\r\n"],'',$Keyword).'%'];
            }
            $Model->where($Where);
        }
        if($W){
            $Where=[];
            foreach ($W as $k=>$v){
                if(($TableColumn = explode('.',$k))&&$v){
                    switch (count($TableColumn)){
                        case 1:
//                            主表属性搜索条件
                            $Where[$TableColumn[0]]=$v;
                            break;
                        case 2:
//TODO                             属性表中的搜索条件
//                            读取属性映射列表，获取属性类型
                            $TableName = $TableColumn[0];
                            $ColumnName = $TableColumn[1];
                            if(!isset($ObjectSearchConfig[$TableName])){
                                $ObjectSearchConfig[$TableName]=[];
                            }
                            $ObjectSearchConfig[$TableName][$ColumnName]=$v;
                            break;
                        default:
                            // 返回失败
                            L('ERROR_SEARCH_CONFIG',LOG_WARNING,[$k=>$v]);
                            break;
                    }
                }
                //TODO 如果开启强制校验模式则返回错误
            }
            $MoreModel = new Model();
            foreach ($ObjectSearchConfig as $tableName=>$item){
                $Where=[];
                foreach($item as $key=>$value){
                    if(is_array($value)){
                            $Where['_complex'][$key]=$value;
                    }else{
                        $Where[$key]=$value;
                    }
                }
                if($rs=$MoreModel->table($DB_PREFIX.strtolower($tableName))->where($Where)->getField(str_replace('_','',parse_name($this->pk)),true)){
                    $ObjectIDs = $ObjectIDs?array_intersect($ObjectIDs,$rs):$rs;
                }
            }
        }
        $ObjectIDs=$ObjectIDs?array_intersect($ObjectIDs,$Model->getField($this->pk,true)):$Model->getField($this->pk,true);
        $PageIDs = array_chunk($ObjectIDs,$N);
        $Objects = isset($PageIDs[$P-1])?$this->gets($PageIDs[$P-1]):[];
        return [
            'L'=>$Objects?array_values($Objects):[],
            'P'=>$P,
            'N'=>count($Objects),
            'T'=>count($ObjectIDs),
        ];
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
        $PropertyObjects = [];
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
            }elseif(isset($Config[self::RELATION_TABLE_PROPERTY])&&isset($Config[self::PROPERTY_OBJECT])&&true===$Config[self::PROPERTY_OBJECT]&&isset($Config[self::RELATION_OBJECT_COLUMN])&&isset($Config[self::RELATION_OBJECT_NAME])){
                $PropertyObjects[$PropertyName]=$Config;
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
                L('Obj配置有问题',LOG_ERR,$Config);
            }
        }
        //处理对象配置
        $PropertyObjectValues=[];
        foreach ($PropertyObjects as $Key=>$Config){
            $ObjectFullName = $Config[self::RELATION_OBJECT_NAME].'Object';
            if(!property_exists($this,$ObjectFullName)){
                $ClassName = $_GET['_m'].'\\Object\\'.$ObjectFullName;
                $this->$ObjectFullName=new $ClassName;
            }
            $ObjectIDs = array_column($Objects,$Config[self::RELATION_OBJECT_COLUMN]);
            $PropertyObjectValues[$Key]=is_array($ObjectIDs)&&$ObjectIDs?$this->$ObjectFullName->gets($ObjectIDs):[];
        }
//         组合生成最终的Object对象
        $Objects = array_key_set($Objects,$this->pk);
        foreach ($Objects as $ID=>$Object){
//            处理一对多关系
            foreach ($ArrayProperties as $PropertyName => $PropertyConfig){
                $Objects[$ID][$PropertyName]=isset($ArrayPropertyValues[$PropertyName][$Object[$PropertyConfig[self::RELATION_TABLE_COLUMN]]])?$ArrayPropertyValues[$PropertyName][$Object[$PropertyConfig[self::RELATION_TABLE_COLUMN]]]:[];
            }
//            处理多对多关系
            foreach ($this->link as $PropertyName=>$PropertyConfig){
                $Objects[$ID][$PropertyName]=isset($LinkPropertyValues[$PropertyName][$Object[$PropertyConfig[self::RELATION_TABLE_COLUMN]]])?$LinkPropertyValues[$PropertyName][$Object[$PropertyConfig[self::RELATION_TABLE_COLUMN]]]:[];
            }
//            处理Object配置
            foreach ($PropertyObjects as $Key=>$Config){
                $Objects[$ID][$Key]=isset($PropertyObjectValues[$Key][$Object[$Config[self::RELATION_OBJECT_COLUMN]]])?$PropertyObjectValues[$Key][$Object[$Config[self::RELATION_OBJECT_COLUMN]]]:[];
            }
        }
        return $Objects;
    }
    function save(){}
}