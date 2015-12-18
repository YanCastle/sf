<?php
/**
 * Created by PhpStorm.
 * User: castle
 * Date: 15-11-27
 * Time: 下午9:00
 */

namespace Plugs\Sms;


class Driver
{

    public $ServerIP;//服务器IP
    public $serverPort;//服务器端口
    public $accountSid;//主账号
    public $accountToken;//主账号令牌
//    public $enabeLog = true;//日志开关。可填值：true、
//    public $log="短信发送日志";
    function __construct(){
    }
    function send($to,$content,$way){

    }
}