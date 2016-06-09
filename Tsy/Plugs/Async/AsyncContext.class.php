<?php
namespace Tsy\Plugs\Async;
/**
 * AsyncContext.class.php
 * @author fang
 * @date 2015-11-5
 */
class AsyncContext {
	protected $data = array ();
	public function set($k, $v) {
		$this->data [$k] = $v;
	}
	public function merge($data){
		if(is_array($data)){
			$this->data = array_merge($this->data, $data);
		}elseif($data instanceof AsyncContext){
			$this->data = array_merge($this->data, $data->data);
		}
	}
	public function get($k) {
		return $this->data [$k];
	}
	public function getAll() {
		return $this->data;
	}
}