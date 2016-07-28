<?php
/**
 * Created by PhpStorm.
 * User: castle
 * Date: 2016/07/28
 * Time: 11:05
 */

namespace Tsy\Library\Msg\Driver;


use Tsy\Library\Msg\Driver\YunTongXun\CCPRestSDK;

class YunTongXun implements \Tsy\Library\Msg\MsgIFace
{
    protected $handle;
    function __construct($config=[])
    {
        $this->handle=new CCPRestSDK();
    }

    function config($config=[]){}
    function send($To,$Content){}
    function RemoteTemplateSend($To,$Params,$TemplateID){}
    function LocalTemplateSend($To,$Params,$Template){
        return false;
    }
    function receive(){}

}