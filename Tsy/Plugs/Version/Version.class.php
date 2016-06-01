<?php
/**
 * Created by PhpStorm.
 * User: castle
 * Date: 5/31/16
 * Time: 3:35 PM
 */

namespace Tsy\Plugs\Version;


use Tsy\Plugs\Db\Db;

class Version
{
    function __construct()
    {
        defined('VERSION_PATH') or define('VERSION_PATH',APP_PATH.DIRECTORY_SEPARATOR.'Common/Version' );
    }

    function check($VersionMap){
//        先获取最高版本号
        foreach ($VersionMap as $Config){

        }
    }
}