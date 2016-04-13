<?php
/**
 * Created by PhpStorm.
 * User: Castle
 * Date: 2016/4/13
 * Time: 21:11
 */

function out($data){
    return json_encode([
        'm'=>$_POST['_mid'],
        'd'=>$data,
        'i'=>$_POST['_i']
    ],JSON_UNESCAPED_UNICODE);
}
function dispatch($data){
    $d = json_decode($data,true);
    return [
        'i'=>isset($d['i'])?$d['i']:'Empty/_empty',
        'd'=>isset($d['d'])?$d['d']:[],
        't'=>isset($d['t'])?$d['t']:uniqid()
    ];
}