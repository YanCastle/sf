<?php
/**
 * Created by PhpStorm.
 * User: castle
 * Date: 2016/4/18
 * Time: 8:05
 */

namespace Tsy\Library;

//use Tsy\Plugs\Db\Db;

class Object
{
    const PROPERTY_ONE = "00"; //一对一属性配置，
    const PROPERTY_ONE_PROPERTY = "04"; //一对一额外属性配置，
    const PROPERTY_ARRAY = "01"; //一对多属性配置
    const PROPERTY_ONE_OBJECT = "02"; //一对一属性配置
    const PROPERTY_ARRAY_OBJECT = "03"; //一对多属性配置

//    const PROPERTY_OBJECT = "02";

    const RELATION_TABLE_NAME = "03"; //关系表名称
    const RELATION_MAIN_COLUMN = "103"; //主表字段名称
    const RELATION_TABLE_COLUMN = "04"; //关系表字段
    const RELATION_TABLE_FIELDS = "101"; //关系表字段接受字符串或数组，如果数组最后一个值为布尔值且为true表示排除这些字段
    const RELATION_TABLE_PROPERTY = "05"; //关系类型， 上面的一对多或者一对一
    const RELATION_TABLE_LINK_HAS_PROPERTY = "06"; // 多对多配置中是否具有属性
    const RELATION_TABLE_LINK_TABLES = "07"; //多对多属性的连接表表名
    const RELATION_OBJECT = "08"; //映射关系对象
    const RELATION_OBJECT_NAME = "09"; //映射关系对象名称
    const RELATION_OBJECT_COLUMN = "10"; //映射关系对象字段
    const RELATION_ORDER_COLUMN = "11"; //映射字段的排序算法
    const RELATION_MUST = 0xFF;//是否必须出现该属性，否则是默认属性
//    const RELATION_TABLE=0xFF;

    //字段配置
    const FIELD_CONFIG_DEFAULT='D';//当值不存在时会取默认值
    const FIELD_CONFIG_DEFAULT_FUNCTION='DF';//当值不存在时会取默认值
    const FIELD_CONFIG_VALUE='V';//不管值是否存在直接覆盖
    const FIELD_CONFIG_VALUE_FUNCTION='VF';//不管值是否存在直接覆盖
    const FIELD_CONFIG_CALLBACK_REPLACE_STR='###';

    const READER_FILTER_FIELD='F';

    protected $main = '';//主表名称，默认为类名部分
    protected $pk = '';//表主键，默认自动获取
    protected $fields='';//自动化对象的字段过滤，接受字符串或数组，如果数组最后一个值为布尔值且为true表示排除这些字段
    protected $link = [];//多对多属性配置
    protected $property = [];//一对一或一对多属性配置
    protected $data = [];//添加、修改时的数据
    protected $searchFields = [];//参与Keywords搜索的字段列表
    protected $searchTable='';
    protected $propertyMap = [];//属性配置反向映射
    protected $_write_filter = [];//输入写入过滤配置
    protected $_read_filter = [];//输入读取过滤配置
    protected $_read_deny=[];//禁止读取字段
    protected $_fieldMap=[];//通过字段查归属表的
    protected $_read_group='';
    protected $_read_having='';
    protected $_read_where=[];
    protected $_tableFieldsMap=[];//表名=>[字段名称]
    protected $searchWFieldsGroup=[
//        'GroupName'=>['Name','Number','BarCode','Standard','PinYin','Memo']
    ];
    protected $searchWFieldsConf=[
//        'Goods'=>'Goods',
    ];
    
    public $is_dic=false;
    public $allow_add=true;//是否允许添加
    public $addFields=[];//定义允许添加的字段，规则同字段限定,默认不限制
    public $addFieldsConfig=[];
    public $addFieldsGroup=[];
    public $allow_save=true;//是否允许修改
    public $saveFields=[];//定义允许修改的字段，规则同字段限定
    public $saveFieldsConfig=[];
    public $saveFieldsGroup=[];
    public $allow_del=true;//是否允许删除
    public $map = [
//        自动生成
    ];//字段=》类型 表名 映射
    protected $__CLASS__;
    protected $MC=[];
    protected $directProperties=[];
    public $allow_replaceW=false;
    public $save_add_if_not_exist=false;
    function __construct($name='',$config=[])
    {
        //检测是否存在属性映射，如果存在则直接读取属性映射，没有则从数据库加载属性映射
//        提取数据库字段，合并到map中
        $this->__CLASS__ = get_class($this);
        $this->MC = explode('\\\\',str_replace(['Controller','Object','Model'],'' ,$this->__CLASS__ ) );
        if (!$this->main) {
            $this->main = $this->_getObjectName();
        }
        if (APP_DEBUG) {
            $this->setMapByColumns();
        } else {
            if ($CachedMap = cache('ObjectMap' . $this->main)) {
                //有缓存存在的情况下
                $this->map = array_merge($CachedMap, $this->map);
                $this->_tableFieldsMap = cache('ObjectTableFieldsMap'.$this->main);
                $this->_fieldMap=cache('ObjectFieldMapMap'.$this->main);
            } else {
//                没有缓存存在的情况下先获取缓存然后再缓存
                $this->setMapByColumns();
            }
        }
        $this->setPropertyMap();
        foreach ($this->property as $PropertyName=>$Config){
            if(isset($Config[self::RELATION_TABLE_PROPERTY])&&$Config[self::RELATION_TABLE_PROPERTY]==self::PROPERTY_ONE){
                $this->directProperties[$PropertyName]=$this->_parseFieldsConfig($Config[self::RELATION_TABLE_NAME],isset($Config[self::RELATION_TABLE_NAME])?$Config[self::RELATION_TABLE_NAME]:'');
            }
            if(!isset($Config[self::RELATION_MAIN_COLUMN])&&isset($Config[self::RELATION_TABLE_COLUMN])){
                $Config[self::RELATION_MAIN_COLUMN]=$Config[self::RELATION_TABLE_COLUMN];
            }
            if(!isset($Config[self::RELATION_TABLE_COLUMN])&&isset($Config[self::RELATION_MAIN_COLUMN])){
                $Config[self::RELATION_TABLE_COLUMN]=$Config[self::RELATION_MAIN_COLUMN];
            }
        }
        foreach ($config as $k=>$v){
            $this->$k=$v;
        }
    }

    /**
     * 设置属性映射
     */
    private function setPropertyMap()
    {
        foreach ($this->link as $name => $item) {
            $this->propertyMap[$name] = array_merge(['Type' => 'LINK',], $item);
        }
        foreach ($this->property as $name => $item) {
            $this->propertyMap[$name] = array_merge(['Type' => 'Property',], $item);
        }
    }

