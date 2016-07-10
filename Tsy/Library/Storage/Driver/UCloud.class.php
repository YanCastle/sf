<?php
// +----------------------------------------------------------------------
// | TOPThink [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://topthink.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
namespace Tsy\Library\Storage\Driver;
use Tsy\Library\Storage;
// 本地文件写入存储类
class UCloud extends Storage{
    static $UCloudSDKPath=__DIR__.DIRECTORY_SEPARATOR.'UCloud';
    private $contents=array();

    /**
     * 架构函数
     * @access public
     */
    public function __construct() {
        global $SDK_VER;
        global $UCLOUD_PROXY_SUFFIX;
        global $UCLOUD_PUBLIC_KEY;
        global $UCLOUD_PRIVATE_KEY;
        $SDK_VER = "1.0.6";
        $UCLOUD_PROXY_SUFFIX = '.ufile.ucloud.cn';
//        $UCLOUD_PUBLIC_KEY = 'ucloudadmin@tansuyun.cn142716205800096890167';
        $UCLOUD_PUBLIC_KEY = C('UCLOUD_PUBLIC_KEY');
//        $UCLOUD_PRIVATE_KEY = '62a462d42168aa597f5fd42893ffcd1953b331d6';
        $UCLOUD_PRIVATE_KEY = C('UCLOUD_PRIVATE_KEY');
        require_once self::$UCloudSDKPath.DIRECTORY_SEPARATOR.'proxy.php';
    }

    /**
     * 文件内容读取
     * @access public
     * @param string $filename  文件名
     * @return string
     */
    public function read($filename,$type=''){
        return $this->get($filename,'content',$type);
    }

    /**
     * 文件写入
     * @access public
     * @param string $filename  文件名
     * @param string $content  文件内容
     * @return boolean
     */
    public function put($filename,$content,$type=''){

    }

    /**
     * 文件追加写入
     * @access public
     * @param string $filename  文件名
     * @param string $content  追加的文件内容
     * @return boolean
     */
    public function append($filename,$content,$type=''){
        if(is_file($filename)){
            $content =  $this->read($filename,$type).$content;
        }
        return $this->put($filename,$content,$type);
    }

    /**
     * 加载文件
     * @access public
     * @param string $filename  文件名
     * @param array $vars  传入变量
     * @return void
     */
    public function load($_filename,$vars=null){
        if(!is_null($vars)){
            extract($vars, EXTR_OVERWRITE);
        }
        include $_filename;
    }

    /**
     * 文件是否存在
     * @access public
     * @param string $filename  文件名
     * @return boolean
     */
    public function has($filename,$type=''){
        return is_file($filename);
    }

    /**
     * 文件删除
     * @access public
     * @param string $filename  文件名
     * @return boolean
     */
    public function unlink($filename,$type=''){
        unset($this->contents[$filename]);
        return is_file($filename) ? unlink($filename) : false;
    }

    /**
     * 读取文件信息
     * @access public
     * @param string $filename  文件名
     * @param string $name  信息名 mtime或者content
     * @return boolean
     */
    public function get($filename,$name,$type=''){
        if(!isset($this->contents[$filename])){
            if(!is_file($filename)) return false;
           $this->contents[$filename]=file_get_contents($filename);
        }
        $content=$this->contents[$filename];
        $info   =   array(
            'mtime'     =>  filemtime($filename),
            'content'   =>  $content
        );
        return $info[$name];
    }
}
