<?php
/**
 * Created by PhpStorm.
 * User: Castle
 * Date: 2016/10/18
 * Time: 21:04
 */
/**
 * 获取年龄
 * @param $birthday
 * @return int
 */
function get_age($birthday) {
    $age = 0;
    $year = $month = $day = 0;
    if(strlen($birthday)==10){
        list($year,$month,$day)=explode('-',date('Y-m-d',$birthday));
    }elseif(strlen($birthday)==8&&is_numeric($birthday)){
        $year = substr($birthday,0,4);
        $month = substr($birthday,5,2);
        $day = substr($birthday,7,2);
    }elseif (is_array($birthday)) {
        extract($birthday);
    } else {
        if (strpos($birthday, '-') !== false) {
            list($year, $month, $day) = explode('-', $birthday);
            $day = substr($day, 0, 2); //get the first two chars in case of '2000-11-03 12:12:00'
        }
    }
    $age = date('Y') - $year;
    if (date('m') < $month || (date('m') == $month && date('d') < $day)) $age--;
    return $age;
}