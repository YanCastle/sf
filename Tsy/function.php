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
    static $session_id = '';
    if(substr($name,0,1)=='['&&substr($name,-1)==']'){
        switch (strtolower(substr($name,1,strlen($name)-2))){
            case 'id':
                if($value){
                    $session_id=$value;
                }elseif(null===$value){
                    $session_id='';
                }else{
                    return $session_id;
                }
//                cache('sess_'.$session_id,[]);
                break;
        }
        return '';
    }
    if(null===$name){
        //清空session
        cache('sess_'.$session_id,[]);
    }

    $session = cache('sess_'.$session_id);
    if(false===$name){
//        获取全部
        return $session;
    }elseif(is_array($name)){
        //设置全部
//        cache('sess_'.$session_id,$name);
        $session = is_array($session)?array_merge($session,$name):$name;
        cache('sess_'.$session_id,$session);
        return true;
    }
    if($value){
        $session[$name]=$value;
    }else{
        return isset($session[$name])?$session[$name]:null;
    }
    cache('sess_'.$session_id,$session,1200);
}





