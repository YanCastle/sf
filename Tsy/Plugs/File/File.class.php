<?php
/**
 * Created by PhpStorm.
 * User: castle
 * Date: 2015/11/30
 * Time: 13:43
 */

namespace Tsy\Plugs\File;


use Tsy\Library\Upload;

class File
{
    static function eachDir($path,$dir_callback,$file_callback){
        return each_dir($path,$dir_callback,$file_callback);
    }

    /**
     * 删除文件
     */
    static function rm(){}

    /**
     * 移动文件，改名
     */
    static function mv(){}

    /**
     * 写入文件，自带目录存在性检测
     * @param string $path 文件目录
     * @param string $content 写入内容
     * @param bool|false $append 是否追加写入
     */
    static function write($path,$content,$append=false){
        if(!is_dir($path)&&!self::mk_dir($path)){
            return false;
        }
        if($append){
            $fp = fopen($path,'w+');
            $writes = fwrite($fp,$content);
            fclose($fp);
            return !!$writes;
        }else{
            return !!file_put_contents($path,$content);
        }
    }
    static function mk_dir($path){
        if(is_dir($path)){return true;}
        if(is_file($path)){return false;}
        return mkdir($path,0777,true);
    }
    /**
     * 上传文件
     * @link http://document.thinkphp.cn/manual_3_2.html#upload
     */
    static function upload($config = array(), $driver = '', $driverConfig = null){
        foreach(explode(',','mimes,maxSize,exts,autoSub,subName,rootPath,savePath,saveName,saveExt,replace,hash,callback,driver,driverConfig') as $k){
            if(!isset($config[$k])){
                $c = C('UPLOAD_'.strtoupper($k),null,'');
                if($c){
                    $config[$k]=$c;
                }
            }
        }
        $Upload = new Upload($config,$driver,$driverConfig);
        $infos = $Upload->upload();
        if($infos){
            $Model = M('Upload');
            $Rs = [];
            foreach($infos as $info){
                $data = [
                    'FileName'=>$info['name'],
                    'Extension'=>$info['ext'],
                    'MIME'=>$info['type'],
                    'Size'=>$info['size'],
                    'SaveName'=>$info['savename'],
                    'SavePath'=>$info['savepath'],
                    'FileMd5'=>$info['md5'],
                    'UploadTime'=>date('Y-m-d H:i:s'),
                    'UploaderUID'=>session('UID'),
                    'DriverType'=>$Upload->driver,
//                    'URL'=>implode('',[C('FILE_UPLOAD_TYPE_URL'),$info['savepath'],$info['savename']])
                ];
                $data['URL']=str_replace(array_keys($data),array_values($data),C('FILE_UPLOAD_TYPE_URL'));
                $UploadID = $Model->add($data);
                if($UploadID){
                    $data['UploadID']=$UploadID;
                    $Rs[]=$data;
                }else{
                    return false;
                }
            }
            return $Rs;
        }else{
            //失败
            return $Upload->getError();
        }
    }

    /**
     * 下载文件
     */
    static function download($file){}

    /**
     * 罗列文件
     */
    static function ls($dir){}

    /**
     * zip压缩
     */
    static function zip(){}

    /**
     * zip解压
     */
    static function unzip(){}

    /**
     * 获取上传文件的下载地址
     * @param $UploadID
     * @return bool|mixed|string
     */
    static function getUploadFileURL($UploadID){
        if($File=M('Upload')->where(['UploadID'=>$UploadID])->find()){
            $URL = C('FILE_UPLOAD_TYPE_URL');
            if($URL&&is_callable($URL)){
                return call_user_func($URL,$File);
            }
            return $URL?(implode('/',array_merge(explode('/',$URL),[$File['SavePath'],$File['SaveName']]))):false;
        }
        return false;
    }
}