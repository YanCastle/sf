<?php
/**
 * Created by PhpStorm.
 * User: Castle
 * Date: 2016/4/12
 * Time: 23:31
 */
//echo 'a';
class cc{
    static public $b ='ss';
    public $s = '';
    function s(){echo 's';}
}
$c = new cc();
$c->s='ss';
$s = serialize($c);
$b = unserialize($s);
$s->s();
echo $s;