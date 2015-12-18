<?php
/**
 * Created by PhpStorm.
 * User: castle
 * Date: 2015/11/25
 * Time: 13:29
 */

namespace Plugs\Strings;

class Strings
{
    /**
     * mb扩展的字符串截取，安全的字符串截取
     * @param $str
     * @param int $start
     * @param $length
     * @param string $charset
     * @param bool $suffix
     * @return string
     */
    static function msubstr($str, $start=0, $length, $charset="utf-8", $suffix=true)
    {
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

    /**
     * 安全的字符串长度检测
     * @param $str
     * @return int
     */
   static function mstrlen($str){
        if(function_exists('mb_strlen'))
            return mb_strlen($str);
        return strlen($str);
    }

    /**
     * 安全的字符串打散函数
     * @param $string
     * @param $step
     * @return array
     */
    static function mexplode($string,$step=1){
        $arr=[];
        for($i=0;$i<mstrlen($string);$i+=$step){
            $arr[]=msubstr($string,$i,1);
        }
        return $arr;
    }
}