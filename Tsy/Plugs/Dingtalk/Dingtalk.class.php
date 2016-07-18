<?php

/**
 * Created by PhpStorm.
 * User: castle
 * Date: 2016/7/15
 * Time: 10:22
 */
class Dingtalk
{
    protected $IsOk=true;
    protected $DD_DIR_ROOT=__DIR__.DIRECTORY_SEPARATOR.'isv'.DIRECTORY_SEPARATOR;
    function __construct()
    {
        C([
            'DD_DIR_ROOT'=>$this->DD_DIR_ROOT,
            'DD_OAPI_HOST'=>'https://oapi.dingtalk.com',
        ]);
        foreach ([
            'DD_CREATE_SUITE_KEY','DD_SUITE_KEY','DD_SUITE_SECRET','DD_TOKEN','DD_APPID','DD_ENCODING_AES_KEY'
                 ] as $key){
            if(!C($key)){
                L('未正确配置钉钉参数');
                $this->IsOk=false;
            }
        }
        require_once($this->DD_DIR_ROOT . "util/Http.php");
        require_once($this->DD_DIR_ROOT . "util/Log.php");
        require_once($this->DD_DIR_ROOT . "util/Cache.php");
        require_once($this->DD_DIR_ROOT . "api/Auth.php");
        require_once($this->DD_DIR_ROOT . "api/User.php");
        require_once($this->DD_DIR_ROOT . "api/Message.php");
        require_once($this->DD_DIR_ROOT . "api/ISVClass.php");
        require_once($this->DD_DIR_ROOT . "api/Activate.php");
        require_once($this->DD_DIR_ROOT . "crypto/DingtalkCrypt.php");
        require_once($this->DD_DIR_ROOT . "api/ISVService.php");
    }

}