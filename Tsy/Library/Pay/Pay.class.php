<?php
/**
 * Created by PhpStorm.
 * User: Castle
 * Date: 2016/9/13
 * Time: 12:44
 */

namespace Tsy\Library\Pay;


class Pay
{
    public $error='';
    function __construct($Type,$Config=[])
    {
        
    }

    function notify(){

    }
    function redirect(){

    }

    /**
     * 支付
     * @param $OrderID
     * @param $Name
     * @param $Money
     * @param string $Memo
     */
    function pay($OrderID,$Name,$Money,$Memo=''){}
}