<?php
/**
 * Created by PhpStorm.
 * User: castle
 * Date: 2015/12/17
 * Time: 12:26
 */

include 'Library/function/functions.php';
include 'Castle.class.php';
C([
    'CASTLE_PATH'=>__DIR__,
    'LIB_PATH'=>__DIR__.'/Library',
    'EXT'=>'.class.php',
    'DEFAULT_C_LAYER'=>'Controller',
    'APP_USE_NAMESPACE'=>true,
    'APP_PATH'=>APP_PATH
]);
$Castle=new Castle();
$Castle->start();