<?php
/**
 * Created by PhpStorm.
 * User: castle
 * Date: 16-5-4
 * Time: 下午6:52
 */
/**
 * 实例化对象类
 * @param string $Name 对象名
 * @param array $Config 初始化对象时的参数
 * @return \TSy\Library\Object
 */
function O($name,$config=[]){
//    $Config=[
//        'main'=>'printer',
//        'pk'=>'PrinterClientID',
//        'property'=>[
//            '属性名称'=>[
//                \Tsy\Library\Object::RELATION_TABLE_NAME=>'',
//            ]
//        ]
//    ];
    static $_objects  = array();
    if(strpos($name,':')) {
        list($class,$name)    =  explode(':',$name);
    }elseif($name){
        $class = current_MCA('M')."\\Object\\{$name}Object";
        if(class_exists($class)){
//            $class
        }else{
            $class      =   'Tsy\\Library\\Object';
        }
    }else{
        $class      =   'Tsy\\Library\\Object';
    }
    $guid           =   (is_array($config)?implode('',$config):$config). $name . '_' . $class;
    if (!isset($_objects[$guid]))
        $_objects[$guid] = new $class($name,$config);
    return $_objects[$guid];
}

function object_generate($Objects,$Properties){
    foreach ($Objects as $ObjectID=>$Object){
//        $Objects[$ObjectID]
    }
}