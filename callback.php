<?php
/**
 * Created by PhpStorm.
 * User: castle
 * Date: 5/7/16
 * Time: 4:11 PM
 */
$a = serialize(function(){return 'a';});
$func = unserialize($a);
if(is_callable($func)){
    call_user_func($func);
}