<?php
/**
 * Copyright (c) 2016. Lorem ipsum dolor sit amet, consectetur adipiscing elit. 
 * Morbi non lorem porttitor neque feugiat blandit. Ut vitae ipsum eget quam lacinia accumsan. 
 * Etiam sed turpis ac ipsum condimentum fringilla. Maecenas magna. 
 * Proin dapibus sapien vel ante. Aliquam erat volutpat. Pellentesque sagittis ligula eget metus. 
 * Vestibulum commodo. Ut rhoncus gravida arcu. 
 */

namespace Tsy\Plugs\Async;
/**
 * HttpClientFuture.class.php
 * 暂时不支持HTTPS，受限于swoole_http_client
 * @author Castle
 * @date 2015-11-5
 */
class HttpClientFuture implements FutureIntf {
	protected $url = null;
	protected $post = null;
	protected $timer = null;
	protected $proxy = false;
	protected $timeout = 0.5;
	
	public function __construct($url, $post = array(), $header = [],$cookie=[], $timeout = 5) {
		$this->url = $url;
		$this->post = $post;
		if($proxy){
			$this->proxy = $proxy;
		}
		$this->timeout = $timeout;
	}
	

	
	public function run(Async &$promise,$content) {
		$urlInfo = parse_url ( $this->url );
		$timeout = $this->timeout;
		$https=false;
		if('http'==$urlInfo['scheme']&&!isset($urlInfo ['port']))$urlInfo ['port'] = 80;
		if('https'==$urlInfo['scheme']&&!isset($urlInfo ['port'])){$urlInfo ['port'] = 443;$https=true;}

        $cli = new \swoole_http_client($urlInfo['host'], $urlInfo ['port'],$https);
		$cli->set(array(
            'timeout' => $timeout,
            'keepalive' => 0,
        ));
        $cli->on ( "error", function($cli)use(&$promise){
            Timer::del($cli->sock);
			$promise->accept(['http_data'=>null, 'http_error'=>'Connect error']);
        } );
        $cli->on ( "close", function ($cli)use(&$promise) {
		} );
        $cli->execute( $this->url, function ($cli)use(&$promise) {
            Timer::del($cli->sock);
            $cli->isDone = true;
            $promise->accept(['http_data'=>$cli->body,'http_header'=>$cli->headers,'http_cookie'=>method_exists($cli, 'cookies')?$cli->cookies:[]]);
        });

		$cli->isConnected = false;

		if(!$cli->errCode){
			Timer::add($cli->sock, $this->timeout, function()use($cli, &$promise){
				@$cli->close();
				if($cli->isConnected){
					$promise->accept(['http_data'=>null, 'http_error'=>'Http client read timeout']);
				}else{
					$promise->accept(['http_data'=>null, 'http_error'=>'Http client connect timeout']);
				}
			});
		}
	}
}
