<?php
/**
 * Created by PhpStorm.
 * User: castle
 * Date: 2015/12/09
 * Time: 21:03
 */
namespace Plugs\Judge;

use Plugs\Controller;
use Plugs\Judge\Model\JudgeLogModel;
use Plugs\Judge\Model\JudgeModel;

class Judge extends Controller
{
    function __construct()
    {
        parent::__construct();
    }
//    审核结果

    function judge($JudgeID,$Result,$Memo,$Method='',$UID='',$Attach='',$Extend=''){
        $JudgeModel=new JudgeModel();
        $JudgeLogModel=new JudgeLogModel();
        $Fields='JudgeID,Result,Memo,Method,UID,Attach,Extend';
        $data = [];
        foreach(explode(',',$Fields) as $field){
            $data[$field]=I($field);
        }
        $data['Time']=time();
        $LogID=$JudgeLogModel->add($data);
        if($LogID){
            if($Result==1){
//                审核通过
                $rs=$JudgeModel->where(['JudgeID'=>$JudgeID])->save(['LastTime'=>$data['Time'],'PassTime'=>$data['Time'],'PassMethod'=>$Method,'PassUID'=>$UID]);
                if(!$rs){
                    return false;
                }else{
                    return $LogID;
                }
            }else{
                $rs=$JudgeModel->where(['JudgeID'=>$JudgeID])->save(['LastTime'=>$data['Time']]);
            }
        }else{
            return false;
        }


    }
}