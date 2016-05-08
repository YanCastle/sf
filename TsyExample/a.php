<?php
/**
 * Created by PhpStorm.
 * User: Castle
 * Date: 2016/4/12
 * Time: 23:31
 */
//echo 'a';
class obj implements Serializable {
    private $data;
    public function __construct() {
        $this->data = "My private data";
    }
    public function serialize() {
        return serialize($this->data);
    }
    public function unserialize($data) {
        $this->data = unserialize($data);
    }
    public function s() {
        return $this->data;
    }
}

$obj = new obj;
$ser = serialize($obj);

$newobj = unserialize($ser);

var_dump($newobj->s());