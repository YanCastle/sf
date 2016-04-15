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
 * @param bool $Headerookie
 * @return bool
 */
function http_cookie($Headerookie=false){
    static $Headerookies=[];
    if(false===$Headerookies){
        return $Headerookie;
    }
    if(is_string($Headerookie)){
        $Headerookies[]=$Headerookie;
    }elseif(is_array($Headerookie)){
        $Headerookies=array_merge($Headerookies,$Headerookie);
    }else{

    }
    return true;
}

/**
 *
 * @param $data
 */
function http_header_parse($data){
    
}

/**
 *
 * @param $data
 */
function http_parse($data){
    $data=array_filter(explode("\r\n",$data));
    $B=[];$Datas=[];$Header=[];
    foreach($data as $d){
        $B[]=explode(':',$d);
    }
    foreach ($B as $b){
        if(count($b)!=1){
            $Header[$b[0]]=$b;
            unset($Header[$b[0]][0]);
            $Header[$b[0]]=array_values($Header[$b[0]]);
            $Header[$b[0]] = implode(':', $Header[$b[0]]);
        }
        else{
            $Datas=array_merge($Datas,$b);
        }
    }
    foreach ($Datas as $k=>$v){
        $Datas[$k]=explode(' ',$v);
    }
    $Version=$Datas[0][2];
    $Method=$Datas[0][0];
    if('GET'==$Method){
        $GETData=explode('?',$Datas[0][1])[1];
        $GETData=explode('&',$GETData);
        foreach ($GETData as $get){
            $res=explode('=',$get);
            $GET[$res[0]]=$res[1];
        }
    }elseif ('POST'==$Method){
        $POSTData=explode('&',$Datas[1][0]);
        foreach ($POSTData as $get){
            $res=explode('=',$get);
            $POST[$res[0]]=$res[1];
        }
    }
    return array_merge(['Header'=>$Header,'Method'=>$Method,'Version'=>$Version],isset($GET)?['GET'=>$GET]:[],isset($POST)?['POST'=>$POST]:[]);
}

/**
 *
 * @param $data
 */
function http_body_parse($data){

}
//----------------------------------get--------------------------//
//GET / HTTP/1.1
//Host: 127.0.0.1:60000
//Connection: keep-alive
//Cache-Control: max-age=0
//Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8
//Upgrade-Insecure-Requests: 1
//User-Agent: Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/49.0.2623.112 Safari/537.36
//Accept-Encoding: gzip, deflate, sdch
//Accept-Language: zh-CN,zh;q=0.8

//--------------------------文件-----------------------//
//PUT /swoole.log HTTP/1.1
//Host: 127.0.0.1:60000
//User-Agent: curl/7.48.0
//Accept: */*
//Content-Length: 1239
//Expect: 100-continue

//----------------------文件内容----------------------//
//ERROR	zm_deactivate_swoole (ERROR 103): Fatal error: Class 'Tsy\Library\Swoole\' not found in /cygdrive/e/CygwinDownload/web/SocketFramework/Tsy/Library/Server.class.php on line 176.
//[2016-04-13 15:24:14 *6040.1]	ERROR	zm_deactivate_swoole (ERROR 9003): worker process is terminated by exit()/die().
//[2016-04-13 15:27:36 *116.1]	ERROR	zm_deactivate_swoole (ERROR 9003): worker process is terminated by exit()/die().
//[2016-04-13 15:41:28 *2760.1]	ERROR	zm_deactivate_swoole (ERROR 103): Fatal error: Call to undefined function Tsy\Library\json_encode() in /cygdrive/e/CygwinDownload/web/SocketFramework/Tsy/Library/Server.class.php on line 80.
//[2016-04-13 15:41:56 *4176.1]	ERROR	zm_deactivate_swoole (ERROR 103): Fatal error: Call to undefined function json_encode() in /cygdrive/e/CygwinDownload/web/SocketFramework/Tsy/Library/Server.class.php on line 80.
//[2016-04-13 16:16:21 *5540.1]	ERROR	zm_deactivate_swoole (ERROR 9003): worker process is terminated by exit()/die().
//[2016-04-13 16:17:13 *5192.1]	ERROR	zm_deactivate_swoole (ERROR 9003): worker process is terminated by exit()/die().
//[2016-04-13 18:42:44 *6992.0]	ERROR	zm_deactivate_swoole (ERROR 9003): worker process is terminated by exit()/die().

//-------------------------post-------------------------------//
//POST / HTTP/1.1
//Host: 127.0.0.1:60000
//User-Agent: curl/7.48.0
//Accept: */*
//Content-Length: 83
//Content-Type: application/x-www-form-urlencoded
//
//leaderboard_id

