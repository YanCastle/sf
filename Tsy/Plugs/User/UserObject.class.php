<?php
/**
 * Created by PhpStorm.
 * User: 鄢鹏权
 * Date: 2016/11/6
 * Time: 22:55
 */

namespace Tsy\Plugs\User;


use Tsy\Library\Object;

class UserObject extends Object
{
    use UserTrait;

    /**
     * 权限检查
     * @param $Module
     * @param $Class
     * @param $Action
     * @param string $Layer
     * @param bool $UID
     */
    static function check($Module,$Class,$Action,$Layer='Controller',$UID=false){
        if($UID===false){
            $UID=session('UID');
            $UID = $UID?$UID:0;
        }
//        获取该用户所属用户组
//        $GIDs=M('UserGroup')->where(['UID'=>$UID])->getField('GID',true);
//        if(!$GIDs||!is_array($GIDs)){return false;}//该用户不属于任何组
//        $GIDs = $GIDs?$GIDs:DEFAULT_USER_GROUP;//获取默认的用户组
        $Conditons = M('UserAccessSearch')->where(['Module'=>$Module,'Class'=>$Class,'Action'=>$Action,'Type'=>$Layer,'UID'=>$UID])->field('Condition,Title')->select();
        if($Conditons){
//            $Conditons 规则 
        }else{
            return false;
        }
    }
    function loginByOpenID($OpenID){
        if($UID=M('User')->where(['OpenID'=>$OpenID])->getField('UID')){
            return $this->loginSuccess($UID);
        }
        return false;
    }
}