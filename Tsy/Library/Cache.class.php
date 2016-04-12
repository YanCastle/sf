<?php
/**
 * Created by PhpStorm.
 * User: Castle
 * Date: 2016/4/12
 * Time: 16:54
 */

namespace Tsy\Library;


abstract class Cache
{
    function read($key){}
    function write($key,$value){}
    function config(array $config=[]){}
}