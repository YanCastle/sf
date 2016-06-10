<?php
/**
 * Copyright (c) 2016. Lorem ipsum dolor sit amet, consectetur adipiscing elit. 
 * Morbi non lorem porttitor neque feugiat blandit. Ut vitae ipsum eget quam lacinia accumsan. 
 * Etiam sed turpis ac ipsum condimentum fringilla. Maecenas magna. 
 * Proin dapibus sapien vel ante. Aliquam erat volutpat. Pellentesque sagittis ligula eget metus. 
 * Vestibulum commodo. Ut rhoncus gravida arcu. 
 */

/**
 * Created by PhpStorm.
 * User: castle
 * Date: 6/10/16
 * Time: 11:38 AM
 */

namespace Tsy\Plugs\Async;


class MySqlFuture implements FutureIntf
{
    static private $instances=[];
    static private $inUse=[];
    function __construct()
    {
    }

    function run(Async &$promise, $content)
    {
        
    }
    function getInstance($config){
        $md5    =   md5(serialize($config));
        if(isset(self::$instances[$md5])&&!isset(self::$inUse[$md5])){
            return self::$instances[$md5];
        }else{
            $mysqli=new \mysqli();
            $mysqli->connect($config['DB_HOST'],$config['DB_USER'],$config['DB_PWD'],$config['DB_NAME'],$config['DB_PORT']);
            
        }
    }
}