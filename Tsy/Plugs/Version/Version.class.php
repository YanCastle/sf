<?php
/**
 * Created by PhpStorm.
 * User: castle
 * Date: 5/31/16
 * Time: 3:35 PM
 */

namespace Tsy\Plugs\Version;


use Tsy\Library\Model;
use Tsy\Plugs\Db\Db;

class Version
{
    function __construct()
    {
        defined('VERSION_PATH') or define('VERSION_PATH',APP_PATH.DIRECTORY_SEPARATOR.'Common/Version' );

    }

    function check($VersionMap,$TargetVersion){
        $VersionMap=[
            [
                1,
                'DB_CONF'=>[]
            ]
        ];
//        先获取最高版本号
        foreach ($VersionMap as $Config){
            if(is_array($Config)&&is_numeric($Config[0])&&$Config[0]<$TargetVersion){//需要升级的情况下
                if(isset($Config['DB_CONF'])){
                    for(++$Config[0];$Config[0]<=$TargetVersion;$Config[0]++){
                        $this->db_execute($Config['DB_CONF'],$Config[0]);
                    }
                }
                $this->php_execute();
            }
        }
    }
    function db_execute($Config,$Version){
        $Version=is_numeric($Version)?'V'.$Version:$Version;
        $AddPath = VERSION_PATH.DIRECTORY_SEPARATOR.$Version;
        $Model = new Model('',isset($Config['DB_PREFIX'])?$Config['DB_PREFIX']:'',$Config);
        $Db = new Db('',isset($Config['DB_PREFIX'])?$Config['DB_PREFIX']:'',$Config);
        if(is_dir($AddPath)&&file_exists($AddPath.DIRECTORY_SEPARATOR.$Version.'.sql')){
            return $Db->build($Model,$AddPath.DIRECTORY_SEPARATOR.$Version.'.sql','',$Config['DB_PREFIX']);
        }
        return false;
    }
    function php_execute($Version,$Config,$file=''){
//        if($Version)
        $Version=is_numeric($Version)?'V'.$Version:$Version;
        $ClassName = "\\Common\\Version\\{$Version}\\{$Version}";
        if(file_exists($file)){
            include $file;
        }
        if(class_exists($ClassName)){
            $Class = new $ClassName($Config);
            if(method_exists($Class,'update')){
                return $Class->update();
            }
        }
        return false;
    }
    function add($DB_CONF){
//        创建一个新的数据库
        $AddPath = VERSION_PATH.DIRECTORY_SEPARATOR.'Add';
        if(is_dir($AddPath)&&file_exists($AddPath.DIRECTORY_SEPARATOR.'Add.sql')){
            $this->db_execute($DB_CONF, 'Add');
            $this->php_execute('Add',$DB_CONF );
        }else{
            L(E('_NO_ADD_CONFIG_'));
            return false;
        }
    }
}