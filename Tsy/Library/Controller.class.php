<?php
/**
 * Created by PhpStorm.
 * User: Castle
 * Date: 2016/4/11
 * Time: 23:11
 */

namespace Tsy\Library;


class Controller
{
    protected $className='';
    protected $swoole;
    function __construct()
    {
        $this->className = $this->getControllerName();
    }
    function __call($name, $arguments)
    {
        $Object = $this->className.'Object';
        if(class_exists($Object)){
            $ObjectClass = new $Object();
            if(method_exists($ObjectClass,$name)){
                return call_user_func_array($ObjectClass,$arguments);
            }
        }
    }
    protected function getControllerName(){
        return substr(__CLASS__,0,strlen(__CLASS__)-10);
    }
    function set_swoole($swoole){
        $this->swoole=$swoole;
    }
    protected function send($UID,$data){
        //TODO 需要建立UID跟fd的连接信息，如果不是在swoole模式下还需要放到队列中去
    }
    function _empty($Action,$Data){
        $Object = $this->className;
        return class_exists($this->className)?controller($this->className.'/'.$Action,$Data,'','Object'):"{$this->className}/{$Action}方法不存在";
    }
}