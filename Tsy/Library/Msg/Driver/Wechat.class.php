<?php
/**
 * Created by PhpStorm.
 * User: 鄢鹏权
 * Date: 2016/12/07
 * Time: 11:16
 */

namespace Tsy\Library\Msg\Driver;


use Tsy\Library\Msg\MsgIFace;
use Wechat\TsyWechat;

class Wechat implements MsgIFace
{
    protected $handle;
    protected $config=[];
    public function __construct($config=[])
    {
        $this->handle = new TsyWechat();
        $this->config=array_merge($this->config,$config);
    }

    /**
     * 使用内容发送
     * @param $To
     * @param $Content
     * @return mixed
     */
    function send($To, $Content)
    {
        // TODO: Implement send() method.
        return false;
    }

    /**
     * 使用服务商模板发送
     * @param $To
     * @param $Params
     * @param $TemplateID
     * @return mixed
     */
    function RemoteTemplateSend($To, $Params, $TemplateID)
    {
        if(!$To){
            return false;
        }
        if(!is_array($To)){
            $To=[$To];
        }
        $rs = true;
        foreach ($To as $to){
            $rs&=$this->handle->WechatAuth->templateSend($to,$TemplateID,$Params['URL'],$Params);
        }
        return $rs;
    }

    /**
     * 使用本地模板发送
     * @param $To
     * @param $Params
     * @param $Template
     * @return mixed
     */
    function LocalTemplateSend($To, $Params, $Content)
    {
        return false;
    }

    /**
     * 接受内容
     * @return mixed
     */
    function receive()
    {
        // TODO: Implement receive() method.
    }
}