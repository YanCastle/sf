<?php
/**
 * Created by PhpStorm.
 * User: castle
 * Date: 2015/11/24
 * Time: 18:47
 */

namespace Plugs\Area;


use Think\Controller;
use Plugs\Area\Model\AreaModel;

class Area extends Controller
{
    protected $AreaModel;
    function __construct(){
        parent::__construct();
        $this->AreaModel=new AreaModel();
    }
    /**
     * 获取单个区域的信息
     * @param int $AreaID 区域编号，整形
     */
    function get($AreaID){
        return $this->AreaModel->areaObj($AreaID);
    }

    /**
     * @param array $AreaIDs 区域编号的数组 [1,2.3]
     * @param int $P 分页中的页码
     * @param int $N 分页控制中的每页数量
     * @param string $Sort 排序规则
     */
    function gets($AreaIDs=[],$P=1,$N=20,$Sort=''){
        $Areas = $this->AreaModel->where(['AreaID'=>['in',$AreaIDs]])->order($Sort)->page($P,$N)->getField('AreaID',true);
        return $Areas?$this->AreaModel->areaObj($Areas):[];
    }

    /**
     *
     * @param array $AreaID
     * @param array $Params 要修改的参数列表 键值对
     */
    function save($AreaID,array $Params){
        return $this->AreaModel->where(['AreaID'=>$AreaID])->save($Params)?$this->AreaModel->areaObj($AreaID):false;
    }

    /**
     *
     * @param array $AreaID
     */
    function del($AreaID){
        return $this->AreaModel->where(['AreaID'=>$AreaID])->delete();
    }

    /**
     * 搜索
     * @param string $keyword
     * @param array $W
     * @param int $P
     * @param int $N
     * @param string $Sort
     */
    function search($keyword='',$W=[],$P=1,$N=20,$Sort=''){
        if($keyword){
            $handleKyeword='%'.trim($keyword).'%';
            $keywords['AreaName']=[
                'like',
                $handleKyeword
            ];
            $keywords['AreaName']=array('like','%'.trim($keyword).'%');
        }
        $res=$this->AreaModel->where($keywords)->page($P,$N)->order($Sort)->select();
        $p=$this->AreaModel->where($keywords)->field('ParentAreaID')->select();
        $result=[];
        for($j=0;$j<count($p);$j++){
            for(;$p[$j]>0;$p[$j]=$this->AreaModel->where('AreaID='.$p[$j]['ParentAreaID'])->getField('ParentAreaID')){
                $res[$j][]=$this->AreaModel->where('AreaID='.$p[$j]['ParentAreaID'])->page($P,$N)->find();
            }
            if(count($res[$j])>=1){                                  //由小单位到大单位排序
                for($s=count($res[$j])-6;$s>0;$s--){
                    $res[$j][$s-1]['Parent']=$res[$j][$s];
                }
            }
            $result[]=$res[$j];
            $test=$result[$j][0];
            unset($result[$j][0]);
            $result[$j]['Parent']=$test;
            unset($result[$j][1]);
        }
        return $result;
    }
}