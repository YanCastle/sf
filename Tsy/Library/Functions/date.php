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
    if(is_numeric($birthday)&&strlen()){

    }if(strlen($birthday)==10){
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

/**
 * 设定执行时间函数
 * @param bool $time
 * @return bool|int
 */
function keep_time($time=false){
    static $t;
    if($time)$t=$time;
    return $t?$t:time();
}

/**
 * 时间转化
 * @param $str
 * @param string $format
 */
function get_time($str,$format='Y-m-d H:i:s'){
    $timestamp=0;
    if(is_numeric($str)){
        switch (strlen($str)){
            case 8:
                $Year = substr($str,0,4);
                $Month = substr($str,4,2);
                $Day = substr($str,6,2);
                if($Month<12&&$Year>=1000&&$Year<5000&&date('d',strtotime("$Year-$Month-$Day")==$Day)){
                    $timestamp=strtotime("$Year-$Month-$Day");
                }else{
                    $timestamp=$str;
                }
                break;
            case 13:
                $timestamp = substr($str,0,10);
                break;
        }
    }elseif($timestamp=strtotime($str)){

    }else{
        return false;
    }
    return $format?date($format,$timestamp):$timestamp;
}