    /**
     * 批量添加接口
     * @param array $data
     * @return array|bool|mixed|string
     */
    function adds($data=[],$Replace=false){
        //        此处自动读取属性并判断是否是必填属性，如果是必填属性且无。。。则。。。
        if(!$this->allow_add)return false;
        if( !$data && isset($_POST['data']) && $_POST['data'] )
            $data=$_POST['data'];
        //遍历添加过滤配置
//        $addDatas=[];
        $ReplaceDatas=$AddDatas=[];
        foreach ($data as $k=>$row){
            if(!is_array($row)){
                return '数据不合规范';
            }
            if(isset($row[$this->pk])&&$row[$this->pk]){
                if(is_array($rs = $this->_parseChangeFieldsConfig('save',$row))){
                    $ReplaceDatas[]=$rs;
                }else{
                    return $rs;
                }
            }else{
                if(is_array($rs = $this->_parseChangeFieldsConfig('add',$row))){
                    $AddDatas[]=$rs;
                }else{
                    return $rs;
                }
            }
        }
        if((is_array($AddDatas)&&$AddDatas)||(is_array($ReplaceDatas)&&$ReplaceDatas)){
            startTrans();
            $Rs = true;
            if($AddDatas){
                $Rs=$Rs&&!!M($this->main)->addAll($AddDatas);
            }
            if($ReplaceDatas){
                foreach ($ReplaceDatas as $k=>$row){
                    if($Rs&&($Rs=$Rs&&(false!==M($this->main)->where([$this->pk=>$row[$this->pk]])->save($row)))){
//                        $ReplaceDatas[$k][$this->pk]
                    }else{
                        rollback();
                        return APP_DEBUG?M()->getDbError():'失败';
                    }
                }
            }
            if($Rs){
                commit();
                return true;
            }else{
                rollback();
                return APP_DEBUG?M()->getDbError():'失败';
            }
        }else{
            return $data;
        }
    }
    /**
     * 设置字段过滤配置相关信息
     */
    private function setMapByColumns()
    {
        //        生成需要字段缓存的表列表
        $tables = [$this->main];
        if ($PropertyTables = array_column($this->property, self::RELATION_TABLE_NAME)) {
            $tables = array_merge($tables, $PropertyTables);
        }
        if ($this->link&&$LinkTables = array_keys(call_user_func_array('array_merge', array_values(array_column($this->link, self::RELATION_TABLE_LINK_TABLES))))) {
            $tables = array_merge($tables, $LinkTables);
        }
        $tables = array_map(function ($data) {
            return parse_name($data);
        }, $tables);
        $Model = new \Tsy\Plugs\Db\Db();
        $Columns = $Model->getColumns($tables, true);
        //生成map结构并缓存
        foreach ($Columns as $TableName => $column) {
//            解析并生成格式限制和转化配置
            foreach ($column as $item) {
                $type = explode(',', str_replace(['(', ')', ' '], ',', $item['Type']));
                $this->map[$TableName . '.' . $item['Field']] = [
                    'U' => strpos($item['Type'], 'unsigned') > 0,//是否无符号
                    'T' => count($type) == 1 ? $type : [$type[0], $type[1]],//数据库类型
                    'D' => $item['Default'],//默认值
                    'P' => 'PRI' == $item['Key'],//是否主键
                    'N' => 'YES' == $item['Null'],//是否为null
                    'A' => 'auto_increment' == $item['Extra'],//是否自增
                    'F'=>$item['Field']
                ];
                if (!$this->pk &&
                    'PRI' == $item['Key']
                ) {
                    $this->pk = $item['Field'];
                }
                //生成反向查询
                if(isset($this->_fieldMap[$item['Field']])){
                    //TODO 考虑重复字段名称怎么处理
                    $this->_fieldMap[$item['Field']]=[];
                }else{
                    $this->_fieldMap[$item['Field']]=$TableName;
                }
                //生成反向Group
                if(!isset($this->_tableFieldsMap[$TableName])){
                    $this->_tableFieldsMap[$TableName]=[];
                }
                $this->_tableFieldsMap[$TableName][]=$item['Field'];
            }
        }
        cache('ObjectTableFieldsMap'.$this->main,$this->_tableFieldsMap);
        cache('ObjectFieldMapMap'.$this->main,$this->_fieldMap);
        cache('ObjectMap' . $this->main, $this->map);
    }

