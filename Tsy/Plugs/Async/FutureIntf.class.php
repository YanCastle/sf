<?php
/**
 * Created by PhpStorm.
 * User: castle
 * Date: 6/9/16
 * Time: 10:45 PM
 */

namespace Tsy\Plugs\Async;

/**
 * FutureIntf.class.php
 * @author fang
 * @date 2015-11-5
 */
interface FutureIntf {
    public function run(Async &$async);
}