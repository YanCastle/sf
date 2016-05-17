<?php
/**
 * Created by PhpStorm.
 * User: Castle
 * Date: 2016/4/11
 * Time: 23:11
 */

namespace Tsy\Library;

/**
 * Class Controller
 * 请尽量不要在Controller中直接调用Model
 * @package Tsy\Library
 */
class Controller
{
    protected $className='';
    protected $swoole;
    public $Controller=[];
    public $PRIKey="";
    public $Params=[];

    function __construct()
    {
        $this->className = $this->getControllerName();
        if(file_exists(APP_PATH.DIRECTORY_SEPARATOR.$_GET['_m'].'Config/controller.php'))
            $this->Controller=include APP_PATH.DIRECTORY_SEPARATOR.$_GET['_m'].'Config/controller.php';
        if(isset($this->Controller[$_GET['_c']])&&
            isset($this->Controller[$_GET['_c']][$_GET['_a']])) {
            $this->PRIKey = $this->Controller[$_GET['_c']]['_pki'];
            $this->Params=$this->Controller[$_GET['_c']][$_GET['_a']];
        }
        $ObjectName = $_GET['_m'].'\\Object\\'.$_GET['_c'].'Object';
        if(class_exists($ObjectName)){
            $this->PRIKey = (new $ObjectName)->pk;
        }
    }
    function __call($name, $arguments)
    {
        $Object = $this->className.'Object';
        if(class_exists($Object)){
            $ObjectClass = new $Object();
            if(method_exists($ObjectClass,$name)){
                return call_user_func_array($ObjectClass,$arguments);
            }
        }
    }
    protected function getControllerName(){
        return substr(__CLASS__,0,strlen(__CLASS__)-10);
    }
    function set_swoole($swoole){
        $this->swoole=$swoole;
    }
    protected function send($UID,$data){
        //TODO 需要建立UID跟fd的连接信息，如果不是在swoole模式下还需要放到队列中去
    }
    function _empty($Action,$Data){
        $Object = $this->className;
        return class_exists($this->className)?controller($this->className.'/'.$Action,$Data,'','Object'):"{$this->className}/{$Action}方法不存在";
    }
    function get($ID=[]){
        if(!$ID){
            $ClassName=$_GET['_c'];
            $ObjectName=$ClassName.'Object';
            $NameSpace = implode('\\',[$_GET['_m'],'Object',$ObjectName]);
            if(!property_exists($this,$ClassName.'Object')){
                $this->$ObjectName=new $NameSpace;
            }
            $ID = $_POST[$this->$ObjectName->pk];
        }
        if($ID){
            $ClassName=$_GET['_c'];
            if(property_exists($this,$ClassName.'Object')){
                $ObjectName=$ClassName.'Object';
                $objs=$this->$ObjectName->get($ID);
                return $objs;
            }
        }
//        if($ID){
//            return array_values(array_values(D($_GET['_c'])->obj($ID)))[0];
//        }
//        if(isset($this->Controller[$_GET['_c']])&&isset($this->Controller[$_GET['_c']][$_GET['_a']])){
//            return array_values(array_values(D($_GET['_c'])->obj([$_REQUEST[array_keys($this->Controller[$_GET['_c']][$_GET['_a']])[0]]])))[0];
//        }else{
//            return FALSE;
//        }
    }
    function gets($P=1,$N=20,$Sort=[]){
        if($this->PRIKey){
            $Model = D($_GET['_c']);
            if(isset($_REQUEST[$this->PRIKey.'s'])&&is_array($_REQUEST[$this->PRIKey.'s'])){
                $IDs = $Model->where([$this->PRIKey=>['in',$_REQUEST[$this->PRIKey.'s']]])->page($P,$N)->order($Sort)->getField($this->PRIKey,true);
            }else{
                $IDs = $Model->page($P,$N)->order($Sort)->getField($this->PRIKey,true);
            }
            return $IDs!==false?[
                'L'=>array_values($Model->obj($IDs)),
                'P'=>$P,
                'N'=>$N,
                'T'=>isset($_REQUEST[$this->PRIKey.'s'])&&is_array($_REQUEST[$this->PRIKey.'s'])?count($_REQUEST[$this->PRIKey.'s']):$Model->count()
            ]:FALSE;
        }else{
            return FALSE;
        }
    }
    function save(array $Params){
        if($this->PRIKey&&isset($_REQUEST[$this->PRIKey])&&is_numeric($_REQUEST[$this->PRIKey])){
            $Model = D($_GET['_c']);
            return $Model->where([$this->PRIKey=>$_REQUEST[$this->PRIKey]])->save($Params);
        }else{return FALSE;}
    }
    function del(){
        if($this->PRIKey&&isset($_REQUEST[$this->PRIKey])&&(is_numeric($_REQUEST[$this->PRIKey])||is_array($_REQUEST[$this->PRIKey]))){
            $IDs=[];
            if(is_array($_REQUEST[$this->PRIKey])){
                foreach($_REQUEST[$this->PRIKey] as $ID){
                    if(is_numeric($ID)){
                        $IDs[]=$ID;
                    }else{
                        return false;
                    }
                }
            }elseif(is_numeric($_REQUEST[$this->PRIKey])){
                $IDs=[$_REQUEST[$this->PRIKey]];
            }
            if($IDs){
                $Model = D($_GET['_c']);
                $Deletes = $Model->obj($IDs);
                if($Model->where([$this->PRIKey=>['in',$IDs]])->delete()){
                    return array_values($Deletes);
                }else{return FALSE;};
            }else{
                return FALSE;
            }
        }else{return FALSE;}
    }
    function search($keyword='',$W=[],$P=1,$N=20,$Sort=[]){
        $where = [];
        if($keyword&&isset($this->Controller[$_GET['_c']]['_search'])){
            foreach($this->Controller[$_GET['_c']]['_search'] as $column){
                $where[$column]=['like',"%{$keyword}%"];
            }
        }
        $Relation=[];//关系处理规则定义
        if(is_array($W)&&count($W)){
            foreach($W as $k=>$v){
                if(substr($k,0,1)=='_'){
                    //这是特殊处理字段
                }else{
                    $Router = explode('.',$k);
                    if(count($Router)==2){
                        //初始化关联查询数组
                        if(!isset($Relation[$Router[0]])){$Relation[$Router[0]]=[];}
                        $Relation[$Router[0]][$Router[1]]=$v;
                    }else{
                        $where[$k]=$v;
                    }
                }
            }
        }
        //检测关联查询
        $IDs=[];
        if(count($Relation)){
            foreach($Relation as $ModelName=>$ModelWhere){
                if($ModelName&&$ModelWhere){
                    $Rs = D($ModelName)->where($ModelWhere)->order($Sort)->getField($this->PRIKey,TRUE);
                    if($Rs){
                        if(strtoupper($W['_logic'])=='AND'){
                            $IDs=array_intersect($IDs,$Rs);
                        }else{
                            $IDs=array_merge($IDs,$Rs);
                        }
                    }
                }
            }
        }
        if($where){
            $IDs = D($_GET['_c'])->where($where)->order($Sort)->getField($this->PRIKey,TRUE);
        }
        $IDs=array_unique($IDs);
        //开始分页处理
        if(isset($W['_logic'])&&$where){
            $Rs = D($_GET['_c'])->order($Sort)->where($where)->getField($this->PRIKey,TRUE);
            if(strtoupper($W['_logic'])=='AND'){
                $IDs = array_intersect($IDs,$Rs);
            }else{
                $IDs = array_merge($IDs,$Rs);
            }
        }
        $IDs=array_unique($IDs);
//	    分页控制
        $PRIKeyIDs = [];
        for($i=$P-1;$i<($P*$N-1);$i++){
            if($IDs[$i])
                $PRIKeyIDs[]=$IDs[$i];
        }
        if(false!==$IDs){
            return [
                'L'=>array_values(D($_GET['_c'])->obj($PRIKeyIDs)),
                'P'=>$P,
                'N'=>$N,
                'T'=>count($IDs)
            ];
        }else{
            return FALSE;
        }
    }
    function add(){
        $ID = D($_GET['_c'])->add($_POST);
        return $ID?array_values(D($_GET['_c'])->obj([$ID]))[0]:false;
    }
}