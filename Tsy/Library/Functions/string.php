<?php
/**
 * Created by PhpStorm.
 * User: 鄢鹏权
 * Date: 2017/03/16
 * Time: 22:10
 */

/**
 * @param $str
 * @param int $start
 * @param $length
 * @param string $charset
 * @param bool $suffix
 * @return string
 */
function msubstr($str, $start=0, $length=false, $charset="utf-8", $suffix=true)
{
    if(false===$length){$length=$start<0?abs($start):(mstrlen($start)-$start);}
    // 加载php_mbstring扩展时有效
    if (function_exists("mb_substr"))
        return mb_substr($str, $start, $length, $charset);
    // PHP 5+版本有效
    if (function_exists("iconv_substr"))
        return iconv_substr($str, $start, $length, $charset);

    $re['utf-8'] = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
    $re['gb2312'] = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
    $re['gbk'] = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
    $re['big5'] = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";
    preg_match_all($re[$charset], $str, $match);
    $slice = implode("", array_slice($match[0], $start, $length));
    if ($suffix)
        return $slice."...";
    else
        return $slice;
}
function mstrlen($str){
    if(function_exists('mb_strlen'))
        return mb_strlen($str);
    return strlen($str);
}

/**
 * 字符串转换为数组，解决中文转换问题
 * @param $string
 * @return array
 */
function mexplode($string){
    $arr=[];
    for($i=0;$i<mstrlen($string);$i++){
        $arr[]=msubstr($string,$i,1);
    }
    return $arr;
}