    /**
     * 得到当前的数据对象名称
     * @access public
     * @return string
     */
    protected function _getObjectName()
    {
        if (empty($this->name)) {
            $name = substr(get_class($this), 0, -strlen('Object'));
            if ($pos = strrpos($name, '\\')) {//有命名空间
                $this->name = substr($name, $pos + 1);
            } else {
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
        if (property_exists($this, $name)) {
            $this->$name = $value;
        } else {
            $this->data[$name] = $value;
        }
    }

    /**
     * 获取属性值
     * @param $name
     * @return mixed
     */
    function __get($name)
    {
        if (property_exists($this, $name)) {
            return $this->$name;
        } else {
            return $this->data[$name];
        }
    }
    protected function _parseProperties(&$Properties){
        if(is_array($Properties)&&true===end($Properties)){
            $Properties=array_diff(array_keys($this->propertyMap),$Properties);
        }elseif(false===$Properties){
            $Properties=array_keys($this->propertyMap);
        }
        $Properties = array_unique($Properties);
        $Properties = array_intersect($Properties,array_keys($this->saveFieldsGroup));
        $Properties[] = $this->main;
        $PropertiesColumns=[];
        foreach ($Properties as $property){
            if(isset($this->propertyMap[$property])){
                $PropertiesColumns[$property]=
                    $this->propertyMap[$property]['Type']=='Property'
                        ?(isset($this->property[$property][self::RELATION_TABLE_FIELDS])?$this->property[$property][self::RELATION_TABLE_FIELDS]:M($property)->getDbFields())
                        :(isset($this->link[$property][self::RELATION_TABLE_FIELDS])?$this->link[$property][self::RELATION_TABLE_FIELDS]:M($property)->getDbFields());
            }elseif($property==$this->main){
                $PropertiesColumns[$property]=M($property)->getDbFields();
            }
        }
        $Properties=$PropertiesColumns;
    }
    function add($data=[],$Properties=false)
    {
//        此处自动读取属性并判断是否是必填属性，如果是必填属性且无。。。则。。。
        if(!$this->allow_add)return false;
        if(!$data&&$_POST)
            $data=$_POST;
        //遍历添加过滤配置
        if(method_exists($this,'_before_add')){
            $this->_before_add($data,$Properties);
        }
        $rs = $this->_parseChangeFieldsConfig('add',$data);
        if(is_array($rs)&&$rs){
            startTrans();
            if($PKID = M($this->main)->add($rs)){
                commit();
            }else{
                rollback();
            }
            if(method_exists($this,'_after_add')){
                $this->_after_add($data,$PKID);
            }
            return $PKID?$this->get($PKID):(APP_DEBUG?M()->getDbError():'添加失败');
        }else{
            return $rs;
        }
    }

    /**
     * 获取一个对象属性
     * @param int $ID 对象唯一标示
     * @return array|bool|mixed
     */
    function get($ID=false)
    {
        $ID = $ID?$ID:$_POST[$this->pk];
        if (!is_numeric($ID)) {
            return false;
        }
        $Object = $this->gets([$ID]);
        return is_array($Object)&&isset($Object[$ID])? $Object[$ID] : [];
    }

    /**
     * @param string $TableName
     * @param array $Where
     * @param string $Field
     * @return mixed
     */
    protected function searchW($TableName,array $Where,$Field,$Sort=''){
        $Model = M($TableName);
        return $Model->where($Where)->order($Sort)->getField($Field,true);
    }

    /**
     * @param string $Keyword
     * @param array $W
     * @param string $Sort
     */
    function search($Keyword = '', $W = [], $Sort = '', $P = 1, $N = 20,$Properties=false)
    {
        $Model = M($this->searchTable ? $this->searchTable : $this->main);
//        $DB_PREFIX = C('DB_PREFIX');
        $ObjectIDs = false;
//        $FieldPrefix = $DB_PREFIX . strtolower($this->main) . '.';
//        $Tables = ['__' . strtoupper(parse_name($this->main)) . '__'];
//        $ObjectSearchConfig = [];
        $Where = [];
        $WObjectIDArray = [];
        $KeywordObjectIDs=[];
        if ((is_string($Keyword) || is_numeric($Keyword)) &&
            strlen($Keyword) > 0 && $this->searchFields
        ) {
            foreach ($this->searchFields as $Filed) {
                $Where[$Filed] = ['LIKE', '%' . str_replace([' ', ';',"%", "\r\n"], '', $Keyword) . '%'];
            }
            $Where['_logic'] = 'OR';
            $Model->where($Where);
            $KeywordObjectIDs = $Model->getField($this->pk, true);
        }
        if ($W) {
            $Data = param_group($this->searchWFieldsGroup, $W);
            unset($Data[0]);
            foreach ($Data as $ObjectName => $Params) {
//                $ObjectName 键名称，$Params 该键的搜索配置
//                $a = isset($this->searchWFieldsConf[$ObjectName]);
                if (isset($this->searchWFieldsConf[$ObjectName])) {
                    //如果是一个字符串就直接当表名使用，否则检测是否是回调函数，如果是回调函数则回调，如果不是则空余并给出警告
                    if (is_string($this->searchWFieldsConf[$ObjectName]) && preg_match('/^[a-z_A-Z]+[a-zA-Z]$/', $this->searchWFieldsConf[$ObjectName])) {
//                        $this->searchWFieldsConf
                        //直接值为表名
                        $ObjectClass = implode('\\',[$this->MC[0],'Object',$this->searchWFieldsConf[$ObjectName].'Object']);
//                        if(class_exists($ObjectClass)){
                            $PK = class_exists($ObjectClass)?(new $ObjectClass)->pk:$this->pk;
//                        }
                        if(isset($W['_logic'])&&in_array(strtolower($W['_logic']),['or','and']))$Params['_logic']=$W['_logic'];
                        $TableSearchIDs = $this->searchW($this->searchWFieldsConf[$ObjectName], $Params, $PK);
                        if($TableSearchIDs)
                            $WObjectIDArray[]=$this->searchW($this->main,[$PK=>['IN',$TableSearchIDs]],$this->pk);
                    } elseif (is_callable($this->searchWFieldsConf[$ObjectName])) {
                        //回调
                        $Result = call_user_func($this->searchWFieldsConf[$ObjectName], $Params);
                        if (is_string($Result) && preg_match('/^[a-z_]+[a-z]$/', $Result)) {
                            //吧这个当作表名，再参与上一个逻辑
                            $WObjectIDArray[] = $this->searchW($Result, $Params, $this->pk,$Sort);
                        } elseif (is_array($Result)
                            && preg_match('/^\d+$/', implode('', $Result))
//                            &&!in_array(false,array_map(function($d){return is_numeric($d);},$Result ) )
                        ) {
                            //继续检测是否是以为数组且数组值全为数字且大于0
                            $WObjectIDArray[] = $Result;
                        } else {
                            //回调函数的返回值错误
                            L(E('_ERROR_SEARCH_CALLBACK_') . json_encode($this->searchWFiledsConf[$ObjectName], JSON_UNESCAPED_UNICODE));
                        }
                    } else {

                    }
                } else {
                    L(E('_NO_SEARCH_TABLE_CONFIG_').$ObjectName);
                }
            }
        }
        if($WObjectIDArray){
            $ObjectIDs=array_unique(call_user_func_array('array_merge',$WObjectIDArray));
        }
        //取交集
        if($ObjectIDs){
            if(strlen($Keyword)){
                $ObjectIDs = array_intersect($ObjectIDs,$KeywordObjectIDs);
            }
        }else{
            $ObjectIDs=$KeywordObjectIDs;
        }
        if (strlen($Keyword) === 0 && count($W) === 0) {
            $ObjectIDs = $Model->page($P, $N)->getField($this->pk, true);
            return [
                'L' => $ObjectIDs?array_values($this->gets($ObjectIDs,$Properties,$Sort)):[],
                'P' => $P,
                'N' => $N,
                'T' => $Model->field('COUNT(' . $this->pk . ') AS Count')->find()['Count'],
            ];
        }
        if (!is_array($ObjectIDs)) {
            return [
                'L' => [], 'P' => $P, 'N' => $N, 'T' => 0
            ];
        }
        $T = count($ObjectIDs);
        rsort($ObjectIDs, SORT_NUMERIC);
        $PageIDs = is_array($ObjectIDs) ? array_chunk($ObjectIDs, $N) : [];
        $Objects = isset($PageIDs[$P - 1]) ? $this->gets($PageIDs[$P - 1], $Properties,$Sort) : [];
        return [
            'L' => $Objects ? array_values($Objects) : [],
            'P' => $P,
            'N' => $N,
            'T' => $T,
        ];
    }

    /**
     * 删除方法
     * @param $IDs
     * @return bool
     */
    function del($IDs)
    {
        if(!$this->allow_del)return false;
        if (is_numeric($IDs) &&
            $IDs > 0
        ) {
            $IDs = [$IDs];
        }
        if (!is_array($IDs)) {
            L($this->main . '删除失败', LOG_ERR);
            return false;
        }
        return !!M($this->main)->where([$this->pk => ['IN', $IDs]])->delete();
    }
    /**
     * 获取多个对象属性
     * @param array|int $IDs 主键字段编号值
     * @return array|bool
     */
    function gets($IDs=[],$Properties=false,$Sort='')
    {
        !(false===$Properties&&isset($_POST['Properties'])) or $Properties=$_POST['Properties'];
//        $AllProperties=array_merge(array_keys($this->property),array_keys($this->link));
//        if(true===end($Properties))
        $emptyClass=new \stdClass();
        $Exclude = true === $Properties&&end($Properties);
        is_array($Properties) or $Properties = [];//初始化
        foreach (array_merge($this->property, $this->link) as $PN => $P) {
            if ($Exclude) {
                if (false !== ($Key = array_search($PN, $Properties, true))) {
                    unset($Properties[$Key]);
                } else {
                    $Properties[] = $PN;
                }
            } else {
                if (!isset($P[self::RELATION_MUST]) || true === $P[self::RELATION_MUST]) {
                    $Properties[] = $PN;
                }
            }
        }
//        true === end($Properties) ? $Properties=array_diff($AllProperties,$Properties):$Properties=$AllProperties;//做全局判定
//        ID检测
        if(!$IDs&&isset($_POST[$this->pk.'s'])){$IDs=$_POST[$this->pk.'s'];}
        if (is_numeric($IDs) &&
            $IDs > 0
        ) {
            $IDs = [$IDs];
        }
//        配置检测
        if (!$this->main || !$this->pk || !$IDs || !is_array($IDs) || count($IDs) < 1) {
            return false;
        }
        $Objects = [];
        $fields=[];
        $PropertyObjects = [];
        $UpperMainTable = strtoupper(parse_name($this->main));
        $Model = M($this->main);
        $Fields=$OneObjectProperties=$ArrayProperties=$OneProperties=$ArrayObjectProperties=$OneObjectPropertyValues=[];

        foreach ($this->property as $PropertyName => $Config) {
//            如果设定了获取的属性限定范围且该属性没有在该范围内则跳过
            if(is_array($Properties)&&!in_array($PropertyName,$Properties))continue;
            if (isset($Config[self::RELATION_TABLE_PROPERTY]) &&
                isset($Config[self::RELATION_TABLE_NAME]) &&
                isset($Config[self::RELATION_TABLE_COLUMN])
                && (in_array($PropertyName, $Properties, true))
//                &&(!isset($Config[self::RELATION_MUST])||(isset($Config[self::RELATION_MUST])&&$Config[self::RELATION_MUST]===true)||in_array($PropertyName,$Properties))
            ) {
                switch ($Config[self::RELATION_TABLE_PROPERTY]){
                    case self::PROPERTY_ONE:
                        //一对一属性
                        $TableName = strtoupper(parse_name($Config[self::RELATION_TABLE_NAME]));
                        $TableColumn = $Config[self::RELATION_TABLE_COLUMN];
                        if (isset($Config[self::RELATION_TABLE_FIELDS]) && $Config[self::RELATION_TABLE_FIELDS]) {
                            $Fields = array_merge($Fields, is_string($Config[self::RELATION_TABLE_FIELDS]) ?
                                explode(',', "__{$TableName}__" . str_replace(',', "__{$TableName}__.", $Config[self::RELATION_TABLE_FIELDS])) :
                                array_map(function ($field) use ($TableName) {
                                    return "__{$TableName}__.{$field}";
                                }, $Config[self::RELATION_TABLE_FIELDS]));
                        }else{
                            $field = isset($Config[self::RELATION_TABLE_FIELDS])?$Config[self::RELATION_TABLE_FIELDS]:M($Config[self::RELATION_TABLE_NAME])->getDbFields();
                            if(is_array($field)&&end($field)===true){//反向
                                $field = array_diff(M($Config[self::RELATION_TABLE_NAME])->getDbFields(),$field );
                            }
                            $Fields = array_merge($Fields,array_map(function($d)use($TableName){
                                return strpos(trim($d),'.')?$d:"__{$TableName}__.{$d}";
                            },is_array($field)?$field:explode(',',$field)));
                        }
                        $MainColumn = $Config[self::RELATION_MAIN_COLUMN] ? $Config[self::RELATION_MAIN_COLUMN] : $TableColumn;
                        $Model->join("__{$TableName}__ ON __{$UpperMainTable}__.{$MainColumn} = __{$TableName}__.{$TableColumn}", 'LEFT');
                        break;
                    case self::PROPERTY_ONE_OBJECT:
                        //一对一的对象式结构
                        if(!isset($Conf[self::RELATION_MAIN_COLUMN])){
                            $Conf[self::RELATION_MAIN_COLUMN]=$Conf[self::RELATION_TABLE_COLUMN];
                        }
                        if(!isset($Conf[self::RELATION_TABLE_FIELDS])){
                            $Conf[self::RELATION_TABLE_FIELDS]='';
                        }
                        $OneObjectProperties[$PropertyName]=$Config;
                        break;
                    case self::PROPERTY_ARRAY:
                        $ArrayProperties[$PropertyName] = $Config;
                        break;
                    case self::PROPERTY_ONE_PROPERTY:
                        //单一表格属性映射
                        $OneProperties[$PropertyName]=$Config;
                        break;
                    case self::PROPERTY_ARRAY_OBJECT:
                        //多个对象化映射
                        $ArrayObjectProperties[$PropertyName]=$Config;
                        break;
                    default:
                        L('错误的Property配置');
                        break;
                }
            } elseif (
//                isset($Config[self::RELATION_TABLE_PROPERTY]) &&
                isset($Config[self::RELATION_OBJECT]) &&
                true === $Config[self::RELATION_OBJECT] &&
                isset($Config[self::RELATION_OBJECT_COLUMN]) &&
                isset($Config[self::RELATION_OBJECT_NAME])
            ) {
//                if(isset($Config[self::RE]))
                $PropertyObjects[$PropertyName] = $Config;
            }
        }
        if(property_exists($this,'order')&&$this->order){
            $Model->order($this->order);
        }
        //获取所有字段
        $Fields = array_unique(array_merge(array_map(function ($d)use($UpperMainTable){
            return strpos(trim($d),'.')?$d:"__{$UpperMainTable}__.{$d}";
        },M($this->main)->getDbFields()),$Fields));
        //准备做字段冲突检测，若有冲突则以前面的为准。
//        if(count($Fields)!=count(array_unique(explode(',',preg_replace('/[_A-Za-z]+\./','',implode(',',$Fields)))))){
//
//        }
        foreach ($Fields as $k=>$field){
            $explode = explode('.',$field);
            $field = end($explode);
            if(in_array($field,$fields)||strlen($field)==0){
                unset($Fields[$k]);continue;
            }
            $fields[]=$field;
        }
        if($this->_read_filter){
            if(end($this->_read_filter)===true){
                $Fields=$this->_read_filter;
            }else{
                foreach ($this->_read_filter as $Config=>$ColumnName){
                    if(is_numeric($Config)&&is_string($ColumnName)){
                        $Model->field($ColumnName,false,true);
                    }else
                        $Fields[$ColumnName]=$Config;
                }
            }
        }
        if ($Fields) {
            $Model->field($Fields,false,true);
            $Fields = [];
        }
//        "SELECT A,B,C FROM A,B ON A.A=B.A WHERE"
        $Objects = $Model->group($this->_read_group)->having($this->_read_having)->where($this->_read_where)->where(["__{$UpperMainTable}__.".$this->pk => ['IN', $IDs]])->order($Sort)->select();
        if (!$Objects) {
            return [];
        }
        $this->_afterObjectsGetsSelect($Objects);
        //处理一对多的情况
        $ArrayPropertyValues = $OnePropertyValues = $ArrayObjectPropertyValues =[];
        foreach ($ArrayProperties as $PropertyName => $Config) {
            //            如果设定了获取的属性限定范围且该属性没有在该范围内则跳过
            if(is_array($Properties)&&!in_array($PropertyName,$Properties))continue;
            $ArrayPropertyValues[$PropertyName] = array_key_set(M($Config[self::RELATION_TABLE_NAME])->field(isset($Config[self::RELATION_TABLE_FIELDS]) ? $Config[self::RELATION_TABLE_FIELDS] : true)->where([$Config[self::RELATION_TABLE_COLUMN] => ['IN', array_column($Objects, $Config[self::RELATION_MAIN_COLUMN])]])->order(isset($Config[self::RELATION_ORDER_COLUMN]) ? $Config[self::RELATION_ORDER_COLUMN] : '')->select(), $Config[self::RELATION_TABLE_COLUMN], true);
        }
        //处理一对一的属性结构
        foreach ($OneProperties as $PropertyName=>$Config){
            $OnePropertyValues[$PropertyName] = array_key_set(M($Config[self::RELATION_TABLE_NAME])->field(isset($Config[self::RELATION_TABLE_FIELDS]) ? $Config[self::RELATION_TABLE_FIELDS] : true)->where([$Config[self::RELATION_TABLE_COLUMN] => ['IN', array_column($Objects, $Config[self::RELATION_MAIN_COLUMN])]])->select(), $Config[self::RELATION_TABLE_COLUMN]);
        }

        //封装一对一的对象结构
        foreach ($OneObjectProperties as $PropertyName=>$Config){
            $OneObjectIDs=[];
            $OneObjectModel = M($Config[self::RELATION_TABLE_NAME]);
//                    特殊指定主表字段与子表字段相同
            $OneObjectIDs = array_column($Objects,isset($Config[self::RELATION_MAIN_COLUMN])&&$Config[self::RELATION_MAIN_COLUMN]?$Config[self::RELATION_MAIN_COLUMN]:$Config[self::RELATION_TABLE_COLUMN]);
//                    处理字段
            $Fields = $this->_parseFieldsConfig($Config[self::RELATION_TABLE_NAME],isset($Config[self::RELATION_TABLE_FIELDS])?$Config[self::RELATION_TABLE_FIELDS]:'',$Config[self::RELATION_TABLE_COLUMN]);
            if($OneObjectIDs&&$Fields){
//                $OneObjectPropertyValues[$PropertyName] = array_key_set($OneObjectModel->where([
//                    $Config[self::RELATION_TABLE_COLUMN]=>['IN',$OneObjectIDs]
//                ])->field($Fields)->select(),$Config[self::RELATION_TABLE_COLUMN]);
                $ObjectClass=$this->MC[0]."\\Object\\".$Config[self::RELATION_TABLE_NAME]."Object";
                $OneObjectPropertyValues[$PropertyName]=class_exists($ObjectClass)?(new $ObjectClass)->search('',[
                    $Config[self::RELATION_TABLE_COLUMN]=>['IN',$OneObjectIDs]
                ],'',1,9999):[];
                $OneObjectPropertyValues[$PropertyName]=$OneObjectPropertyValues[$PropertyName]?array_key_set($OneObjectPropertyValues[$PropertyName]['L'],$Config[self::RELATION_TABLE_COLUMN]):[];
            }else{
                $OneObjectPropertyValues[$PropertyName]=[];
            }
        }
        // 处理一对多的对象化结构
        foreach ($ArrayObjectProperties as $PropertyName=>$Config){
            $ArrayObjectIDs=[];
            $ArrayObjectModel = M($Config[self::RELATION_TABLE_NAME]);
//                    特殊指定主表字段与子表字段相同
            $ArrayObjectIDs = array_column($Objects,isset($Config[self::RELATION_MAIN_COLUMN])&&$Config[self::RELATION_MAIN_COLUMN]?$Config[self::RELATION_MAIN_COLUMN]:$Config[self::RELATION_TABLE_COLUMN]);
//                    处理字段
//            $Fields = $this->_parseFieldsConfig($Config[self::RELATION_TABLE_NAME],isset($Config[self::RELATION_TABLE_FIELDS])?$Config[self::RELATION_TABLE_FIELDS]:'',$Config[self::RELATION_TABLE_COLUMN]);
            if($ArrayObjectIDs){
//                $ArrayObjectPropertyValues[$PropertyName] = array_key_set($ArrayObjectModel->where([
//                    $Config[self::RELATION_TABLE_COLUMN]=>['IN',$ArrayObjectIDs]
//                ])->field($Fields)->order("{$Config[self::RELATION_TABLE_COLUMN]} DESC")->select(),$Config[self::RELATION_TABLE_COLUMN],true);
                $ObjectClass=$this->MC[0]."\\Object\\".$Config[self::RELATION_TABLE_NAME]."Object";
//                if(class_exists($ObjectClass))
                $ArrayObjectPropertyValues[$PropertyName]=class_exists($ObjectClass)?(new $ObjectClass)->search('',[
                    $Config[self::RELATION_TABLE_COLUMN]=>['IN',$ArrayObjectIDs]
                ],'',1,9999):[];
                $ArrayObjectPropertyValues[$PropertyName]=$ArrayObjectPropertyValues[$PropertyName]?array_key_set($ArrayObjectPropertyValues[$PropertyName]['L'],$Config[self::RELATION_TABLE_COLUMN],true):[];
            }else{
                $ArrayObjectPropertyValues[$PropertyName]=new \stdClass();
            }
        }
        //处理多对多属性
        $LinkPropertyValues = [];
        foreach ($this->link as $PropertyName => $Config) {
            if (
                isset($Config[self::RELATION_TABLE_NAME]) &&
                isset($Config[self::RELATION_TABLE_COLUMN]) &&
                isset($Config[self::RELATION_TABLE_LINK_HAS_PROPERTY]) &&
                isset($Config[self::RELATION_TABLE_LINK_TABLES]) &&
                is_array($Config[self::RELATION_TABLE_LINK_TABLES]) &&
                count($Config[self::RELATION_TABLE_LINK_TABLES]) > 0
            ) {
//                $Fields=[];
                $UpperMainTable = strtoupper(parse_name($Config[self::RELATION_TABLE_NAME]));
                $LinkModel = M($Config[self::RELATION_TABLE_NAME])->where(
                    [
                        "__{$UpperMainTable}__.".$Config[self::RELATION_TABLE_COLUMN] => ['IN', array_column($Objects, $Config[self::RELATION_TABLE_COLUMN])]
                    ]
                );
//                $Fields=[
//                    "__{$UpperMainTable}__.{$Config[self::RELATION_TABLE_COLUMN]}"
//                ];
                $Fields = $Config[self::RELATION_TABLE_LINK_HAS_PROPERTY]?$this->_parseFieldsConfig(parse_name($Config[self::RELATION_TABLE_NAME]),isset($Config[self::RELATION_TABLE_FIELDS])?$Config[self::RELATION_TABLE_FIELDS]:($Config[self::RELATION_TABLE_LINK_HAS_PROPERTY]?[]:[true]),$Config[self::RELATION_TABLE_COLUMN]):[$Config[self::RELATION_TABLE_COLUMN]];
                $UpperJoinTable = strtoupper(parse_name($Config[self::RELATION_TABLE_NAME]));
//                TODO Link表中的多对多关系先忽略不计
                foreach ($Config[self::RELATION_TABLE_LINK_TABLES] as $OriginTableName => $Conf) {
                    $TableName = strtoupper(parse_name($OriginTableName));
                    $TableColumn = $Conf[self::RELATION_TABLE_COLUMN];
                    $LinkModel->join("__{$TableName}__ ON __{$UpperJoinTable}__.{$TableColumn} = __{$TableName}__.{$TableColumn}", 'LEFT');
                    //拿到这张表的所有字段
                    $Fields = array_merge($Fields,$this->_parseFieldsConfig($OriginTableName,isset($Conf[self::RELATION_TABLE_FIELDS])?$Conf[self::RELATION_TABLE_FIELDS]:[]));
                }
                $LinkModel->field($Fields);
                $LinkPropertyValues[$PropertyName] = array_key_set($LinkModel->select(), $Config[self::RELATION_TABLE_COLUMN], true);
            } else {
                L('Obj配置有问题', LOG_ERR, $Config);
            }
        }
        //处理对象配置
        $PropertyObjectValues = [];
        foreach ($PropertyObjects as $Key => $Config) {
            $ObjectName = $Config[self::RELATION_OBJECT_NAME];
            $ModuleObject = explode('\\',$ObjectName);
            if(is_array($ModuleObject)){
                if(count($ModuleObject)==2){
                    $ObjectName = implode('\\',[$ModuleObject[0],'Object',$ModuleObject[1]]).'Object';
                    if(class_exists($ObjectName)){
//                        判断是否在当前这个模块下，如果不在则使用controller来切换
//                        $Object = new $ObjectName();
                        $PropertyObjectValues[$Key] = controller($ModuleObject[0].'/'.$ModuleObject[1].'/gets',['IDs'=>array_column($Objects,$Config[self::RELATION_OBJECT_COLUMN])],'','Object');
//                        $PropertyObjectValues[$Key] = $Object->gets(array_column($Objects,$Config[self::RELATION_OBJECT_COLUMN]));
                    }else{
                        L(E('_OBJECT_PROPERTY_OBJECT_ERROR_').':'.$Key);
                    }
                }elseif(count($ModuleObject)==1){
                    // 当前模块下的。。
                    $ClassName = $this->MC[0].'\\Object\\'.$ObjectName.'Object';
                    $$ObjectName = new $ClassName();
                    $PropertyObjectValues[$Key] = $$ObjectName->gets(array_column($Objects,$Config[self::RELATION_OBJECT_COLUMN]));
                }else{
                    L(E('_OBJECT_PROPERTY_OBJECT_ERROR_').':'.$Key);
                }
            }
//            if (!property_exists($this, $ObjectFullName)) {
//                $ClassName = $this->MC[0] . '\\Object\\' . $ObjectFullName;
//                if(class_exists($ClassName)){
//                    $this->$ObjectFullName = new $ClassName;
//                }else{
//                    $PropertyObjectValues[$Key]=[];
//                    L('对象化配置中配置的对象类不存在：'.$ClassName,LOG_ERR);
//                    continue;
//                }
//            }
//            $ObjectIDs = array_column($Objects, $Config[self::RELATION_OBJECT_COLUMN]);
//            $PropertyObjectValues[$Key] = is_array($ObjectIDs) && $ObjectIDs ? $this->$ObjectFullName->gets($ObjectIDs) : [];
        }
//         组合生成最终的Object对象
        $Objects = array_key_set($Objects, $this->pk);
        $this->_beforeObjectGetsForeach($Objects,$ArrayProperties,$OneProperties,$ArrayObjectProperties,$OneObjectProperties,$LinkPropertyValues,$PropertyObjects);
        foreach ($Objects as $ID => $Object) {
//            处理一对多关系
            foreach ($ArrayProperties as $PropertyName => $PropertyConfig) {
                $Objects[$ID][$PropertyName] = isset($ArrayPropertyValues[$PropertyName][$Object[$PropertyConfig[self::RELATION_TABLE_COLUMN]]]) ? $ArrayPropertyValues[$PropertyName][$Object[$PropertyConfig[self::RELATION_TABLE_COLUMN]]] : [];
            }
            //处理一对一的属性问题
            foreach ($OneProperties as $PropertyName=>$PropertyConfig){
                $Objects[$ID][$PropertyName]=isset($OnePropertyValues[$PropertyName][$Object[$PropertyConfig[self::RELATION_MAIN_COLUMN]]])?$OnePropertyValues[$PropertyName][$Object[$PropertyConfig[self::RELATION_MAIN_COLUMN]]]:$emptyClass;
            }
            //处理一对多的对象化关系组合
            foreach ($ArrayObjectProperties as $PropertyName=>$PropertyConfig){
                $Objects[$ID][$PropertyName] = isset($ArrayObjectPropertyValues[$PropertyName][$Object[$PropertyConfig[self::RELATION_TABLE_COLUMN]]]) ? $ArrayObjectPropertyValues[$PropertyName][$Object[$PropertyConfig[self::RELATION_TABLE_COLUMN]]] : [];
            }
//            处理一对一对象化
            foreach ($OneObjectProperties as $PropertyName=>$PropertyConfig){
                $Objects[$ID][$PropertyName]=isset($OneObjectPropertyValues[$PropertyName][$Object[$PropertyConfig[self::RELATION_MAIN_COLUMN]]])?$OneObjectPropertyValues[$PropertyName][$Object[$PropertyConfig[self::RELATION_MAIN_COLUMN]]]:$emptyClass;
            }
//            处理多对多关系
            foreach ($this->link as $PropertyName => $PropertyConfig) {
                $Objects[$ID][$PropertyName] = isset($LinkPropertyValues[$PropertyName][$Object[$PropertyConfig[self::RELATION_TABLE_COLUMN]]]) ? $LinkPropertyValues[$PropertyName][$Object[$PropertyConfig[self::RELATION_TABLE_COLUMN]]] : [];
            }
//            处理Object配置
            foreach ($PropertyObjects as $Key => $Config) {
                $Objects[$ID][$Key] = isset($PropertyObjectValues[$Key][$Object[$Config[self::RELATION_OBJECT_COLUMN]]]) ? $PropertyObjectValues[$Key][$Object[$Config[self::RELATION_OBJECT_COLUMN]]] : $emptyClass;
            }
            $this->_foreachObjectGets($Objects,$ID);
        }
//        $Objects=array_values($Objects);
        $this->_beforeObjectGetsReturn($Objects);
        return $Objects;
    }

    function save($ID=false,$Params,$Properties=false)
    {
        if(!$this->allow_save)return false;
        $Where=[];
        $ID = $ID?$ID:$_POST[$this->pk];
        if(is_array($ID)){
            foreach ($ID as $v){
                if(!is_numeric($v)){
                    L(E('错误的对象编号'));
                    return false;
                }
            }
            $Where[$this->pk]=['IN',$ID];
        }elseif (is_numeric($ID)){
            $Where[$this->pk]=$ID;
        }else{
            L(E('错误的对象编号'));
        }
        $this->_parseProperties($Properties);
        $rs = $this->_parseChangeFieldsConfig('save',$Params);
        $rs = param_group($Properties,$rs);
        if(false!==$rs){
            $MainColumns = [];
            $ObjectsColumns=[];
            unset($rs[0]);
            startTrans();
            foreach ($rs as $k=>$v){
                if($k==$this->main){
                    if(false===M($this->main)->where($Where)->save($v)){
                        rollback();
                        return false;
                    }
                }else{
                    $ObjectClass = implode("\\",[$this->MC[0],'Object',$k.'Object']);
                    if(class_exists($ObjectClass)) {
                        //调用Object的存储方法
                        $Object = new $ObjectClass();
                        if (is_array($Rs = $Object->save($rows[0], $rows[1]))) {

                        } else {
                            rollback();
                            return $Rs;
                        }
                    }
                }
            }
            return $this->get($ID);
//            if($this->save_add_if_not_exist&&!$this->get($ID)){
//                return $this->add(array_merge($Params,[$this->pk=>$ID]));
//            }
//            startTrans();
//            if($MainColumns){
//                if(false===($rs=M($this->main)->where($Where)->save($MainColumns))){
//                    rollback();
//                    return APP_DEBUG?M()->getDbError():'属性修改失败';
//                }
//            }
//            foreach ($ObjectsColumns as $k=>$rows){
//                if(0===$k||!in_array(strtolower($k),$Properties))continue;
//                $ObjectClass = implode("\\",[$this->MC[0],'Object',$k.'Object']);
//                if(class_exists($ObjectClass)){
//                    //调用Object的存储方法
//                    $Object = new $ObjectClass();
//                    if(is_array($Rs = $Object->save($rows[0],$rows[1]))){
//
//                    }else{
//                        rollback();
//                        return $Rs;
//                    }
//                }else{
//                    //直接操作表格进行存储
//                    if($rows[2]&&$rows[3]&&M($rows[2])->where([$rows[3]=>$rows[0]])->save($rows[1])){
//
//                    }else{
//                        rollback();
//                        return APP_DEBUG?M()->getDbError():"属性{$k}保存失败";
//                    }
//                }
//            }
            commit();
            return $this->get($ID);
        }else{
            return false;
        }
    }

    function saveW($W,$Data){
        if(is_array($W)&&is_array($Data)&&$this->allow_saveW){
            if($Save=M($this->main)->where($W)->save($Data)){
                return $this->gets(M($this->main)->where($W)->getField($this->pk));
            }
            return APP_DEBUG?M()->getDbError():'修改失败';
        }
        return false;
    }

    function replaceW($W,$Data){
        //删除原有数据并用新数据替换，仅针对数据量小的情况下使用，有危险。
        if(!$this->allow_replaceW)return '该类禁止此操作';
        if(is_array($W)&&is_array($Data)){
            foreach ($W as $K=>$V){
                if(!is_numeric($V)){//仅允许直接相等的情况下做处理
                    return '禁止该替换逻辑生效';
                }
            }
            foreach ($Data as $K=>$V){
                $Data[$K]=array_merge($V,$W);
            }
            startTrans();
            if(false===M($this->main)->where($W)->delete()){
                rollback();
                return APP_DEBUG?M()->getDbError():'删除失败';
            }
            if(true===($Rs=$this->adds($Data))){
                //成功
                commit();
                return $this->search('',$W);
            }
            return APP_DEBUG?M()->getDbError():$Rs;
        }
        return '错误的数据结构';
    }

    /**
     * 绑定多对多属性到关联表
     * @param string $Property 属性名称
     * @param array $Data 绑定数据
     * @param bool $PKID 主键ID
     * @return array|bool|mixed
     */
    function bind(string $Property,array $Data,$PKID=false){
        if(false===$PKID&&isset($_POST[$this->pk]))$PKID=$_POST[$this->pk];
        if(false==$PKID||!is_numeric($PKID))return '错误的对象编号';
        if(!isset($this->link[$Property]))return '错误的属性名称';
        $PropertyConfig = $this->link[$Property];
        $LinkTableName = $PropertyConfig[self::RELATION_TABLE_NAME];
        $LinkTableHasProperty = isset($PropertyConfig[self::RELATION_TABLE_LINK_HAS_PROPERTY])?$PropertyConfig[self::RELATION_TABLE_LINK_HAS_PROPERTY]:false;
        $LinkTableColumn = $PropertyConfig[self::RELATION_TABLE_COLUMN];
        if(!$LinkTableName||!$LinkTableColumn){
            return '错误的关联配置信息';
        }
        $AddData=[];
        $AddAll=false;
        foreach ($Data as $Key=>$Value){
            $Value[$LinkTableColumn]=$PKID;
            if(is_numeric($Key)){
                //进入批量操作逻辑
                $AddAll=true;
                $AddData[]=$Value;
            }elseif(is_string($Key)&&$AddAll===false){
                $AddData=$Value;
            }
        }
        $Model = M($LinkTableName);
        if($AddAll?$Model->addAll($AddData):$Model->add($AddData)){
            return $this->get($PKID);
        }
        return '绑定失败';
    }

    /**
     * Link表解除绑定
     * @param string $Property
     * @param array $Data
     * @param bool $PKID
     * @return array|bool|mixed|string
     */
    function unbind(string $Property,array $Data,$PKID=false){
        if(false===$PKID&&isset($_POST[$this->pk]))$PKID=$_POST[$this->pk];
        if(false==$PKID||!is_numeric($PKID))return '错误的对象编号';
        if(!isset($this->link[$Property]))return '错误的属性名称';
        $PropertyConfig = $this->link[$Property];
        $LinkTableName = $PropertyConfig[self::RELATION_TABLE_NAME];
        $LinkTableHasProperty = isset($PropertyConfig[self::RELATION_TABLE_LINK_HAS_PROPERTY])?$PropertyConfig[self::RELATION_TABLE_LINK_HAS_PROPERTY]:false;
        $LinkTableColumn = $PropertyConfig[self::RELATION_TABLE_COLUMN];
        if(!$LinkTableName||!$LinkTableColumn){
            return '错误的关联配置信息';
        }
        $Model = M($LinkTableName);
//        startTrans();
        if($Model->where(array_merge($Data,[$LinkTableColumn=>$PKID]))->delete()){
//            commit();
            return $this->get($PKID);
        }
//        rollback();
        return '解除绑定失败';
    }

    function __call($name, $arguments)
    {
        $cmd = explode('_',$name);
        switch ($cmd[0]){
            case 'parent':
                return call_user_func_array([$this,$cmd[1]],$arguments);
                break;
        }
    }
    protected function where($Where){
        return M($this->main)->where($Where)->getField($this->pk,true);
    }

    /**
     * 获取所有对象，当且仅当这个对象被定义成字典对象时可用
     * @return array|bool|null
     */
    function getAll(){
        if($this->is_dic){
            return $this->gets(M($this->main)->getField($this->pk,true));
        }
        return null;
    }

    /**
     * 解析并生成fields字段信息,不能用于add和save操作
     * @param $TableName
     * @param $Config
     * @param bool $Column
     * @return array
     */
    protected function _parseFieldsConfig($TableName,$Config,$Column=false){
        $TableFields=[];
        $UpperTableName = strtoupper(parse_name($TableName));
        $AllFields=[];
        if(is_array($Config)&&0==count($Config))$Config='';
        if(is_array($Config)){
            if(($LastField = array_pop($Config))===true){
                //字段排除
                $Fields = M($TableName)->getDbFields();
                foreach (array_diff($Fields,$Config) as $Field){
                    $TableFields[]="__{$UpperTableName}__.{$Field}";
                }
            }else{
                array_push($Config,$LastField);
                foreach ($Config as $Field){
                    $TableFields[]="__{$UpperTableName}__.{$Field}";
                }
            }
        }elseif(is_string($Config)&&$Config){
            foreach (explode(',',$Config) as $Field){
                $TableFields[]="__{$UpperTableName}__.{$Field}";
            }
        }elseif(is_string($Config)&&strlen($Config)===0){
            foreach ($Fields = M($TableName)->getDbFields() as $Field){
                $TableFields[]="__{$UpperTableName}__.{$Field}";
            }
        }else{
            L('错误的字段配置信息');
        }
        if(!in_array("__{$UpperTableName}__.{$Column}",$TableFields)&&$Column){
            $TableFields[]="__{$UpperTableName}__.{$Column}";
        }
        return $TableFields;
    }

    /**
     * @param $Method
     * @param $Data
     * @return array|string
     */
    protected function _parseChangeFieldsConfig($Method,$Data){
        //获取必填字段，并验证数据，再返回数据
        switch ($Method){
            case 'add':
                $Rules=$this->addFieldsConfig;
                $Fields=$this->addFields;
                break;
            case 'save':
                $Rules=$this->saveFieldsConfig;
                $Fields = $this->saveFields;
                break;
            default:
                $Rules=[];
                $Fields=[];
                break;
        }
        //读取Rule并根据Rule生成数据
//        foreach ($Rules as $Key=>$Config){
//
//        }
        if(is_string($Fields)&&$Fields){
            $Fields = explode(',',$Fields);
        }elseif(is_array($Fields)){
            if(true===end($Fields)){
                array_pop($Fields);
                $Fields = array_diff(M($this->main)->getDbFields(),$Fields);
            }elseif(count($Fields)==0){
                $Fields = M($this->main)->getDbFields();
            }
        }else{
            $Fields = $Fields = M($this->main)->getDbFields();;
        }
        $Fields = array_diff($Fields,[$this->pk]);//去掉PK，在Add和save中不需要用到这个参数
        //释放不必要的参数
//        $Fields = array_keys($this->_fieldMap);
//        foreach (array_diff(array_keys($Data),$Fields) as $Field){
//            unset($Data[$Field]);
//        }
        //开始处理数据、填充及其它规则处理
        foreach ($Rules as $Key=>$Rule){
            foreach ([self::FIELD_CONFIG_VALUE,self::FIELD_CONFIG_VALUE_FUNCTION,self::FIELD_CONFIG_DEFAULT,self::FIELD_CONFIG_DEFAULT_FUNCTION] as $RuleName){
                if(isset($Rule[$RuleName])&&('add'==$Method||('save'==$Method&&isset($Data[$Key])))){
                    //规则存在
                    switch ($RuleName){
                        case self::FIELD_CONFIG_DEFAULT:
                            if(!isset($Data[$Key]))$Data[$Key]=$Rule[$RuleName];
                            break;
                        case self::FIELD_CONFIG_DEFAULT_FUNCTION:
                            if(!isset($Data[$Key]))$this->_execFieldFunctionConfig($Data,$Key,$Rule[$RuleName]);
                            break;
                        case self::FIELD_CONFIG_VALUE:
                            $Data[$Key]=$Rule[$RuleName];
                            break;
                        case self::FIELD_CONFIG_VALUE_FUNCTION:
                            $this->_execFieldFunctionConfig($Data,$Key,$Rule[$RuleName]);
                            break;
                        default:
                            L('无法识别的字段限定配置:'.$Rule);
                            break;
                    }
                }
            }
        }
        if('add'==$Method&&array_diff($Fields,array_keys($Data))){
            $NotExistFields=[];
            foreach (M($this->main)->getDbFields(null,true) as $ColumnName=>$Conf){
                if(is_array($Conf)&&$Conf['default']===null&&$Conf['autonic']!=true&&$Conf['notnull']===true){
                    $NotExistFields[]=$ColumnName;
                }
            }
            //TODO 判断数据库必填选项，若数据库必填且默认值不存在的情况下返回字段不存在
            return $NotExistFields?L('如下字段不存在:'.implode(',',$NotExistFields)):$Data;
        }
        return $Data;
        //暂时直接从POST中取有效数据返回
    }
    protected function _verifyData(&$Data,$Rule){

    }

    /**
     * 执行Function的字段配置
     * @param $Data
     * @param $Key
     * @param $Rule
     */
    protected function _execFieldFunctionConfig(&$Data,$Key,$Rule){
        if(is_callable($Rule)){
            $Data[$Key]=call_user_func($Rule);
        }else
//        if(is_array($Rule)){
//            //如果是数组，
//            //有可能是[$this,'callback']的。。。不好说。。。所以先把is_callable放在前面
//            if(!is_callable($Rule[0])){
//                L("{$Rule[0]} 不是可调用函数");
//            }
//            switch (count($Rule)){
//                case 2:
//                    if(is_string($Rule[1])){
//                        $Rule[1] = explode(',',$Rule[1]);
//                    }
//                    if(is_array($Rule[1])){
//                        //参数部分是数组，寻找值为   的变量并替换成当前值
//                        str_replace(self::FIELD_CONFIG_CALLBACK_REPLACE_STR,)
//                    }
//                    break;
//            }
//        }else
        if(is_string($Rule)){
            if('unset'==$Rule){
                unset($Data[$Key]);
            }else
            if(is_callable($Rule)){
//                'time';
                $Data[$Key]=call_user_func($Rule);
            }elseif('$'==substr($Rule,0,1)){
//                取变量
//                $_POST['UID'];
                $Data[$Key]=eval($Rule);
            }elseif(preg_match('/^[a-zA-Z\d]+\([\$a-zA-Z\d,\'"]+\)$/',$Rule)){
                //session('UID')
                $Rule = str_replace(['\'','"',')'],'',$Rule);
                list($FunctionName,$Params)=explode('(',$Rule);
                $Params = $Params?explode(',',$Params):[];
                if(is_callable($FunctionName))
                    $Data[$Key]=call_user_func_array($FunctionName,$Params);
                else
                    L('错误的字段值回调函数配置:'.$Key.':'.$FunctionName);
            }else{
                L('无法识别的字段限定配置:'.$Rule);
            }
        }else{
            L('无法识别的字段限定配置:'.$Rule);
        }
    }
    /**
     *
     * @param mixed $Data 数据
     * @param string $Key 字段名称
     * @param array $Rule 规则
     */
    protected function _verifyFieldsConfig(&$Data,$Method){
        //按照规则优先级遍历
        switch ($Method){
            case 'add':$Rule=$this->addFieldsConfig;break;
            case 'save':$Rule=$this->saveFieldsConfig;break;
            default:$Rule=[];break;
        }
        foreach ($Data as $Key=>$Value){
            foreach ([self::FIELD_CONFIG_VALUE,self::FIELD_CONFIG_DEFAULT] as $RuleName){
                if(isset($Rule[$RuleName])){
                    //规则存在
                    switch ($RuleName){
                        case self::FIELD_CONFIG_DEFAULT:
//                            if(!isset($Data[$Key]))$Data[$Key]=
                                break;
                        case self::FIELD_CONFIG_DEFAULT_FUNCTION:

                            break;
                        case self::FIELD_CONFIG_VALUE:

                            break;
                        case self::FIELD_CONFIG_VALUE_FUNCTION:

                            break;
                    }
                }
            }
        }
    }

    /**
     * 在gets的select后触发
     * @param $Objects
     */
    protected function _afterObjectsGetsSelect(&$Objects){}

    /**
     * 在foreach中被触发，用于添加逻辑字段
     * @param $Objects
     * @param $ObjectID
     */
    protected function _foreachObjectGets(&$Objects,$ObjectID){}

    /**
     * 在返回前触发，用于输出过滤
     * @param $Objects
     */
    protected function _beforeObjectGetsReturn(&$Objects){}

    /**
     * 在开始foreach之前触发，
     * @param $Objects
     * @param $ArrayProperties
     * @param $OneProperties
     * @param $ArrayObjectProperties
     * @param $OneObjectProperties
     * @param $LinkPropertyValues
     * @param $PropertyObjects
     */
    protected function _beforeObjectGetsForeach(&$Objects,&$ArrayProperties,&$OneProperties,&$ArrayObjectProperties,&$OneObjectProperties,&$LinkPropertyValues,&$PropertyObjects){}
}