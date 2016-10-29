<?php
/**
 * Created by PhpStorm.
 * User: Castle
 * Date: 2016/10/26
 * Time: 20:14
 */
function SqlServer2MySql($file, $out)
{
    if (is_file($file)) {
        $fin = fopen($file, 'r');//读入数据
        $fout = fopen($out, 'w');//写出数据
        $replace = [
            [

            ], [

            ]
        ];
    }
}