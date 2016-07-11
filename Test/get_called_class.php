<?php
/**
 * Created by PhpStorm.
 * User: castle
 * Date: 7/7/16
 * Time: 11:25 AM
 */
namespace a;
class method{
    function method(){
        echo __METHOD__;
    }
}
(new method())->method();