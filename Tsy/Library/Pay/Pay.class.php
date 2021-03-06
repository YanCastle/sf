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
    const TRADE_SUCCESS='SUCCESS';//成功
    const TRADE_FINISH='FINISH';//完成
    const TRADE_FAILD='FAILD';//失败

    public static $error='';
    /**
     * @var PayIFace
     */
    private $handle=null;
    function __construct($Type,$Config=[])
    {
        $class = 'Tsy\Library\Pay\Driver\\'.$Type;
        if(class_exists($class)){
            $this->handle=new $class($Config);
        }
    }

    /**
     * 验证失败
     * @param callable $success
     * @param callable $finish
     * @param callable $fail
     */
    function notify(callable $success){
        $verifyResult=$this->handle->notify($success);
        return $verifyResult;
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
    function pay($Type,$OrderID,$Name,$Money,$Memo='',$Config=[]){
        if($this->handle){
            $rs = $this->handle->pay($Type,$OrderID,$Name,$Money,$Memo);
            if($rs){
                return $rs;
            }else{
                $this->error=$this->handle->error;
                return false;
            }
        }
        return false;
    }
//    function
}