<?php
/**
 * Copyright (c) 2016. Lorem ipsum dolor sit amet, consectetur adipiscing elit.
 * Morbi non lorem porttitor neque feugiat blandit. Ut vitae ipsum eget quam lacinia accumsan.
 * Etiam sed turpis ac ipsum condimentum fringilla. Maecenas magna.
 * Proin dapibus sapien vel ante. Aliquam erat volutpat. Pellentesque sagittis ligula eget metus.
 * Vestibulum commodo. Ut rhoncus gravida arcu.
 */

/**
 * Created by PhpStorm.
 * User: castle
 * Date: 6/24/16
 * Time: 10:41 PM
 */

/**
 * 文件监控，需要基于Inotify扩展
 * Class Inotify
 */
class Inotify
{
    protected $inotify;
    protected $watched=[];
    function __construct()
    {
        $this->inotify = inotify_init();
    }

    function watch($Dir){
        if(is_dir($Dir)||is_file($Dir)){
            $this->watched[]=$Dir;
            inotify_add_watch($this->inotify,$Dir,IN_MODIFY|IN_CREATE|IN_DELETE|IN_MOVED_FROM|IN_MOVED_TO|IN_DELETE_SELF|IN_MOVE_SELF|IN_MOVE);
            return true;
        }
        return false;
    }
    function unwatch($Dir){
        if(in_array($Dir,$this->watched )){
            inotify_rm_watch($this->inotify,$Dir );
        }
    }
    function start(callable $function){
        swoole_event_add($this->inotify,function($fd)use($function){
            $events = inotify_read($fd);
            if($events){
                foreach ($events as $event){
                    //TODO 检测变更类型，回调用户函数
                    echo var_export($event);
                }
            }
        });
    }
    function stop(){}
}
