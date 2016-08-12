<?php
namespace Tsy\Plugs\BaiDuYunWangPan;
/**
 * Created by PhpStorm.
 * User: castle
 * Date: 2016/08/12
 * Time: 20:17
 */
class BaiDuYunWangPan
{
    ### Auth servers
    const GaeUrl = 'https://bypyoauth.appspot.com';
    const OpenShiftUrl = 'https://bypy-tianze.rhcloud.com';
    const HerokuUrl = 'https://bypyoauth.herokuapp.com';

    function __construct()
    {
        //引入PHP文件
        include __DIR__.DIRECTORY_SEPARATOR.'libs/BaiduPCS.class.php';
    }
    function getAccessToken($AuthorizationCode){

    }
}