<?php
/**
 * Created by PhpStorm.
 * User: castle
 * Date: 2016/4/18
 * Time: 8:05
 */

namespace Tsy\Library;

use Tsy\Plugs\Db\Db;

class Object
{
    const PROPERTY_ONE = "00"; //一对一属性配置，
    const PROPERTY_ARRAY = "01"; //一对多属性配置

//    const PROPERTY_OBJECT = "02";

    const RELATION_TABLE_NAME = "03"; //关系表名称
    const RELATION_TABLE_COLUMN = "04"; //关系表字段
    const RELATION_TABLE_PROPERTY = "05"; //关系类型， 上面的一对多或者一对一
    const RELATION_TABLE_LINK_HAS_PROPERTY = "06"; // 多对多配置中是否具有属性
    const RELATION_TABLE_LINK_TABLES = "07"; //多对多属性的连接表表名
    const RELATION_OBJECT = "08"; //映射关系对象
    const RELATION_OBJECT_NAME = "09"; //映射关系对象名称
    const RELATION_OBJECT_COLUMN = "10"; //映射关系对象字段

    protected $main = '';//主表名称，默认为类名部分
    protected $pk = '';//表主键，默认自动获取
    protected $link = [];//多对多属性配置
    protected $property = [];//一对一或一对多属性配置
    protected $data = [];//添加、修改时的数据
    protected $searchFields = [];//参与Keywords搜索的字段列表
    protected $searchTable='';
    protected $propertyMap = [];//属性配置反向映射
    protected $object = [];//对象化属性配置，一个对象中嵌套另一个属性的配置情况
    protected $_write_filter = [];//输入写入过滤配置
    protected $_read_filter = [];//输入读取过滤配置
    protected $_read_deny=[];//禁止读取字段

    protected $searchWFieldsGroup=[
//        'GroupName'=>['Name','Number','BarCode','Standard','PinYin','Memo']
    ];
    protected $searchWFieldsConf=[

    ];
    
    public $is_dic=false;
    public $allow_add=true;//是否允许添加
    public $allow_save=true;//是否允许修改
    public $allow_del=true;//是否允许删除
    public $map = [
//        自动生成
    ];//字段=》类型 表名 映射
    protected $__CLASS__;
    protected $MC=[];
    function __construct()
    {
        //检测是否存在属性映射，如果存在则直接读取属性映射，没有则从数据库加载属性映射
//        提取数据库字段，合并到map中
        $this->__CLASS__ = get_class($this);
        $this->MC = explode('\\\\',str_replace(['Controller','Object','Model'],'' ,$this->__CLASS__ ) );
        if (!$this->main) {
            $this->main = $this->getObjectName();
        }
        if (APP_DEBUG) {
            $this->setMapByColumns();
        } else {
            if ($CachedMap = cache('ObjectMap' . $this->main)) {
                //有缓存存在的情况下
                $this->map = array_merge($CachedMap, $this->map);
            } else {
//                没有缓存存在的情况下先获取缓存然后再缓存
                $this->setMapByColumns();
            }
        }
        $this->setPropertyMap();
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
        $Model = new Db();
        $Columns = $Model->getColumns($tables, true);
        //生成map结构并缓存
        foreach ($Columns as $TableName => $column) {
//            解析并生成格式限制和转化配置
            foreach ($column as $item) {
                $type = explode(',', str_replace(['(', ')', ' '], ',', $item['type']));
                $this->map[$TableName . '.' . $item['field']] = [
                    'U' => strpos($item['type'], 'unsigned') > 0,//是否无符号
                    'T' => count($type) == 1 ? $type : [$type[0], $type[1]],//数据库类型
                    'D' => $item['default'],//默认值
                    'P' => 'PRI' == $item['key'],//是否主键
                    'N' => 'YES' == $item['null'],//是否为null
                    'A' => 'auto_increment' == $item['extra']//是否自增
                ];
                if (!$this->pk &&
                    'PRI' == $item['key']
                ) {
                    $this->pk = $item['field'];
                }
            }
        }
        cache('ObjectMap' . $this->main, $this->map);
    }

    /**
     * 得到当前的数据对象名称
     * @access public
     * @return string
     */
    public function getObjectName()
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

