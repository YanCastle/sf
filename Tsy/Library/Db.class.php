<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2014 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

namespace Tsy\Library;

/**
 * ThinkPHP 数据库中间层实现类
 */
class Db {

    static private  $instance   =  array();     //  数据库连接实例
    static private  $_instance  =  null;   //  当前数据库连接实例

    /**
     * 取得数据库类实例
     * @static
     * @access public
     * @param mixed $config 连接配置
     * @return Object 返回数据库驱动类
     */
    static public function getInstance($config=array()) {
        $md5    =   md5(serialize($config));
        if(!isset(self::$instance[$md5])) {
            // 解析连接参数 支持数组和字符串
            $options    =   self::parseConfig($config);
            // 兼容mysqli
            if('mysqli' == $options['type']) $options['type']   =   'mysql';
            // 如果采用lite方式 仅支持原生SQL 包括query和execute方法
            $class  =   !empty($options['lite'])?  'Tsy\Library\Db\Lite' :   'Tsy\\Library\\Db\\Driver\\'.ucwords(strtolower($options['type']));
            if(class_exists($class)){
                self::$instance[$md5]   =   new $class($options);
            }else{
                // 类没有定义
                L("缺少数据库驱动类");
            }
        }
        self::$_instance    =   self::$instance[$md5];
        return self::$_instance;
    }

    /**
     * 数据库连接参数解析
     * @static
     * @access private
     * @param mixed $config
     * @return array
     */
    static private function parseConfig($config){
        if(!empty($config)){
            if(is_string($config)) {
                return self::parseDsn($config);
            }
            $config =   array_change_key_case($config);
            $config = array (
                'type'          =>  $config['DB_TYPE'],
                'username'      =>  $config['DB_USER'],
                'password'      =>  $config['DB_PWD'],
                'hostname'      =>  $config['DB_HOST'],
                'hostport'      =>  $config['DB_PORT'],
                'database'      =>  $config['DB_NAME'],
                'dsn'           =>  isset($config['DB_DSN'])?$config['DB_DSN']:null,
                'params'        =>  isset($config['DB_PARAMS'])?$config['DB_PARAMS']:null,
                'charset'       =>  isset($config['DB_CHARSET'])?$config['DB_CHARSET']:'utf8',
                'deploy'        =>  isset($config['DB_DEPLOY_TYPE'])?$config['DB_DEPLOY_TYPE']:0,
                'rw_separate'   =>  isset($config['DB_RW_SEPARATE'])?$config['DB_RW_SEPARATE']:false,
                'master_num'    =>  isset($config['DB_MASTER_NUM'])?$config['DB_MASTER_NUM']:1,
                'slave_no'      =>  isset($config['DB_SLAVE_NO'])?$config['DB_SLAVE_NO']:'',
                'debug'         =>  isset($config['DB_DEBUG'])?$config['DB_DEBUG']:APP_DEBUG,
                'lite'          =>  isset($config['DB_LITE'])?$config['DB_LITE']:false,
            );
        }else {
            $config = array (
                'type'          =>  C('DB_TYPE'),
                'username'      =>  C('DB_USER'),
                'password'      =>  C('DB_PWD'),
                'hostname'      =>  C('DB_HOST'),
                'hostport'      =>  C('DB_PORT'),
                'database'      =>  C('DB_NAME'),
                'dsn'           =>  C('DB_DSN'),
                'params'        =>  C('DB_PARAMS'),
                'charset'       =>  C('DB_CHARSET'),
                'deploy'        =>  C('DB_DEPLOY_TYPE'),
                'rw_separate'   =>  C('DB_RW_SEPARATE'),
                'master_num'    =>  C('DB_MASTER_NUM'),
                'slave_no'      =>  C('DB_SLAVE_NO'),
                'debug'         =>  C('DB_DEBUG',null,APP_DEBUG),
                'lite'          =>  C('DB_LITE'),
            );
        }
        return $config;
    }

    /**
     * DSN解析
     * 格式： mysql://username:passwd@localhost:3306/DbName?param1=val1&param2=val2#utf8
     * @static
     * @access private
     * @param string $dsnStr
     * @return array
     */
    static private function parseDsn($dsnStr) {
        if( empty($dsnStr) ){return false;}
        $info = parse_url($dsnStr);
        if(!$info) {
            return false;
        }
        $dsn = array(
            'type'      =>  $info['scheme'],
            'username'  =>  isset($info['user']) ? $info['user'] : '',
            'password'  =>  isset($info['pass']) ? $info['pass'] : '',
            'hostname'  =>  isset($info['host']) ? $info['host'] : '',
            'hostport'  =>  isset($info['port']) ? $info['port'] : '',
            'database'  =>  isset($info['path']) ? substr($info['path'],1) : '',
            'charset'   =>  isset($info['fragment'])?$info['fragment']:'utf8',
        );
        
        if(isset($info['query'])) {
            parse_str($info['query'],$dsn['params']);
        }else{
            $dsn['params']  =   array();
        }
        return $dsn;
     }

    // 调用驱动类的方法
    static public function __callStatic($method, $params){
        return call_user_func_array(array(self::$_instance, $method), $params);
    }
}
