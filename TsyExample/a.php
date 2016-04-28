<?php
/**
 * Created by PhpStorm.
 * User: Castle
 * Date: 2016/4/12
 * Time: 23:31
 */
//echo 'a';
$queue = new SplQueue();
$queue->add(0,2);
$b = serialize($queue);
$v = unserialize($b);
$a=1;