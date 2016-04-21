<?php
/**
 * Created by PhpStorm.
 * User: Castle
 * Date: 2016/4/11
 * Time: 23:09
 */

namespace Application\Controller;

use Tsy\Library\Controller;
use Tsy\Plugs\WebQQ\WebQQ;

class IndexController extends Controller
{
    function index(){
        //如果这儿是return的字符串，则会作为错误信息返回
        //如果return的数组则是有效消息
        $WebQQ = new WebQQ(RUNTIME_PATH.DIRECTORY_SEPARATOR.'qr.png',490523604);
        if(!$WebQQ->autoLogin()){
            $WebQQ->downQrcode();
            $NickName = $WebQQ->login();
            while (!$NickName){
                $NickName = $WebQQ->login();
            }
        }
        $WebQQ->init();
        while (true){
            $value=$WebQQ->poll();
//            sleep(2);
            echo json_encode($value,JSON_UNESCAPED_UNICODE);
        }
    }
    /**
     * 空操作
     * @param string $Action 方法名称
     * @param array|string $Data 数据
     */
    function _empty($Action,$Data){}
}