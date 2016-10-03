<?php
/**
 * Created by PhpStorm.
 * User: castle
 * Date: 2016/07/27
 * Time: 9:40
 */

namespace Tsy\Library;
/**
 * Class Report
 * @package Tsy\Library
 */
class Report extends Controller
{
    public $ReportModel='';//要查询数据的Model名称
    public $ReportXColumn=[];//X轴限定信息配置
    public $ReportValueColumn=[];//其他值限定信息配置

    function report($Config=[],$Output='EChart'){
//        $Config=[
//            [
////                'Zh'=>'',
//                'Column'=>'1',
//                'Start'=>'',
//                'End'=>'',
//                'Size'=>''
//            ],
//            [
//                'Column'=>2,
//            ]
//        ];
        $X=$Others=$ColumnConfig=[];
        //配置检测开始
        foreach ($Config as $config){
            if(isset($config['Column'])){
                if(isset($config['Start'])||isset($config['End'])||isset($config['Size'])){
                    //表示这是X坐标
                    $X=$config;
                }else{
                    $Others[]=$config;
                    $ColumnConfig[$config['Column']]=isset($config['Func'])?strtoupper($config['Func']):'SUM';
                }
            }else{
                return '配置结构不正确';
            }
        }
        if(!$X){
            return 'X坐标配置不正确,没法识别X坐标';
        }
        $Fields=array_column($Others,'Column');
        $Fields[]=$X['Column'];
        //生成Where配置
        $Where=[];
        $Size=1;
        foreach ($X as $k=>$config){
            switch ($k){
                case 'Start':
                    $Where[$X['Column']]=isset($Where[$X['Column']])?($Where[$X['Column']][0]=='elt'?['between',[$config,$Where[$X['Column']][1]]]:['egt',$config]):['egt',$config];
                    break;
                case 'End':
                    $Where[$X['Column']]=isset($Where[$X['Column']])?($Where[$X['Column']][0]=='egt'?['between',[$Where[$X['Column']][1],$config]]:['elt',$config]):['elt',$config];
                    break;
                case 'Size':
                    $Size=$config;
                    break;
                default:break;
            }
        }
        //开始查询数据
        $Model = new Model($this->ReportModel);
        $Data = $Model->where($Where)->field($Fields)->select();
//        开始按Size进行分组
        $Data=array_group($Data,$X['Column'],$X['Start'],$X['End'],$Size);
        $Data=array_column_function($Data,$ColumnConfig);
        return $Data;
    }
    
}