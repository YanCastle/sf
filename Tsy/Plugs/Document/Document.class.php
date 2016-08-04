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
        'Classes'=>[],
        'Functions'=>[],
        'Objects'=>[],
        'ObjectMap'=>[]
    ];
    function loadPDM($File){
        $JSON = \Tsy\Plugs\PowerDesigner\PowerDesigner::analysis($File);
        $Tables=[];
        foreach ($JSON['Tables'] as $k=>$table){
            $Tables[str_replace('{$PREFIX}','',$k)]=$table;
        }
        $JSON['Tables']=$Tables;
        self::$docs['PDM']=$JSON;
    }
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
    function getDoc($name='',$MethodsAccess=['private','protected','public']){
//        self::$docs=[
//            'Classes'=>[
//                '完整类名'=>[
//                    'memo'=>'类说明',
//                    'type'=>'',//这个类是什么类型，控制器？Model？Object？其他？
//                    'properties'=>[
//                        '属性名称'=>[
//                            'name'=>'属性名称',
//                            'zh'=>'中文名称',
//                            'access'=>'public/protected/private',//三选一
//                            'memo'=>'属性备注',
//                            'type'=>'属性类型'
//                        ]
//                    ],
//                    'methods'=>[
//                        '方法名称'=>[
//                            'params'=>[ //参数列表
//                                '参数名称'=>[
//                                    'name'=>'参数名称',
//                                    'zh'=>'中文名称',
//                                    'memo'=>'参数备注',
//                                    'type'=>'参数类型',
//                                    'default'=>'参数默认值'
//                                ]
//                            ],
//                            'login'=>true,//是否需要登录
//                            'name'=>'方法名称',
//                            'zh'=>'方法中文名',
//                            'access'=>'访问性',
//                            'memo'=>'注释',
//                            'author'=>'作者信息',
//                            'link'=>'帮助信息链接地址',
//                            'return'=>[//返回类型
//
//                            ]
//                        ]
//                    ]
//                ]
//            ],
//            'Functions'=>[
//                '函数名称'=>[
//                    'params'=>[ //参数列表
//                        '参数名称'=>[
//                            'name'=>'参数名称',
//                            'zh'=>'中文名称',
//                            'memo'=>'参数备注',
//                            'type'=>'参数类型',
//                            'default'=>'参数默认值'
//                        ]
//                    ],
//                    'name'=>'方法名称',
//                    'zh'=>'方法中文名',
//                    'memo'=>'注释',
//                    'author'=>'作者信息',
//                    'link'=>'帮助信息链接地址',
//                    'return'=>[//返回类型
//
//                    ]
//                ]
//            ]
//        ];
        if(is_string($name)){
            if(is_object($name)){
                $this->parseClass($name,$MethodsAccess);
            }elseif(class_exists($name)){
                $class = \Tsy\Tsy::instance($name);
                $this->parseClass($class,$MethodsAccess);
            }elseif(function_exists($name)){

            }elseif(is_dir($name)){
//                each_dir() 遍历循环

            }elseif(is_file($name)){

            }else{
                return false;
            }
        }elseif(is_object($name)){
            $this->parseClass($name,$MethodsAccess);
        }
        return false;
    }
    function parseClass($class,$MethodsAccess){
//        判断类是否是Controller/Object/Model中的一种，如果是则调用对应类型的解析方法
        $RelClass = new ReflectionClass($class);
        $ClassType = '';
        foreach (['Tsy\Library\Object','Tsy\Library\Model','Tsy\Library\Controller'] as $InsideClass){
            if($RelClass->isSubclassOf($InsideClass)){
                $ClassType = str_replace('Tsy\Library\\','',$InsideClass);
            }
        }
        switch ($ClassType){
            case 'Object':
                $this->parseObject($RelClass,$MethodsAccess);
                break;
            case 'Controller':
                $this->parseController($RelClass,$MethodsAccess);
                break;
            case 'Model':
                $this->parseModel($RelClass,$MethodsAccess);
                break;
            default:
                self::$docs['Classes'][$RelClass->getName()]=array_merge([
                    'memo'=>'',
                    'zh'=>'',
                    'name'=>'',
                    'type'=>'',//这个类是什么类型，控制器？Model？Object？其他？
                    'properties'=>[],
                    'methods'=>[]
                ],$this->parseDocComment($RelClass->getDocComment(),null,$RelClass));
//        foreach ($RelClass->getProperties() as $property){
//
//        }
                //开始解析方法注释
                $methods = [];
                foreach ($RelClass->getMethods() as $method){
//            $method->isPrivate() or $access =
                    $access = 'public';
                    if($method->isPrivate()){$access='private';}
                    if($method->isProtected()){$access='protected';}
                    if($method->isPublic()){$access='public';}
//            限定输出的方法范围
                    if(!in_array($access,$MethodsAccess)){continue;}
                    $methods[$method->getName()]=array_merge([
                        'name'=>$method->getName(),'access'=>$access,'static'=>$method->isStatic()
                    ],$this->parseDocComment($method->getDocComment(),$method));
                }
                self::$docs['Classes'][$RelClass->getName()]['methods']=$methods;
                break;
        }

        return $this;
    }
    protected function parseDocComment($Comment,$Method=null,ReflectionClass $class=null){
        $Comment = str_replace(['/**','*/'," * "],'' ,$Comment);
        $Comment = str_replace("\r\n","\n" ,$Comment );
        $Comment = trim($Comment,"\n");
        $Comment = explode("\n",$Comment );
        $data=['memo'=>[],'params'=>[]];
        $matched=false;
        foreach ($Comment as $line=>$content){
            $content = trim($content);
            if(preg_match('/^@[a-z]+[ .]+/',$content,$match)){
//                这是标签内容
                $fields = explode(' ',$content );
                $key = str_replace('@','' ,$fields[0] );
                if(in_array($key,['package','link','author','version','access','login'] )){
                    $data[$key]=$fields[1];
                }else{
                    switch ($key){
                        case 'param':
                            $count = count($fields);
                            if($count>=5){
                                $param=[
                                    'type'=>$fields[1],
                                    'name'=>$fields[2],
                                    'zh'=>$fields[3],
                                    'memo'=>substr($content,strpos($content, $fields[4]))
                                ];
                            }elseif($count==4){
                                $param=[
                                    'type'=>$fields[1],
                                    'name'=>$fields[2],
                                    'zh'=>$fields[3],
                                    'memo'=>''
                                ];
                            }elseif($count==3){
                                if(substr($fields[1],0,1 )=='$'){
                                    //变量名称
                                    $param=[
                                        'type'=>'',
                                        'name'=>$fields[1],
                                        'zh'=>$fields[2],
                                        'memo'=>''
                                    ];
                                }else{
                                    //变量类型
                                    $param=[
                                        'type'=>$fields[1],
                                        'name'=>$fields[2],
                                        'zh'=>$fields[2],
                                        'memo'=>''
                                    ];
                                }
                            }elseif($count==2){
                                $param=[
                                    'type'=>'',
                                    'name'=>$fields[1],
                                    'zh'=>$fields[1],
                                    'memo'=>''
                                ];
                            }else{
                                $param=[];
                            }
                            $data['params'][]=$param;
                            break;
                        case 'example':

                            break;
                        case 'return':
                            unset($fields[0]);
                            $data['return']=implode(' ',$fields );
                            break;
                    }
                }
            }elseif($content){
                if($line===0){
                    $data['zh']=trim($content);
                }elseif($line>0&&!$matched){
                    $data['memo'][]=trim($content);
                }
            }
        }
        if($data['memo']){
            $data['memo'] = implode("\r\n",$data['memo'] );
        }else{
            unset($data['memo']);
        }
        if($data['params']){
            $data['params'] = array_key_set($data['params'],'name');
        }
        //开始做反射检测
        if($Method instanceof ReflectionMethod){
            $data['name']=$Method->getName();
            foreach ($Method->getParameters() as $parameter){
                $name = '$'.$parameter->getName();
                $param=[
                    'name'=>$name,
                    'must'=>!$parameter->isOptional(),
                    'default'=>$parameter->isOptional()?$parameter->getDefaultValue():'',
                    'pos'=>$parameter->getPosition()
                ];
                $data['params'][$name] = isset($data['params'][$name])?(array_merge($data['params'][$name],$param)):array_merge([
                    'type'=>'',
                    'name'=>$name,
                    'zh'=>$name,
                    'memo'=>''
                ], $param);
            }
        }elseif($Method instanceof ReflectionFunction){
            $data['name']=$Method->getName();
            foreach ($Method->getParameters() as $parameter){
                $name = '$'.$parameter->getName();
                $param=[
                    'name'=>$name,
                    'must'=>!$parameter->isOptional(),
                    'default'=>$parameter->isOptional()?$parameter->getDefaultValue():'',
                    'pos'=>$parameter->getPosition()
                ];
                $data['params'][$name] = isset($data['params'][$name])?(array_merge($data['params'][$name],$param)):array_merge([
                    'type'=>'',
                    'name'=>$name,
                    'zh'=>$name,
                    'memo'=>''
                ], $param);
            }
        }else{

        }
        if($class instanceof ReflectionClass){
            $data['name']=$class->getName();
            $data['namespace']=$class->getNamespaceName();
            
        }
        return $data;
    }

    /**
     * 解析对象文档
     * @param $RefClass
     * @param $MethodsAccess
     */
    function parseObject(ReflectionClass $RefClass,array $MethodsAccess){
        $ClassName = $RefClass->getName();
        self::$docs['Classes'][$ClassName]=array_merge([
            'memo'=>'',
            'zh'=>'',
            'name'=>'',
            'type'=>'Object',//这个类是什么类型，控制器？Model？Object？其他？
            'Properties'=>[],
            'methods'=>[],
            'Object'=>[]
        ],$this->parseDocComment($RefClass->getDocComment(),null,$RefClass));
        $Class = $RefClass->newInstance();
//        读取属性渲染对象化配置
        $Properties = $RefClass->getProperties();
        $Object=[];
        $ObjectColumns=[];
        $ObjectSetting=[];
        foreach ($Properties as $Property){
            switch ($Property->getName()){
                case 'main':
//                    主表
//                    读取fields属性，检查是否有值
                    $ObjectSetting['main']=$TableName=parse_name($Class->main,1);
                    $Fields=[];
                    $Values = $Class->fields;
                    if(is_string($Values)&&$Values){
                        $Fields=explode(',',$Fields);
                    }elseif($Values&&is_array($Values)){
                        if(true === ($LastValue = array_shift($Values))){
//                            取差集

                        }else{
                            array_push($Values,$LastValue);
                        }

                    }else{
                        //TODO DB Fields需要优化
                        $Table = isset(self::$docs['PDM']['Tables'][parse_name($TableName)])?self::$docs['PDM']['Tables'][parse_name($TableName)]:[];
                        $Fields = isset($Table['Columns'])?array_keys($Table['Columns']):M($TableName)->getDbFields();
                    }
                    //生成数据对象
                    $Object=array_merge($Object,array_fill_keys($Fields,1));
                    foreach (self::$docs['PDM']['Tables'][parse_name($TableName)]['Columns'] as $ColumnName=>$column){
                        if(in_array($ColumnName,$Fields))
                            $ObjectColumns[$ColumnName]=$column;
                    }
                    break;
                case 'pk':
                    $ObjectSetting['pk']=$Class->pk;
//                    主键
                    break;
                case 'property':
//                    一对一、一对多属性
                    $ObjectSetting['property']=$ObjectProperties=$Class->property;

                    foreach ($ObjectProperties as $PropertyName=>$ObjectProperty){
                        if(isset($ObjectProperty[Tsy\Library\Object::RELATION_TABLE_NAME])){
                            //表映射
                            $TableName=$ObjectProperty[Tsy\Library\Object::RELATION_TABLE_NAME];
                            $Fields=[];
                            $Values = isset($ObjectProperty[Tsy\Library\Object::RELATION_TABLE_FIELDS])?$ObjectProperty[Tsy\Library\Object::RELATION_TABLE_FIELDS]:'';
                            if(is_string($Values)&&$Values){
                                $Fields=explode(',',$Fields);
                            }elseif($Values&&is_array($Values)){
                                if(true === ($LastValue = array_shift($Values))){
//                            取差集

                                }else{
                                    array_push($Values,$LastValue);
                                }

                            }else{
                                //TODO DB Fields需要优化
                                $Fields = isset(self::$docs['PDM']['Tables'][parse_name($TableName)])?array_column(self::$docs['PDM']['Tables'][parse_name($TableName)]['Columns'],'Code'):M($TableName)->getDbFields();
                            }
                            //生成数据对象
                            $ColumnPrifix='';
                            if($ObjectProperty[Tsy\Library\Object::RELATION_TABLE_PROPERTY]==Tsy\Library\Object::PROPERTY_ONE)
                                $Object=array_merge($Object,array_fill_keys($Fields,1));
                            else{
                                $ColumnPrifix=$PropertyName;
                                $Object[$PropertyName]=array_fill_keys($Fields,1);
                            }
                            foreach (self::$docs['PDM']['Tables'][parse_name($TableName)]['Columns'] as $ColumnName=>$column){
                                if(in_array($ColumnName,$Fields))
                                    $ObjectColumns[($ColumnPrifix?($ColumnPrifix.'.'):'').$ColumnName]=$column;
                            }
                        }else
                        if(isset($ObjectProperty[Tsy\Library\Object::RELATION_OBJECT_NAME])){
                            //对象映射
//                            $TableName=$ObjectProperty[Tsy\Library\Object::RELATION_OBJECT_NAME];
                            self::$docs['ObjectMap'][$ObjectProperty[Tsy\Library\Object::RELATION_OBJECT_NAME]] = $ObjectProperty;
                        }
                    }
                    break;
                case 'link':
//                    多对多关联
                    $ObjectSetting['link']=$Links = $Class->link;
                    foreach ($Links as $PropertyName=>$PropertyConfig){
                        $Fields=[];
                        if($PropertyConfig[\Tsy\Library\Object::RELATION_TABLE_LINK_HAS_PROPERTY]){
                            //关联表带属性，需要把相关关联表字段带入到输出结果中
                            foreach (self::$docs['PDM']['Tables'][parse_name($PropertyConfig[\Tsy\Library\Object::RELATION_TABLE_NAME])]['Columns'] as $ColumnName=>$column){
                                $ObjectColumns[($PropertyName?($PropertyName.'.'):'').$ColumnName]=$column;
                                $Fields[]=$ColumnName;
                            }
                        }else{

                        }
                        //循环处理
                        foreach ($PropertyConfig[\Tsy\Library\Object::RELATION_TABLE_LINK_TABLES] as $TableName=>$Config){
//                            读取字段
                            foreach (self::$docs['PDM']['Tables'][parse_name($TableName)]['Columns'] as $ColumnName=>$column){
                                $ObjectColumns[$PropertyName.'.'.$ColumnName]=$column;
                                $Fields[]=$ColumnName;
                            }
                        }
                        $Object[$PropertyName]=array_fill_keys($Fields,1);
                    }
                    break;
                case 'searchFields':
//                    限定Keyword搜索的
                    $ObjectSetting['searchFields']=$Class->searchFields;
                    break;
                case 'searchTable':
//                    限定Keyword的搜索表
                    $ObjectSetting['searchTable']=$Class->searchTable;
                    break;
                case 'searchWFieldsConf':
//                    设定分组精确搜索的表配置
                    $ObjectSetting['searchWFieldsConf']=$Class->searchWFieldsConf;
                    break;
                case 'searchWFieldsGroup':
//                    设定分组精确搜索的字段配置
                    $ObjectSetting['searchWFieldsGroup']=$Class->searchWFieldsGroup;
                    break;
                case 'allow_add':
//                    是否允许添加
                    $ObjectSetting['allow_add']=$Class->allow_add;
                    break;
                case 'allow_save':
                    $ObjectSetting['allow_save']=$Class->allow_save;
                    break;
                case 'allow_del':
                    $ObjectSetting['allow_del']=$Class->allow_del;
                    break;
                case 'is_dic':
//                    是否字典表
                    $ObjectSetting['is_dic']=$Class->is_dic;
                    break;
                case 'map':break;
                default:break;
            }
        }
        $Result = [
            'Object'=>$Object,
            'ObjectName'=>self::$docs['PDM']['Tables'][parse_name($ObjectSetting['main'])]['Name'],
            'ObjectJSON'=>json_format($Object),
            'ObjectSetting'=>$ObjectSetting,
            'ObjectColumns'=>$ObjectColumns
        ];
        self::$docs['Classes'][$ClassName]=array_merge(self::$docs['Classes'][$ClassName],$Result);
        self::$docs['Objects'][$ClassName]=$Result;
    }
    /**
     * @login true
     *
     */
    function renderMD($outputFile='',$templateFile=''){
        $View = new \Tsy\Library\View();
        $View->assign(self::$docs);
        $View->assign('line',"\r\n");
        $content = $View->fetch(is_file($templateFile)?$templateFile:(__DIR__.DIRECTORY_SEPARATOR.'Template'.DIRECTORY_SEPARATOR.'render.html'));
        $content = str_replace('{$PREFIX}','',$content);
        if($outputFile){
            file_put_contents($outputFile,$content);
        }
        return $content;
    }
    function renderHTML(){}
    function renderDOC(){}
    function renderUML(){}
    function renderXLS(){}
    function renderCSV(){}
}