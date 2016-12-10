<?php
/**
 * Created by PhpStorm.
 * User: castle
 * Date: 2015/11/24
 * Time: 23:19
 */
namespace Tsy\Plugs\Curl;

class Curl {
    public $UserID=false;
    public $error;
    public $info;
    static $METHOD_GET='GET';
    static $METHOD_POST='POST';

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
    /**
     * 采集远程文件
     * @access public
     * @param string $remote 远程文件名
     * @param string $local 本地保存文件名
     * @return mixed
     */
    static function down($remote,$local){
        $cp = curl_init($remote);
        $fp = fopen($local,"w");
        curl_setopt($cp, CURLOPT_FILE, $fp);
        curl_setopt($cp, CURLOPT_HEADER, 0);
        curl_exec($cp);
        curl_close($cp);
        fclose($fp);
    }

    /**
     * 图片下载
     * @param $remote
     * @param $local
     * @return string
     */
    static function Picturedown($remote,$local){
        $file = $local.'/'.uniqid().'.jpg';
        Http::curlDownload($remote,$file);
        return $file;
//        if(!is_dir(dirname($local)))mkdir(dirname($local));
//        $cp = curl_init($remote);
//        curl_setopt($cp, CURLOPT_FILE, $fp);
//        curl_setopt($cp, CURLOPT_HEADER, true);
//        curl_setopt($cp, CURLOPT_RETURNTRANSFER, 1 );
//        $heads=curl_exec($cp);
//        curl_close($cp);
//        $Files= explode("\r\n",$heads);
//        $Type=substr($Files[3],-4,3);
//        for($i=0;$i<=7;$i++){
//            unset($Files[$i]);
//        }
//        $img=implode('',$Files);
//        $local=$local.'/'.uniqid().'.'.$Type;
//        $fp = fopen($local,"w");
//        fwrite($fp, $img);
//        fclose($fp);
////TODO        检测内容长度，判断下载结果，如果出错发送报警
//        $flog = fopen(RUNTIME_PATH.'wedownload.log','a');
//        fwrite($flog,json_encode([
//            'length'=>strlen($img),
//            'save'=>$local
//        ],JSON_UNESCAPED_UNICODE)."\r\n");
//        fclose($flog);
//        return $local;
    }
    function upload($url,$path,$cookie_id=false,$header=[]){
        if($this->UserID&&false===$cookie_id){$cookie_id=$this->UserID;}
        $data = array(
            'pic'=>new \CURLFile($path)
        );
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true );
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        if($header)
            curl_setopt($ch,CURLOPT_HTTPHEADER,$header);
        if($cookie_id){
            $cookie_jar = RUNTIME_PATH.'/Cookies/'.md5($cookie_id);
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
        $ch = curl_init($url.(is_array($get)&&$get?('?'.http_build_query($get)):''));
        if($post){
            curl_setopt($ch,CURLOPT_POSTFIELDS,is_string($post)?$post:http_build_query($post));
            curl_setopt($ch,CURLOPT_POST,true);
        }
        if($cookie_id){
            $cookie_jar = RUNTIME_PATH.'/Cookies/'.md5($cookie_id);
            if(!is_dir(dirname($cookie_jar))){
                @mkdir(dirname($cookie_jar),0777,true);
            }
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

    /**
     * @param array $config 二维数组，0地址，1存储名，自动后缀检测
     */
    static function multi_down(array $config){
        $mch = curl_multi_init();
        $ches = [];
        foreach($config as $conf){
            if(count($conf)==2){
                $ch = curl_init($conf[0]);
                curl_setopt($cp, CURLOPT_HEADER, true);
                curl_setopt($cp, CURLOPT_RETURNTRANSFER, true );
                curl_multi_add_handle($mch,$ch);
                $ches[$conf[1]]=$ch;
            }
        }
        $active = null;
// 执行批处理句柄
        do {
            $mrc = curl_multi_exec($mh, $active);
        } while ($mrc == CURLM_CALL_MULTI_PERFORM);

        while ($active && $mrc == CURLM_OK) {
            if (curl_multi_select($mh) != -1) {
                do {
                    $mrc = curl_multi_exec($mh, $active);
                } while ($mrc == CURLM_CALL_MULTI_PERFORM);
            }
        }

        /* This is the relevant bit */
        // iterate through the handles and get your content
        foreach ($ches as $filename=>$ch) {
            $html = curl_multi_getcontent($ch); // get the content

            // do what you want with the HTML
            curl_multi_remove_handle($mh, $ch); // remove the handle (assuming  you are done with it);
        }
        /* End of the relevant bit */

        curl_multi_close($mh); // close the curl multi handler
    }
    static function download($filename,$showname='',$content='',$expire=180){
        if(is_file($filename)){
            $length = filesize($filename);
        }elseif($content){
            $length = strlen($content);
        }else{
            return false;
        }
        if(empty($showname)) {
            $showname = $filename;
        }
        $showname = basename($showname);
        if(!empty($filename)) {
            $finfo 	= 	new \finfo(FILEINFO_MIME);
            $type 	= 	$finfo->file($filename);
        }else{
            $type	=	"application/octet-stream";
        }
        //发送Http Header信息 开始下载
        header("Pragma: public");
        header("Cache-control: max-age=".$expire);
        header('Cache-Control: no-store, no-cache, must-revalidate');
        header("Expires: " . gmdate("D, d M Y H:i:s",time()+$expire) . "GMT");
        header("Last-Modified: " . gmdate("D, d M Y H:i:s",time()) . "GMT");
        header("Content-Disposition: attachment; filename=".urlencode($showname));
        header("Content-Length: ".$length);
        header("Content-type: ".$type);
        header('Content-Encoding: UTF-8');
        header("Content-Transfer-Encoding: binary" );
        if($content == '' ) {
            readfile($filename);
        }else {
            echo($content);
        }
        return true;
    }

    /**
     * 异步HTTP方案
     * 需要被请求端开启ignore_user_abort
     * @param $Url 异步请求地址
     * @param string $Method 请求方式 GET，POST
     * @param array $Data POST内容
     * @return bool
     */
    function async($Url,$Method = 'GET',$Data=[]){
        $return = false;
        $conf=[];
        if($Method==self::$METHOD_POST&&$Data){
            $conf['post']=$Data;
        }
        if(!is_array($conf)) return $return;

        $matches = parse_url($Url);
        !isset($matches['host']) 	&& $matches['host'] 	= '';
        !isset($matches['path']) 	&& $matches['path'] 	= '';
        !isset($matches['query']) 	&& $matches['query'] 	= '';
        !isset($matches['port']) 	&& $matches['port'] 	= '';
        $host = $matches['host'];
        $path = $matches['path'] ? $matches['path'].($matches['query'] ? '?'.$matches['query'] : '') : '/';
        $port = !empty($matches['port']) ? $matches['port'] : 80;

        $conf_arr = array(
            'limit'		=>	0,
            'post'		=>	'',
            'cookie'	=>	'',
            'ip'		=>	'',
            'timeout'	=>	2,
            'block'		=>	false,
        );

        foreach (array_merge($conf_arr, $conf) as $k=>$v) ${$k} = $v;

        if($post) {
            if(is_array($post))
            {
                $post = http_build_query($post);
            }
            $out  = "POST $path HTTP/1.0\r\n";
            $out .= "Accept: */*\r\n";
            //$out .= "Referer: $boardurl\r\n";
            $out .= "Accept-Language: zh-cn\r\n";
            $out .= "Content-Type: application/x-www-form-urlencoded\r\n";
            $out .= "User-Agent: $_SERVER[HTTP_USER_AGENT]\r\n";
            $out .= "Host: $host\r\n";
            $out .= 'Content-Length: '.strlen($post)."\r\n";
            $out .= "Connection: Close\r\n";
            $out .= "Cache-Control: no-cache\r\n";
            $out .= "Cookie: $cookie\r\n\r\n";
            $out .= $post;
        } else {
            $out  = "GET $path HTTP/1.0\r\n";
            $out .= "Accept: */*\r\n";
            //$out .= "Referer: $boardurl\r\n";
            $out .= "Accept-Language: zh-cn\r\n";
            $out .= "User-Agent: $_SERVER[HTTP_USER_AGENT]\r\n";
            $out .= "Host: $host\r\n";
            $out .= "Connection: Close\r\n";
            $out .= "Cookie: $cookie\r\n\r\n";
        }
        $fp = @fsockopen(($ip ? $ip : $host), $port, $errno, $errstr, $timeout);
        if(!$fp) {
            return false;
        } else {
            stream_set_blocking($fp, $block);
            @fwrite($fp, $out);
            @fclose($fp);
            return true;
        }
    }
}