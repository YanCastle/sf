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
    static $headers=[
        'HTTP/1.1'=>'200 OK',
        'Connection:'=>'keep-alive',
        'Content-Type:'=>'text/html',
    ];
    if(false===$header){
        return $headers;
    }
    if(is_string($header)){
        $list = explode(' ',$header);
        if(count($list)<2){
            return false;
        }
        $key = $list[0];unset($list[0]);
        $headers[$key]=implode(' ',$list);
    }elseif(is_array($header)){
        $headers=array_merge($headers,$header);
    }elseif(null===$header){
        $str='';
        foreach ($headers as $k=>$v){
            $str.=($k.' '.$v."\r\n");
        }
        $headers=[
            'HTTP/1.1'=>'200 OK',
            'Connection:'=>'keep-alive',
            'Content-Type:'=>'text/html',
            'Access-Control-Allow-Origin:'=>$_SERVER['REQUEST_METHOD'],
            'Access-Control-Allow-Credentials:'=>'true',
            'Access-Control-Request-Method:'=>'GET,POST',
            'Access-Control-Allow-Headers:'=>'X-Requested-With,Cookie,ContentType',
        ];
        return $str."\r\n";
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
//    if('GET'==$Method){
        $GETData=explode('?',$Datas[0][1])[1];
        $GETData=explode('&',$GETData);
        foreach ($GETData as $get){
            $res=explode('=',$get);
            $GET[$res[0]]=$res[1];
        }
//    }else
    if ('POST'==$Method){
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

function http_in_check(){
//    调用HTTP模式的DISPATCH，然后调用Controller
    $Data=[
        'i'=>'Empty/_empty',
        'd'=>[],
    ];
    $Dispatch = C('HTTP.DISPATCH');
    if(is_callable($Dispatch)){
        $tmpData = call_user_func($Dispatch);
        $Data = is_array($tmpData)?array_merge($Data,$tmpData):$Data;
    }
    return $Data;
}

function http_out_check($data){
    $Out = C('HTTP.OUT');
    $OutData=is_callable($Out)?call_user_func($Out,$data):'';
    if(is_string($OutData)&&strlen($OutData)>0){
        echo $OutData;
    }
}

/**
 * 发送http_comment消息，
 * @param string $fdName 链接命名
 * @param string|int|array $data
 * @param bool $online
 * @return bool
 */
function http_comment($fdName,$data,$online=true){
    //检测需要comment的消息是否在线，

//    如果需要离线消息
    return true;
}