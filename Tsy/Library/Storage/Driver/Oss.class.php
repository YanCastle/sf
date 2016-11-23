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
use OSS\Core\OssException;
use Tsy\Library\Storage;
require_once(VENDOR_PATH.'/aliyun-oss-php-sdk-2.2.0.phar');
// 本地文件写入存储类
class Oss extends Storage{
    private $config = array(
        'AccessKeyId' => '', //OSS用户
        'AccessKeySecret' => '', //OSS密码
        'domain'        =>'',   //OSS空间路径
        'Bucket'   => '', //空间名称
        'Endpoint'  => '', //带http的节点名称
    );
    private $handle='';
    private $contents=array();

    /**
     * 架构函数
     * @access public
     */
    public function __construct($config) {
        $this->config = array_merge($this->config,$config);
        try{
            $this->handle = new \OSS\OssClient($this->config['AccessKeyId'],$this->config['AccessKeySecret'],$this->config['Endpoint']);
        }catch (OssException $e){
            L($e->getErrorMessage());
        }
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
        $key = $filename;
//        $content = fopen( $file['tmp_name'],'r');
        $size = strlen($content);
        try{
            return $this->handle->putObject($this->config['Bucket'],$key,$content);
        }catch (OssException $e){
            L($e->getErrorMessage());
            return false;
        }
    }

    /**
     * 文件追加写入
     * @access public
     * @param string $filename  文件名
     * @param string $content  追加的文件内容
     * @return boolean        
     */
    public function append($filename,$content,$type=''){
        try{
            return !!$this->handle->appendObject($this->config['Bucket'],$filename,$content,0);
        }catch (OssException $e){
            L($e->getErrorMessage());
            return false;
        }
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
        return !!$this->handle->doesObjectExist($this->config['Bucket'],$filename);
    }

    /**
     * 文件删除
     * @access public
     * @param string $filename  文件名
     * @return boolean     
     */
    public function unlink($filename,$type=''){
//        unset($this->contents[$filename]);
//        return is_file($filename) ? unlink($filename) : false;
        return !!$this->handle->deleteObject($this->config['Bucket'],$filename);
    }

    /**
     * 读取文件信息
     * @access public
     * @param string $filename  文件名
     * @param string $name  信息名 mtime或者content
     * @return boolean     
     */
    public function get($filename,$name='content',$type=''){
        if(!isset($this->contents[$filename])){
            $this->contents[$filename]=$this->handle->getObject($this->config['Bucket'],$filename);
        }
        return $name=='content'?$this->contents[$filename]:time();
    }
}
