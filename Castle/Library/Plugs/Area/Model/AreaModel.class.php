<?php
/**
 * Created by PhpStorm.
 * User: castle
 * Date: 2015/11/24
 * Time: 18:56
 */
namespace Plugs\Area\Model;
use Think\Model;

class AreaModel extends Model{
    /**
     * 对象化
     * @param $AreaIDs
     */
    function areaObj($AreaIDs){
        if(is_numeric($AreaIDs)){
            $AreaIDs=[$AreaIDs];
        }
        //提取父类区域编号信息
        $ParentAreaIDs = array_key_value($AreaIDs,'ParentAreaID');
        if($ParentAreaIDs){
            //进行父类的处理
            $this->where(['AreaID'=>['in',$ParentAreaIDs]])->select();
        }
        return [];
    }
}