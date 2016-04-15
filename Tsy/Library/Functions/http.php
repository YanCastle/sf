<?php
/**
 * Created by PhpStorm.
 * User: Castle
 * Date: 2016/4/15
 * Time: 17:15
 */
/**
 * 在swoole模式下发送header信息
 */
function http_header($header=false){
    static $headers=[];
    if(false===$header){
        return $headers;
    }
    if(is_string($header)){
        $headers[]=$header;
    }elseif(is_array($header)){
        $headers=array_merge($headers,$header);
    }else{

    }
    return true;
}

/**
 * @param bool $cookie
 * @return bool
 */
function http_cookie($cookie=false){
    static $cookies=[];
    if(false===$cookies){
        return $cookie;
    }
    if(is_string($cookie)){
        $cookies[]=$cookie;
    }elseif(is_array($cookie)){
        $cookies=array_merge($cookies,$cookie);
    }else{

    }
    return true;
}

function http_header_parse($data){

}
function http_parse($data){

}
function http_body_parse($data){

}