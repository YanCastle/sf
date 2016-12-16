<?php
/**
 * Created by PhpStorm.
 * User: 鄢鹏权
 * Date: 2016/12/1
 * Time: 21:53
 */

namespace Tsy\Library\Upload\Driver;
require_once TSY_PATH.'/Library/Storage/Driver/UCloud/proxy.php';
class Ucloud
{
    private $config=[
        'UCLOUD_PUBLIC_KEY'=>'',
        'UCLOUD_PRIVATE_KEY'=>'',
        'UCLOUD_BUKKET'=>''
    ];
    private $UCLOUD_PUBLIC_KEY='';
    private $UCLOUD_PRIVATE_KEY='';
    private $UCLOUD_BUKKET='';
    /**
     * 本地上传错误信息
     * @var string
     */
    private $error      =   '';

    /**
     * 构造函数，用于设置上传根路径
     * @param array  $config 配置
     */
    public function __construct($config){
        /* 默认配置 */
        $this->config = array_merge($this->config, $config);
        foreach ($this->config as $k=>$v){
            $this->$k=$v;
        }
    }

    /**
     * 检测上传根目录(OSS上传时支持自动创建目录，直接返回)
     * @param string $rootpath   根目录
     * @return boolean true-检测通过，false-检测失败
     */
    public function checkRootPath($rootpath){
        /* 设置根目录 */
        $this->rootPath = trim($rootpath, './') . '/';
        return true;
    }

    /**
     * 检测上传目录(OSS上传时支持自动创建目录，直接返回)
     * @param  string $savepath 上传目录
     * @return boolean          检测结果，true-通过，false-失败
     */
    public function checkSavePath($savepath){
        return true;
    }

    /**
     * 创建文件夹 (OSS上传时支持自动创建目录，直接返回)
     * @param  string $savepath 目录名称
     * @return boolean          true-创建成功，false-创建失败
     */
    public function mkdir($savepath){
        return true;
    }

    /**
     * 保存指定文件
     * @param  array   $file    保存的文件信息
     * @param  boolean $replace 同名文件是否覆盖
     * @return boolean          保存状态，true-成功，false-失败
     */
    public function save(&$file,$replace=true) {
        $key = $file['savepath'] . $file['savename'];
        list($data,$error) = UCloud_PutFile($this->UCLOUD_BUKKET,$key,$file['tmp_name']);
        if($error){
            L($error->ErrMsg,LOG_TIP);
            $this->error = $error->ErrMsg;
            return false;
        }
        return true;
    }

    /**
     * 获取最后一次上传错误信息
     * @return string 错误信息
     */
    public function getError(){
        return $this->error;
    }

    /**
     * 获取私有文件访问地址
     * @param string $bucket bucket名称
     * @param string $key 文件路径
     * @return string
     */
    public function getUrl($bucket,$key){
        return UCloud_MakePrivateUrl($bucket,$key);
    }
}