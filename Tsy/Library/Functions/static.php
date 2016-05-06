<?php
/**
 * Created by PhpStorm.
 * User: castle
 * Date: 5/6/16
 * Time: 4:07 PM
 */
function static_keep($name,$value=null){
    static $values = [];
    if($name&&$value){
        $values[$name]=$value;
    }else{
        return isset($values[$name])?$values[$name]:null;
    }
}