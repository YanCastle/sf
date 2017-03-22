<?php
/**
 * Created by PhpStorm.
 * User: Castle
 * Date: 2016/4/11
 * Time: 23:09
 */

namespace Application\Controller;
use Tsy\Library\Controller;
use Tsy\Library\Storage;
use Tsy\Plugs\File\File;

class IndexController extends Controller
{
    function index(){
        if(!file_exists('config.json')){
            return 'config.json配置文件不存在';
        }
        $Config = file_get_contents('config.json');
        if($Config){
            $Config = json_decode($Config,true);
        }
        if(
            isset($Config['UCLOUD_PUBLIC_KEY'])&&$Config['UCLOUD_PUBLIC_KEY']&&
            isset($Config['UCLOUD_PRIVATE_KEY'])&&$Config['UCLOUD_PRIVATE_KEY']&&
            isset($Config['UCLOUD_BUKKET'])&&$Config['UCLOUD_BUKKET']&&
            isset($Config['SYNC_DIR'])&&$Config['SYNC_DIR']&&
            isset($Config['UCLOUD_PROXY_SUFFIX'])&&$Config['UCLOUD_PROXY_SUFFIX']
        ){
            C($Config);
        }else{
            return '错误的配置文件';
        }
        Storage::connect('UCloud');
        $SyncDir = C('SYNC_DIR');
        if(is_dir($SyncDir)){
            each_dir($SyncDir,null,function($path)use($SyncDir){
                $rs = Storage::put(str_replace([$SyncDir,"\\"],['','/'],$path),file_get_contents($path));
            });
            return '成功';
        }else{
            return '目录不存在;';
        }
    }
    /**
     * 空操作
     * @param string $Action 方法名称
     * @param array|string $Data 数据
     */
    function _empty($Action,$Data){}
    function sleep(){
        sleep(10);
        return 'out';
    }
    function check(){
        return 'sds';
    }
    function abc(){
        $Document = new \Document('./Doc/abc.pdm');
        $Document->generateControllers()->generateObjects()->generateModels();
    }
}