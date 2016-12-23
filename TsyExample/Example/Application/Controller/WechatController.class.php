<?php
/**
 * Created by PhpStorm.
 * User: 鄢鹏权
 * Date: 2016/12/13
 * Time: 22:27
 */

namespace Application\Controller;


use Tsy\Plugs\WebWechat\WebWechat;

class WechatController
{
    function __construct()
    {
        $Wechat = new WebWechat();
        $Wechat->downQRCode();
    }

    function index(){

    }
}