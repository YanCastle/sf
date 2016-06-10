<?php
namespace Tsy\Plugs\Async;
/**
 * Future.class.php
 * @author fang
 * @date 2015-11-5
 */
class Future implements FutureIntf {
	protected $callback;
	public function __construct($callback) {
		$this->callback = $callback;
	}
	public function run(Async &$promise,$content) {
		$cb = $this->callback;
		return $cb ( $promise ,$content);
	}
}