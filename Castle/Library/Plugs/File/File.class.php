<?php
/**
 * Created by PhpStorm.
 * User: castle
 * Date: 2015/11/30
 * Time: 13:43
 */

namespace Plugs\File;


use Core\Upload;

class File
{
    static function eachDir(){}

    /**
     * 删除文件
     */
    static function rm(){}

    /**
     * 移动文件，改名
     */
    static function mv(){}

    /**
     * 上传文件
     * @link http://document.thinkphp.cn/manual_3_2.html#upload
     */
    static function upload($TPUploadConfig=[]){
        foreach(explode(',','mimes,maxSize,exts,autoSub,subName,rootPath,savePath,saveName,saveExt,replace,hash,callback,driver,driverConfig') as $config){
            if(!isset($TPUploadConfig[$config])){
                $c = C('UPLOAD_'.strtoupper($config),null,'');
                if($c){
                    $TPUploadConfig[$config]=$c;
                }
            }
        }
        $Upload = new Upload($TPUploadConfig);
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
                    'UploadTime'=>time(),
                    'UploaderUID'=>session('UID')
                ];
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
}