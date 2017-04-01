<?php
class a{
    private $a=1;//私有属性，只有a这个类可以变更和读取
    protected $b=1;//受保护的属性，
    public $c=1;
    private static $d=1;
    protected static $e=1;
    public static  $f=1;
    const H=1;
    private function i(){}
    protected function j(){}
    public function k(){
        echo 'k';
    }
    private static function l(){}
    protected static function m(){}
    public static function n(){}
}
class b extends a{
    function ba(){
        echo get_class($this);
        echo __CLASS__;
        self::ca();
        $this->ca();
    }
}
class c extends b{
    function ca(){
        echo get_class($this);
        echo __CLASS__;
    }
}
//a::n();//静态调用
//$a = new a();
//$a->c=1;
//$c = new c();
//echo "\r\n";
//$c->ba();
//echo "\r\n";
//$c->ca();
echo c::$f,b::$f,a::$f;
c::$f=2;
echo c::$f,b::$f,a::$f;