    function add()
    {
//        此处自动读取属性并判断是否是必填属性，如果是必填属性且无。。。则。。。
        if(!$this->allow_add)return false;
        $data = [];
        foreach ($this->map as $key => $map) {
            $TableName = explode('.', $key)[0];
//            TODO 检测外检并实现自动添加等逻辑
            if($TableName==parse_name($this->main)){
                $column = explode('.', $key)[1];
                if (!$data[$TableName]['PK']) {
                    $data[$TableName]['PK'] = $map['P'] === true ? $column : '';
                }
                if (isset($_POST[$column])) {
//                if (!call_user_func($map['T'][0], $_POST[$column])) {
//                    return $column . '参数类型错误';
//                }
//                if (count($_POST[$column]) >= $map['T'][1]) {
//                    return $column . '参数过长';
//                }
                    $data[$TableName]['data'][$column] = $_POST[$column];
                } else {
                    if (true === $map['N'] &&
                        $map['P'] === false
                    ) {
                        //TODO 有外键时，数据初始化的问题
//                    return $column.'参数不完整';
                    }
                    if (true === $map['P']) {
                        continue;
                    }
//                $data[$TableName]['data'][$column] = $_POST[$column];
                }
            }
        }
        if (!$data) {
            return '没有传参数或参数错误';
        }
        foreach ($data as $d) {
            if (array_filter($d) == []) {
                return $d . '未传入任何参数';
            }
        }
        startTrans();
        $RS=[];
        $PKID=false;
        foreach ($data as $key => $Data) {
            $Model = M($key);
            if($this->main=$key){
                $RS[] = $PKID = $Model->add($Data['data']);
            }else{
                $RS[] = $Model->add($Data['data']);
            }
        }
        foreach ($RS as $v){
            if($v===false){
                rollback();
                return false;
            }
        }
        commit();
        return $this->get($PKID);
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
    protected function searchW(string $TableName,array $Where,string $Field){
        return M($TableName)->where($Where)->getField($Field,true);
    }

    /**
     * @param string $Keyword
     * @param array $W
     * @param string $Sort
     */
    function search($Keyword = '', $W = [], $Sort = '', $P = 1, $N = 20,$Properties=false)
    {
        $Model = new Model($this->searchTable ? $this->searchTable : $this->main);
        $DB_PREFIX = C('DB_PREFIX');
        $ObjectIDs = false;
        $FieldPrefix = $DB_PREFIX . strtolower($this->main) . '.';
        $Tables = ['__' . strtoupper($this->main) . '__'];
        $ObjectSearchConfig = [];
        $Where = [];
        $WObjectIDArray = [];
        $KeywordObjectIDs=[];
        if ((is_string($Keyword) || is_numeric($Keyword)) &&
            strlen($Keyword) > 0 && $this->searchFields
        ) {
            foreach ($this->searchFields as $Filed) {
                $Where[$Filed] = ['LIKE', '%' . str_replace([' ', ';', "\r\n"], '', $Keyword) . '%'];
            }
            $Where['_logic'] = 'OR';
            $Model->where($Where);
            $KeywordObjectIDs = $Model->getField($this->pk, true);
        }
        if ($W) {
            $Data = param_group($this->searchWFieldsGroup, $W);
            unset($Data[0]);
            foreach ($Data as $ObjectName => $Params) {
                $a = isset($this->searchWFieldsConf[$ObjectName]);
                if (isset($this->searchWFieldsConf[$ObjectName])) {
                    //如果是一个字符串就直接当表名使用，否则检测是否是回调函数，如果是回调函数则回调，如果不是则空余并给出警告
                    if (is_string($this->searchWFieldsConf[$ObjectName]) && preg_match('/^[a-z_A-Z]+[a-zA-Z]$/', $this->searchWFieldsConf[$ObjectName])) {
                        //直接值为表名
                        $WObjectIDArray[] = $this->searchW($this->searchWFieldsConf[$ObjectName], $Params, $this->pk);
                    } elseif (is_callable($this->searchWFieldsConf[$ObjectName])) {
                        //回调
                        $Result = call_user_func($this->searchWFieldsConf[$ObjectName], $Params);
                        if (is_string($Result) && preg_match('/^[a-z_]+[a-z]$/', $Result)) {
                            //吧这个当作表名，再参与上一个逻辑
                            $WObjectIDArray[] = $this->searchW($Result, $Params, $this->pk);
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
                    L(E('_NO_SEARCH_TABLE_CONFIG_'));
                }
            }
        }
        if($WObjectIDArray){
            $ObjectIDs=array_unique(call_user_func_array('array_merge',$WObjectIDArray));
        }
        //取交集
        if(strlen($Keyword))
            $ObjectIDs = array_intersect($ObjectIDs,$KeywordObjectIDs);
        if (strlen($Keyword) === 0 && count($W) === 0) {
            $ObjectIDs = $Model->page($P, $N)->getField($this->pk, true);
            return [
                'L' => array_values($this->gets($ObjectIDs)),
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
        $Objects = isset($PageIDs[$P - 1]) ? $this->gets($PageIDs[$P - 1], $Properties) : [];
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
        return M($this->main)->where([$this->pk => ['IN', $IDs]])->delete();
    }

    /**
     * 获取多个对象属性
     * @param array|int $IDs 主键字段编号值
     * @return array|bool
     */
    function gets($IDs=[],$Properties=false)
    {
        !(false===$Properties&&isset($_POST['Properties'])) or $Properties=$_POST['Properties'];
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
        $PropertyObjects = [];
        $UpperMainTable = strtoupper(parse_name($this->main));
        $Model = M($this->main);
        $ArrayProperties = [];
        foreach ($this->property as $PropertyName => $Config) {
//            如果设定了获取的属性限定范围且该属性没有在该范围内则跳过
            if(is_array($Properties)&&!in_array($PropertyName,$Properties))continue;
            if (isset($Config[self::RELATION_TABLE_PROPERTY]) &&
                isset($Config[self::RELATION_TABLE_NAME]) &&
                isset($Config[self::RELATION_TABLE_COLUMN])
            ) {
                if ($Config[self::RELATION_TABLE_PROPERTY] == self::PROPERTY_ONE) {
                    //一对一属性
//                    TODO 字段映射
                    $TableName = strtoupper(parse_name($Config[self::RELATION_TABLE_NAME]));
                    $TableColumn = $Config[self::RELATION_TABLE_COLUMN];
                    $Model->join("__{$TableName}__ ON __{$UpperMainTable}__.{$TableColumn} = __{$TableName}__.{$TableColumn}", 'LEFT');
                } else {
                    //一对多
                    $ArrayProperties[$PropertyName] = $Config;
                }
            } elseif (
//                isset($Config[self::RELATION_TABLE_PROPERTY]) &&
                isset($Config[self::RELATION_OBJECT]) &&
                true === $Config[self::RELATION_OBJECT] &&
                isset($Config[self::RELATION_OBJECT_COLUMN]) &&
                isset($Config[self::RELATION_OBJECT_NAME])
            ) {
                $PropertyObjects[$PropertyName] = $Config;
            }
        }
        if($this->_read_deny){
            $Model->field($this->_read_deny, true);
        }
//        "SELECT A,B,C FROM A,B ON A.A=B.A WHERE"
        $Objects = $Model->where(["__{$UpperMainTable}__.".$this->pk => ['IN', $IDs]])->select();
        if (!$Objects) {
            return [];
        }
        //处理一对多的情况
        $ArrayPropertyValues = [];
        foreach ($ArrayProperties as $PropertyName => $Config) {
            //            如果设定了获取的属性限定范围且该属性没有在该范围内则跳过
            if(is_array($Properties)&&!in_array($PropertyName,$Properties))continue;
            $ArrayPropertyValues[$PropertyName] = array_key_set(M($Config[self::RELATION_TABLE_NAME])->where([$Config[self::RELATION_TABLE_COLUMN] => ['IN', array_column($Objects, $Config[self::RELATION_TABLE_COLUMN])]])->select(), $Config[self::RELATION_TABLE_COLUMN], true);
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
                $LinkModel = M($Config[self::RELATION_TABLE_NAME])->where(
                    [
                        $Config[self::RELATION_TABLE_COLUMN] => ['IN', array_column($Objects, $Config[self::RELATION_TABLE_COLUMN])]
                    ]
                );
                $UpperJoinTable = strtoupper(parse_name($Config[self::RELATION_TABLE_NAME]));
//                TODO Link表中的多对多关系先忽略不计
                foreach ($Config[self::RELATION_TABLE_LINK_TABLES] as $TableName => $Conf) {
                    $TableName = strtoupper(parse_name($TableName));
                    $TableColumn = $Conf[self::RELATION_TABLE_COLUMN];
                    $LinkModel->join("__{$TableName}__ ON __{$UpperJoinTable}__.{$TableColumn} = __{$TableName}__.{$TableColumn}", 'LEFT');
                }
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
                    //TODO 当前模块下的。。
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
        foreach ($Objects as $ID => $Object) {
//            处理一对多关系
            foreach ($ArrayProperties as $PropertyName => $PropertyConfig) {
                $Objects[$ID][$PropertyName] = isset($ArrayPropertyValues[$PropertyName][$Object[$PropertyConfig[self::RELATION_TABLE_COLUMN]]]) ? $ArrayPropertyValues[$PropertyName][$Object[$PropertyConfig[self::RELATION_TABLE_COLUMN]]] : [];
            }
//            处理多对多关系
            foreach ($this->link as $PropertyName => $PropertyConfig) {
                $Objects[$ID][$PropertyName] = isset($LinkPropertyValues[$PropertyName][$Object[$PropertyConfig[self::RELATION_TABLE_COLUMN]]]) ? $LinkPropertyValues[$PropertyName][$Object[$PropertyConfig[self::RELATION_TABLE_COLUMN]]] : [];
            }
//            处理Object配置
            foreach ($PropertyObjects as $Key => $Config) {
                $Objects[$ID][$Key] = isset($PropertyObjectValues[$Key][$Object[$Config[self::RELATION_OBJECT_COLUMN]]]) ? $PropertyObjectValues[$Key][$Object[$Config[self::RELATION_OBJECT_COLUMN]]] : [];
            }
        }
        krsort($Objects);
        return $Objects;
    }

    function save($ID,$Params)
    {
        if(!$this->allow_save)return false;
        $Where=[];
        if(is_array($ID)){
            foreach ($ID as $v){
                if(!is_numeric($v)){
                    L(E('_SAVE_ID_'));
                    return false;
                }
            }
            $Where[$this->pk]=['IN',$ID];
        }elseif (is_numeric($ID)){
            $Where[$this->pk]=$ID;
        }else{
            L(E('_SAVE_ID_'));
        }
        if($Params&&is_array($Params)){
            return M($this->main)->where($Where)->save($Params)!==false;
        }else{
            L(E('_SAVE_DATA_'));
        }
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
    function where($Where){
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
}