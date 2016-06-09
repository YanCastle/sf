<?php
/**
 * Created by PhpStorm.
 * User: castle
 * Date: 6/9/16
 * Time: 11:08 PM
 */

namespace Application\Controller;


use Tsy\Library\Controller;
use Tsy\Plugs\Async\Async;
use Tsy\Plugs\Async\AsyncContext;

class AsyncController extends Controller
{
    function async(){
        Async::create([$this,'e'])->then([$this,'a'])->start(new AsyncContext());
    }
    function e(&$promise){
        echo 'e';
        return $promise;
    }
    function a(&$promise){
        echo 'a';
        return $promise;
    }
}