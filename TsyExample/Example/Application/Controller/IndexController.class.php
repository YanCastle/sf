<?php
/**
 * Created by PhpStorm.
 * User: Castle
 * Date: 2016/4/11
 * Time: 23:09
 */

namespace Application\Controller;


use Tsy\Library\Controller;
use Tsy\Library\Model;

class IndexController extends Controller
{
    function index(){
        //如果这儿是return的字符串，则会作为错误信息返回
        //如果return的数组则是有效消息
        $M = new Model();
        $M->add(['a'=>1]);
    }
    /**
     * 空操作
     * @param string $Action 方法名称
     * @param array|string $Data 数据
     */
    function _empty($Action,$Data){}
}