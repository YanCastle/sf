<?php
/**
 * Created by PhpStorm.
 * User: Castle
 * Date: 2016/4/12
 * Time: 23:31
 */
//echo 'a';
$Redis=new \Tsy\Library\Cache\Driver\Redis();
$Redis->set('blibli','123456');
$rs=$Redis->get('blibli');
echo $rs;