<?php
/**
 * Created by PhpStorm.
 * User: castle
 * Date: 2016/6/29
 * Time: 19:30
 */

namespace Application\Controller;


use Tsy\Library\Controller;

class ViewController extends Controller
{
    function view(){
        $this->assign(['a'=>1]);
        $this->display();
    }
}