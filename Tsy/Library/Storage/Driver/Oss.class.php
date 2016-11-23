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
use Aliyun\OSS\OSSClient;
use Tsy\Library\Storage;
require_once(VENDOR_PATH.'/Oss/aliyun.php');
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
        $this->handle = OSSClient::factory(array(
            'Endpoint' => $this->config['Endpoint'],
            'AccessKeyId' => $this->config['AccessKeyId'],
            'AccessKeySecret' => $this->config['AccessKeySecret'],
        ));
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
        $save = $this->handle->putObject(array(
            'Bucket'    => $this->config['Bucket'],
            'Key'       => $key,
            'Content'   => $content,
            'ContentLength'=> $size,
        ));
        if ($save) {
            return true;
        }else{
            L($this->handle->errorStr);
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
        if($this->handle->getObject([
            'Bucket'=>$this->config['Bucket'],
            'Key'=>$filename,
            'MetaOnly'=>true
        ])){
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
        return !!$this->handle->getObject([
            'Bucket'=>$this->config['Bucket'],
            'Key'=>$filename,
            'MetaOnly'=>true
        ]);
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
        return !!$this->handle->deleteObject([
            'Bucket'=>$this->config['Bucket'],
            'Key'=>$filename,
        ]);
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
           $this->contents[$filename]=$this->handle->getObject([
               'Bucket'=>$this->config['Bucket'],
               'Key'=>$filename
           ]);
        }
        $content=$this->contents[$filename];
        $info   =   array(
            'mtime'     =>  time(),
            'content'   =>  $content
        );
        return $info[$name];
    }
}
