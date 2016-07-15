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
    function __construct()
    {
        C([
            'DD_DIR_ROOT'=>RUNTIME_PATH.DIRECTORY_SEPARATOR.'Dingtalk'.DIRECTORY_SEPARATOR,
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
    }

}