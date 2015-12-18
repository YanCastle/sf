<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015-11-28
 * Time: 16:38
 */

namespace Plugs\Sms\Drivers;


use Plugs\Sms\Driver;
use Plugs\Sms\Drivers\Rly\SendSMS;


class Rly extends Driver{
    public $AppId;
    public $SoftVersion;
    public $BodyType = "xml";//包体格式，可填值：json 、xml
//    private $Filename="./log.txt"; //日志文件
//    private $Handle;
    function __construct(){
        parent::__construct();
    }
    function config($accountSid='aaf98f895147cd2a01514dea0a370ee5',$accountToken='380cec0f204c461aa9cf98d461a51f89',$ServerIP='sandboxapp.cloopen.com',$serverPort='8883'){
        $this->ServerIP=$ServerIP;
        $this->serverPort=$serverPort;
        $this->SoftVersion='2013-12-26';
        $this->accountSid=$accountSid;
        $this->accountToken=$accountToken;
        $this->AppId='aaf98f894308abec014315a37efc0083';

    }
    function sent($to,$content,$tempId='1'){
        if($to){
            if(is_array($to)){
                $handleTo='';
                foreach($to as $val){
                    preg_match('/1[34578]\d{9}$/',$val,$MatchRs);
                    if(count($MatchRs)<1){
                        return false;
                    }else {
                        $handleTo +=',' + $val;
                    }
                }
                unset($to);
                $to=trim($handleTo);
            }elseif(is_string($to)){
//                为了安全，重新分割，生成
                $Handletos=explode(',',$to);
                if(is_array($Handletos)){
                    $handleTo='';
                    foreach($Handletos as $val){
//                        判断val是否全为数字，数字的位数
//                        只允许11位
                        preg_match('/1[34578]\d{9}$/',$val,$MatchRs);
                        if(count($MatchRs)<1){
                            return false;
                        }else{
                            $handleTo+=','+$val;
                        }
                    }
                }
                unset($to);
                $to=trim($handleTo);
            }
        }else{
//            没有发送对象
            return false;
        }
        if($content){
            if(is_string($content)){
                //如果为字符串，则处理成数组。
                $handlecon=array($content);
                unset($content);
                $content=$handlecon;
            }
            elseif(is_array($content)){
                //如果为数组，则不做处理。
            }
        }else{
            //没有传入数据，则默认不需要替换
            $content=array(null);
        }
        //调用底层
        $SendTemplateSMS=new SendSMS();
        $rs=  $SendTemplateSMS->sendTemplateSMS($to,$content,$tempId,$this->ServerIP,$this->serverPort,$this->SoftVersion,$this->accountSid,$this->accountToken,$this->AppId);
        if(!$rs){
            return false;
        }
    }
}