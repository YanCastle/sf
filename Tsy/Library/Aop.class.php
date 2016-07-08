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
    public static $AOP_BEFORE=0;
    public static $AOP_AFTER=1;

    /**
     * 添加aop拦截
     * @param string $name
     * @param callable $callback
     * @param $where
     * @param int $order
     * @return bool
     */
    public static function add(string $name,callable $callback,$where,$async=false,$order=0){
        if(!isset(self::$config[$name])){
            self::$config[$name]=[[],[]];
        }
        if(!isset(self::$config[$name][$where][$order])){
            self::$config[$name][$where][$order]=[];
        }
        self::$config[$name][$where][$order][]=$callback;
        return true;
    }
    public static function remove(string $name,$where=-1){}

    /**
     * aop执行
     * @param string $name
     * @param $where
     * @param array $data
     */
    public static function exec(string $name,$where,array &$data,$async=null){
        if(isset(self::$config[$name][$where])){
            foreach (self::$config[$name][$where] as $callback){
                if(is_callable($callback))
                    call_user_func($callback,$data);
            }
        }
    }
    public static function regist(string $name,callable $callback,$where){}
}