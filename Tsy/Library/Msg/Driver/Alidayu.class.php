<?php
/**
 * Created by PhpStorm.
 * User: Castle
 * Date: 2016/9/5
 * Time: 22:07
 */

namespace Tsy\Library\Msg\Driver;


use Tsy\Library\Msg\MsgIFace;

class Alidayu implements MsgIFace
{

    public function __construct($config=[])
    {
//        parent::__construct($config);
    }

    /**
     * 使用内容发送
     * @param $To
     * @param $Content
     * @return mixed
     */
    function send($To, $Content)
    {
        // : Implement send() method.
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
        // : Implement RemoteTemplateSend() method.

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
//         : Implement LocalTemplateSend() method.
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