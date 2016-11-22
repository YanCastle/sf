<?php
/**
 * Created by PhpStorm.
 * User: 鄢鹏权
 * Date: 2016/11/21
 * Time: 15:57
 */

namespace Tsy\Library\Pay;


interface PayIFace
{
//    public $error;
    function __construct($Type,$Config='');

    /**
     * 异步回调通知
     * @param callable $success 支付成功
     * @return mixed
     */
    function notify($success);
    /**
     * 支付
     * @param $OrderID
     * @param $Name
     * @param $Money
     * @param string $Memo
     */
    function pay($Type,$OrderID,$Name,$Money,$Memo='');
}