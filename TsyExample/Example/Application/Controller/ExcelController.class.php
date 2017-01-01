<?php
/**
 * Created by PhpStorm.
 * User: 鄢鹏权
 * Date: 2016/12/30
 * Time: 20:18
 */

namespace Application\Controller;


use Tsy\Plugs\Excel\Excel;

class ExcelController
{
    function index(){
//        $Excel = new Excel();
//        $Data = $Excel->read('start.xlsx');
//        $data = $Data['2015'];
//        file_put_contents('d',json_encode($data));
        $data = json_decode(file_get_contents('d'));
        $rowNumber = count($data)-1;
        $i=3;//i决定了pi和v的个数
        $Pi1=['A18','A19','A20',];
        $Pi2=['B18','B19','B20',];
        $V1=['C18','C19','C20',];
        $V2=['D18','D19','D20',];
        $W1=['E18','E19'];
        $W2=['F18','F19'];
        $U=['G18','G19'];
//        for ($o=0;$o<$rowNumber;$o++){
//
//        }
//        $data = explode('|',str_replace(',','',implode('|',$data)));
        $str = [];
        unset($data[0]);
//        foreach ($data)
        foreach ($data as $i=>$row){
            $data[$i]=explode('|',str_replace(',','',implode('|',$row)));
        }
        foreach ($data as $i=>$row){
//            if($i===0){unset($data)};

            $data[$i]=$row;
            //第一个
            $P1str=$this->repeat([$row[1],$row[2],$row[3],],$Pi1);//+
            $V2str=$this->repeat([$row[1],$row[2],$row[3],],$V2);//-
            $P2str=$this->repeat([$row[1],$row[2],$row[3],],$Pi2);//+
            $W2str=$this->repeat([$row[4],$row[5],],$W2);//+
            $str[] = "($P1str)+($V2str)-($P2str)+($W2str)=1";
            //第二组
            $p1str=[];
            $w1str = [];
            foreach ($data as $o=>$p1row){
                $p1str[] = $this->repeat([$p1row[1],$p1row[2],$p1row[3],],$Pi1);//+
                $w1str[] = $this->repeat([$p1row[4],$p1row[5],],$W1);//+
            }
            $p1str = implode('+',$p1str);
            $w1str = implode('+',$w1str);
            $str[]="($p1str)-($w1str)>=0";
            //第三组
            $v2str=[];
            $w2str = [];
            $p2str=[];
            $ustr=[];
            foreach ($data as $o=>$vrow){
                $v2str[] = $this->repeat([$vrow[1],$vrow[2],$vrow[3],],$V2);//+
                $p2str[] = $this->repeat([$vrow[1],$vrow[2],$vrow[3],],$Pi2);//+
                $w2str[] = $this->repeat([$vrow[4],$vrow[5],],$W2);//+
                $ustr[] = $this->repeat([$vrow[6],$vrow[8],],$U);//+
            }
            $v2str = implode('+',$v2str);
            $w2str = implode('+',$w2str);
            $p2str = implode('+',$p2str);
            $ustr = implode('+',$ustr);
            $str[]="($v2str)+($w2str)-($p2str)-($ustr)>=0";
            //第四组

        }
        echo $str;
    }
    function repeat($data,$t,$split='+'){
        $str = [];
        foreach ($data as $i=>$r){
            $str[]="$r*$t[$i]";
        }
        return implode($split,$str);
    }
}