<?php
/**
 * Created by PhpStorm.
 * User: castle
 * Date: 6/6/16
 * Time: 11:01 AM
 */

namespace Tsy\Plugs\HttpClient;


class HttpClient
{
    public $url;
    public $cookie;
    public $response_header;
    public $request_header;
    /**
     * @var \swoole_client $client
     */
    private $client;
    private $UserID;
    private $clients=[];
    function __construct($url='',$UserID='',$async=false)
    {
        if(!extension_loaded('swoole')){
            L('未找到Swoole扩展');
        }
        $this->url=$url;
        $this->UserID = $UserID?uniqid('user_'):$UserID;
    }
    function post(callable $func,$data,$url='',$header=[],$cookie=[]){}
    function get(callable $func,$data=[],$url='',$header=[],$cookie=[]){
        $this->getClient($url?$url:$this->url);
        $this->client->send($this->http_build($url?$url:$this->url,$data,[],$header,$cookie));
    }
    function put(callable $func,$data,$url='',$header=[],$cookie=[]){}
    function delete(callable $func,$data,$url='',$header=[],$cookie=[]){}
    function options(callable $func,$data,$url='',$header=[],$cookie=[]){}
    function cookie($name,$value){
        if($name&&$value)
            $this->cookie[$name]=$value;
        elseif($name===null)
            $this->cookie=[];
        elseif($name===false){
            return $this->cookie;
        }
    }
    function header($name,$value=''){
        if($name&&$value)
            $this->request_header[$name]=$value;
        elseif($name===null)
            $this->request_header=[];
        elseif($name===false){
            return $this->request_header;
        }
    }
    private function getClient($url){
        $parse = parse_url($url);
        if(!isset($parse['host'])||isset($parse['user'])||isset($parse['pass'])){
            return false;
        }
        if(!isset($parse['scheme'])){
            $parse['scheme']='http';
        }
        switch ($parse['scheme']){
            case 'http':
                $parse['port']=isset($parse['port'])?$parse['port']:80;
                $this->client = new \swoole_client(SWOOLE_SOCK_TCP,SWOOLE_SOCK_ASYNC);
                break;
            case 'https':
                $parse['port']=isset($parse['port'])?$parse['port']:443;
                $this->client = new \swoole_client(SWOOLE_SOCK_TCP|SWOOLE_SSL,SWOOLE_SOCK_ASYNC);
                break;
            default:
                return false;
                break;
        }
        $this->client->on('receive',[$this,'receive']);
        $this->client->on('close',[$this,'close']);
        $this->client->on('error',[$this,'error']);
        $this->client->on('connect',[$this,'connect']);
        $this->client->connect($parse['host'],$parse['port']);
        return true;
    }
    function close(\swoole_client $client){}
    function connect(\swoole_client $client){}
    function receive(\swoole_client $client,$data){
        //解析http代码
        $c = $data;
    }
    function error(\swoole_client $client){}
    function http_build($URL,$GET=[],$POST=[],$Header=[],$Cookie=[],$Method='GET'){
        $parse = parse_url($URL);
        parse_str($parse['query'],$UrlGet);
        $RequestUrl = http_build_query(array_merge($UrlGet,$GET));
        $RequestUrl = $RequestUrl?$parse['path'].'?'.$RequestUrl:$parse['path'];
        $header = [
            strtoupper($Method)." {$RequestUrl} HTTP/1.0",
        ];
        $str = '';
        $header_array = [
            "HOST"=>$parse['host'],
            "User-Agent"=>"Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/34.0.1847.116 Safari/537.36"
        ];
        $header_array = array_merge($header_array,$this->request_header,$Header);
        switch (strtoupper($Method)){
            case 'GET':break;
            case 'POST':break;
            case 'DELETE':break;
            case 'OPTIONS':break;
            case 'PUT':break;
        }
        foreach ($header_array as $k=>$v){
            $header[]=$k.': '.$v;
        }
        $str = implode("\r\n",$header)."\r\n";
        return $str;
    }
    function __set($name, $value)
    {
        if(in_array($name,['referer'])){
            
        }elseif(in_array($name, [])){

        }else{

        }
    }
}