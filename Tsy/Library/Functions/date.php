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
            case 10:
                $timestamp=$str;
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

//这个星期的星期一
// @$timestamp ，某个星期的某一个时间戳，默认为当前时间
// @is_return_timestamp ,是否返回时间戳，否则返回时间格式
function this_monday($timestamp=0,$is_return_timestamp=true){
    static $cache ;
    $id = $timestamp.$is_return_timestamp;
    if(!isset($cache[$id])){
        if(!$timestamp) $timestamp = time();
        $monday_date = date('Y-m-d', $timestamp-86400*date('w',$timestamp)+(date('w',$timestamp)>0?86400:-/*6*86400*/518400));
        if($is_return_timestamp){
            $cache[$id] = strtotime($monday_date);
        }else{
            $cache[$id] = $monday_date;
        }
    }
    return $cache[$id];

}

//这个星期的星期天
// @$timestamp ，某个星期的某一个时间戳，默认为当前时间
// @is_return_timestamp ,是否返回时间戳，否则返回时间格式
function this_sunday($timestamp=0,$is_return_timestamp=true){
    static $cache ;
    $id = $timestamp.$is_return_timestamp;
    if(!isset($cache[$id])){
        if(!$timestamp) $timestamp = time();
        $sunday = this_monday($timestamp) + /*6*86400*/518400;
        if($is_return_timestamp){
            $cache[$id] = $sunday;
        }else{
            $cache[$id] = date('Y-m-d',$sunday);
        }
    }
    return $cache[$id];
}

//上周一
// @$timestamp ，某个星期的某一个时间戳，默认为当前时间
// @is_return_timestamp ,是否返回时间戳，否则返回时间格式
function last_monday($timestamp=0,$is_return_timestamp=true){
    static $cache ;
    $id = $timestamp.$is_return_timestamp;
    if(!isset($cache[$id])){
        if(!$timestamp) $timestamp = time();
        $thismonday = this_monday($timestamp) - /*7*86400*/604800;
        if($is_return_timestamp){
            $cache[$id] = $thismonday;
        }else{
            $cache[$id] = date('Y-m-d',$thismonday);
        }
    }
    return $cache[$id];
}

//上个星期天
// @$timestamp ，某个星期的某一个时间戳，默认为当前时间
// @is_return_timestamp ,是否返回时间戳，否则返回时间格式
function last_sunday($timestamp=0,$is_return_timestamp=true){
    static $cache ;
    $id = $timestamp.$is_return_timestamp;
    if(!isset($cache[$id])){
        if(!$timestamp) $timestamp = time();
        $thissunday = this_sunday($timestamp) - /*7*86400*/604800;
        if($is_return_timestamp){
            $cache[$id] = $thissunday;
        }else{
            $cache[$id] = date('Y-m-d',$thissunday);
        }
    }
    return $cache[$id];

}

//这个月的第一天
// @$timestamp ，某个月的某一个时间戳，默认为当前时间
// @is_return_timestamp ,是否返回时间戳，否则返回时间格式

function month_firstday($timestamp = 0, $is_return_timestamp=true){
    static $cache ;
    $id = $timestamp.$is_return_timestamp;
    if(!isset($cache[$id])){
        if(!$timestamp) $timestamp = time();
        $firstday = date('Y-m-d', mktime(0,0,0,date('m',$timestamp),1,date('Y',$timestamp)));
        if($is_return_timestamp){
            $cache[$id] = strtotime($firstday);
        }else{
            $cache[$id] = $firstday;
        }
    }
    return $cache[$id];
}

//这个月的第一天
// @$timestamp ，某个月的某一个时间戳，默认为当前时间
// @is_return_timestamp ,是否返回时间戳，否则返回时间格式

function month_lastday($timestamp = 0, $is_return_timestamp=true){
    static $cache ;
    $id = $timestamp.$is_return_timestamp;
    if(!isset($cache[$id])){
        if(!$timestamp) $timestamp = time();
        $lastday = date('Y-m-d', mktime(0,0,0,date('m',$timestamp),date('t',$timestamp),date('Y',$timestamp)));
        if($is_return_timestamp){
            $cache[$id] = strtotime($lastday);
        }else{
            $cache[$id] = $lastday;
        }
    }
    return $cache[$id];
}

//上个月的第一天
// @$timestamp ，某个月的某一个时间戳，默认为当前时间
// @is_return_timestamp ,是否返回时间戳，否则返回时间格式

function lastmonth_firstday($timestamp = 0, $is_return_timestamp=true){
    static $cache ;
    $id = $timestamp.$is_return_timestamp;
    if(!isset($cache[$id])){
        if(!$timestamp) $timestamp = time();
        $firstday = date('Y-m-d', mktime(0,0,0,date('m',$timestamp)-1,1,date('Y',$timestamp)));
        if($is_return_timestamp){
            $cache[$id] = strtotime($firstday);
        }else{
            $cache[$id] = $firstday;
        }
    }
    return $cache[$id];
}

//上个月的第一天
// @$timestamp ，某个月的某一个时间戳，默认为当前时间
// @is_return_timestamp ,是否返回时间戳，否则返回时间格式

function lastmonth_lastday($timestamp = 0, $is_return_timestamp=true){
    static $cache ;
    $id = $timestamp.$is_return_timestamp;
    if(!isset($cache[$id])){
        if(!$timestamp) $timestamp = time();
        $lastday = date('Y-m-d', mktime(0,0,0,date('m',$timestamp)-1, date('t',lastmonth_firstday($timestamp)),date('Y',$timestamp)));
        if($is_return_timestamp){
            $cache[$id] = strtotime($lastday);
        }else{
            $cache[$id] =  $lastday;
        }
    }
    return $cache[$id];
}