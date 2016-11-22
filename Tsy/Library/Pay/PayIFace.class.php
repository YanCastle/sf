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
    function __construct($Type,$Config='');

    /**
     * 异步回调通知
     * @param callable $success 支付成功
     * @param callable $finish 支付完成
     * @param callable|null $fail 支付失败
     * @return mixed
     */
    function notify($success,$finish,$fail);
    /**
     * 支付
     * @param $OrderID
     * @param $Name
     * @param $Money
     * @param string $Memo
     */
    function pay($Type,$OrderID,$Name,$Money,$Memo='');
}