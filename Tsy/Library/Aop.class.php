<?php
/**
 * Created by PhpStorm.
 * User: castle
 * Date: 2016/7/4
 * Time: 22:28
 */

namespace Tsy\Library;


class Aop
{
    protected static $config=[];
    public static function add(string $name,callable $callback){}
    public static function remove(string $name){}
    public static function exec(string $name,array &$data){}
}