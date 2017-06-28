<?php
/**
 * Created by PhpStorm.
 * User: 鄢鹏权
 * Date: 2017/06/28
 * Time: 15:18
 */

namespace Tsy\Library\Msg\Driver;


use Tsy\Library\Msg\MsgIFace;

class C123 implements MsgIFace
{
    protected $config=[];
    function __construct($config = [])
    {
        if($config){
            $this->config=$config;
        }else{
            foreach (['AC','AUTHKEY','CGID'] as $item){
                $this->config[strtolower($item)]=C("MSG.C123.{$item}");
            }
        }
    }
    function send($To, $Content)
    {
        if(!is_array($To)){$To=[$To];}
        $Message=urlencode($Content);
        $url = "http://smsapi.c123.cn/OpenPlatform/OpenApi?action=sendOnce&ac={$this->config['AC']}&authkey={$this->config['AUTHKEY']}&cgid={$this->config['CGID']}&c={$Message}&m=".implode(',',$To);
        $rs = file_get_contents($url);
        return strpos($rs,'result="1"')>0;
    }
    function RemoteTemplateSend($To, $Params, $TemplateID)
    {
        return false;
    }
    function LocalTemplateSend($To, $Params, $Content)
    {
        return false;
    }
    function receive()
    {
        return false;
    }
}