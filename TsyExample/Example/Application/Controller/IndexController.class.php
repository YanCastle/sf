<?php
/**
 * Created by PhpStorm.
 * User: Castle
 * Date: 2016/4/11
 * Time: 23:09
 */

namespace Application\Controller;
use Tsy\Library\Controller;
use Tsy\Plugs\File\File;

class IndexController extends Controller
{
    function index(){
        echo C('FILE_UPLOAD_TYPE'),"\r\n";
        var_dump(File::upload());
    }
    /**
     * 空操作
     * @param string $Action 方法名称
     * @param array|string $Data 数据
     */
    function _empty($Action,$Data){}
    function sleep(){
        sleep(10);
        return 'out';
    }
    function check(){
        return 'sds';
    }
}