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
    private $handle=null;
    function __construct($Type,$Config=[])
    {
        $class = 'Tsy\Library\Pay\Driver\\'.$Type;
        if(class_exists($class)){
            $this->handle=new $class($Config);
        }
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
    function pay($OrderID,$Name,$Money,$Memo=''){
        if($this->handle){
            $rs = $this->handle->pay($OrderID,$Name,$Money,$Memo);
            if($rs){
                return $rs;
            }else{
                $this->error=$this->handle->error;
                return false;
            }
        }
        return false;
    }
}