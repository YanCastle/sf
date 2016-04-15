<?php
/**
 * Created by PhpStorm.
 * User: Castle
 * Date: 2016/4/13
 * Time: 21:37
 */
function S($key,$value=false,$expire=false,$Default=''){
    $value = cache($key,$value,$expire);
    return null!==$value?$value:$Default;
}

/**
 * 缓存
 * @param $key
 * @param bool $value
 * @param bool $expire
 */
function cache($key,$value=false,$expire=null,$type=''){
    static $_map =[];
    if(empty($type))  $type = C('DATA_CACHE_TYPE');
    if(!isset($_map[$type])){
        $class  =   strpos($type,'\\')? $type : 'Tsy\\Library\\Cache\\Driver\\'.ucwords(strtolower($type));
        if(class_exists($class)){
            $cache = new $class([]);
            $_map[$type]=$cache;
        }
        else{
            L('缓存驱动类不存在',LOG_ERR);
            return false;
        }
    }else{
        $cache = $_map[$type];
    }
    //开始数据处理
    if($cache){
        if(preg_match('/\[[a-z]+\]/',$key)){
            switch (substr($key,1,strlen($key)-2)){
                case 'clean':
                    $cache->clean();
                    break;
            }
            return null;
        }
        if(false===$value){
            return $cache->get($key);
        }elseif (null==$value){
            return $cache->rm($key);
        }else{
            return $cache->set($key,$value,$expire);
        }
    }else{
        L('缓存驱动类不存在',LOG_ERR);
        return false;
    }
}

/**
 * 队列读写
 * @param $key
 * @param bool $value
 * @param int $order 1表示先进先出 0 先进后出
 */
function queue($key,$value=false,$order=1){

}