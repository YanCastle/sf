<?php
/**
 * Created by PhpStorm.
 * User: castle
 * Date: 2015/11/24
 * Time: 23:19
 */
namespace Plugs\Curl;
use Core\Controller;

class Curl extends Controller{
    public $UserID=false;
    public $error;
    public $info;
    function post($url,$data,$getParam='',$cookie_id=false,$referer=false,$header=false){
        return $this->curl($url,$getParam,$data,$cookie_id,$referer,$header);
    }
    function get($url,$getParam='',$cookie_id=false,$referer=false,$header=false){
        return $this->curl($url,$getParam,[],$cookie_id,$referer,$header);
    }
    function put($url,$data){
        $ch=curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
//        curl_setopt($ch,CURLOPT_PUT,true);
        curl_setopt($ch,CURLOPT_POST,false);
        curl_setopt($ch,CURLOPT_POSTFIELDS,$data);
        $res=curl_exec($ch);
        $this->error = curl_error($ch);
        $this->info = curl_getinfo($ch);
        curl_close($ch);
        return $res;
    }
    function delete($url,$data){
        $ch=curl_init();
        curl_setopt($ch,CURLOPT_URL,$url);
        curl_setopt($ch,CURLOPT_CUSTOMREQUEST ,'DELETE');
        curl_setopt($ch,CURLOPT_POSTFIELDS,$data);
        $res=curl_exec($ch);
        $this->error = curl_error($ch);
        $this->info = curl_getinfo($ch);
        curl_close($ch);
        return $res;
    }
    function down(){}
    function upload($url,$filename,$path,$type,$cookie_id=false){
        if($this->UserID&&false===$cookie_id){$cookie_id=$this->UserID;}
        $data = array(
            'pic'=>'@'.realpath($path).";type=".$type.";filename=".$filename
        );
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true );
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        if($cookie_id){
            $cookie_jar = md5($cookie_id);
            curl_setopt($ch,CURLOPT_COOKIEFILE,$cookie_jar);
            curl_setopt($ch,CURLOPT_COOKIEJAR,$cookie_jar);
        }
        // curl_getinfo($ch);
        $return_data = curl_exec($ch);
        $this->error = curl_error($ch);
        curl_close($ch);
        return $return_data;
    }
    function setUserID($UserID){
        $this->UserID=$UserID;
    }
    function curl($url,$get=[],$post=[],$cookie_id=false,$referer=false,$header=false){
        if($this->UserID&&false===$cookie_id){$cookie_id=$this->UserID;}
        $ch = curl_init($url.'?'.http_build_query($get));
        if($post){
            curl_setopt($ch,CURLOPT_POSTFIELDS,is_string($post)?$post:http_build_query($post));
            curl_setopt($ch,CURLOPT_POST,true);
        }
        if($cookie_id){
            $cookie_jar = md5($cookie_id);
            curl_setopt($ch,CURLOPT_COOKIEFILE,$cookie_jar);
            curl_setopt($ch,CURLOPT_COOKIEJAR,$cookie_jar);
        }
        if($header){
            curl_setopt($ch,CURLOPT_HTTPHEADER,$header);
        }
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); // https请求 不验证证书和hosts
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
//	$header = ["content-type: application/x-www-form-urlencoded;
//charset=UTF-8"];
//	curl_setopt($ch,CURLOPT_HTTPHEADER,$header);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
//	curl_setopt($ch,CURLOPT_ENCODING,'gzip');
        curl_setopt($ch,CURLOPT_FOLLOWLOCATION, true);
        if($referer)
            curl_setopt($ch,CURLOPT_REFERER,$referer);
        curl_setopt($ch,CURLOPT_USERAGENT,'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/35.0.1916.153 Safari/537.36 SE 2.X MetaSr 1.0');
        $rs = curl_exec($ch);
        $this->error = curl_error($ch);
        $this->info = curl_getinfo($ch);
        curl_close($ch);
//    var_dump($rs,$error,$info);
        return $rs;
    }
    function getLastErr(){
        return $this->error;
    }
    function getLastInfo(){
        return $this->info;
    }
}