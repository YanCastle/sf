<?php
/**
 * Created by PhpStorm.
 * User: Castle
 * Date: 2016/4/11
 * Time: 22:20
 */
function is_first_receive($fd){
    return true;
}

function session($name,$value=false){
    if(substr($name,0,1)=='['&&substr($name,-1)==']'){
        switch (strtolower(substr($name,1,strlen($name)-2))){
            case 'id':
                if($value){
                    $_COOKIE['session_id']=$value;
                }else{
                    return $_COOKIE['session_id'];
                }
                break;
        }
    }
    if(null===$name){
        //清空session
        cache('session_'.$_COOKIE['session_id'],[]);
    }
    $session = cache('session_'.$_COOKIE['session_id'],false,false,[]);
    if($value){
        $session[$name]=$value;
    }else{
        return isset($session[$name])?$session[$name]:null;
    }
}


/**
 * 任务投递
 */
function task(){}

/**
 * 异步任务
 * @param callable $callback
 * @param array $params
 */
function async($config,array $params=[]){}


