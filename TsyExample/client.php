<?php
/**
 * Created by PhpStorm.
 * User: Castle
 * Date: 2016/4/13
 * Time: 21:24
 */
$str = json_encode([
    'i'=>'Test/test',
    'd'=>['s'=>5],
    't'=>'fwe'
],JSON_UNESCAPED_UNICODE);
//$client = fsockopen('10.10.13.22',65502);
//fputs($client,$str);
//fclose($client);
echo $str;