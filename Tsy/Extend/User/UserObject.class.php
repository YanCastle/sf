<?php
/**
 * Created by PhpStorm.
 * User: castle
 * Date: 2016/08/02
 * Time: 22:46
 */

namespace Tsy\Extend\User;


trait UserObject
{
    function login($Account,$PWD){}
    function logout(){
        session(null);
    }
    function findAccount($Account){}
    function resetPWD($UID,$PWD,$Code,$RePWD=''){}
    function regist($Account,$PWD,$Params=[]){}
    function sendVerifyCode(){}
    function reLogin(){}
    function adminResetPWD($UID,$PWD){
        //验证权限
    }
    private function createVerifyCode(){}
    private function checkVerifyCode(){}
}