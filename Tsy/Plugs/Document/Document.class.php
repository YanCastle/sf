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
    function getDoc($name=''){
//        self::$docs=[
//            'classes'=>[
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
//            'functions'=>[
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
                $this->parseClass($name);
            }elseif(class_exists($name)){
                $class = \Tsy\Tsy::instance($name);
                $this->parseClass($class);
            }elseif(function_exists($name)){

            }elseif(is_dir($name)){
//                each_dir() 遍历循环
            }elseif(is_file($name)){

            }else{
                return false;
            }
        }
        return false;
    }
    function parseClass($class){
        $RelClass = new ReflectionClass($class);
        self::$docs['classes'][$RelClass->getName()]=array_merge([
            'memo'=>'',
            'zh'=>'',
            'name'=>'',
            'type'=>'',//这个类是什么类型，控制器？Model？Object？其他？
            'properties'=>[],
            'methods'=>[]
        ],$this->parseDocComment($RelClass->getDocComment()));
//        foreach ($RelClass->getProperties() as $property){
//
//        }
        $methods = [];
        foreach ($RelClass->getMethods() as $method){
//            $method->isPrivate() or $access =
            $access = 'public';
            if($method->isPrivate()){$access='private';}
            if($method->isProtected()){$access='protected';}
            if($method->isPublic()){$access='public';}

            $methods[$method->getName()]=array_merge([
                'name'=>$method->getName(),'access'=>$access,'static'=>$method->isStatic()
            ],$this->parseDocComment($method->getDocComment(),$method));
        }
        self::$docs['classes'][$RelClass->getName()]['methods']=$methods;
    }
    protected function parseDocComment($Comment,ReflectionMethod $Method=null){
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
                $name = $parameter->getName();
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
                $name = $parameter->getName();
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
        return $data;
    }
    /**
     * @login true
     *
     */
    function renderMD(){
        $View = new \Tsy\Library\View();
        $View->assign(self::$docs);
        return $View->fetch(__DIR__.DIRECTORY_SEPARATOR.'Template'.DIRECTORY_SEPARATOR.'render.html');
    }
    function renderHTML(){}
    function renderDOC(){}
    function renderUML(){}
    function renderXLS(){}
    function renderCSV(){}
}