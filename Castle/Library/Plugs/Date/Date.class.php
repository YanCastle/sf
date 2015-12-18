<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 12/11/15
 * Time: 10:24 PM
 */

namespace Plugs\Date;


class Date
{
    /**
     * 根据时间戳获取季度
     * @param int $timestamp
     * @return int
     */
    static function quarter($timestamp=0){
        if(!$timestamp){$timestamp=time();}
        return intval(date('m',$timestamp)/3)+1;
    }

    /**
     * 创建时间搓
     * @param int $year
     * @param int $month
     * @param int $day
     * @param int $hour
     * @param int $min
     * @param int $s
     * @return int
     */
    static function make_time($year = 0, $month = 0, $day = 0, $hour = 0, $min = 0, $s = 0)
    {
        $date = explode('-', date('Y-m-d-H-i-s'));
        $year = $year < 1 ? $date[0] + $year : $year;
        $month = $month < 1 ? $date[1] + $month : $month;
        $day = $day < 1 ? $date[2] + $day : $day;
        return mktime($hour, $min, $s, $month, $day, $year);
    }
}