<?php
class Test extends CRUDsader\Adapter{}
class Adapter_Test extends PHPUnit_Framework_TestCase {
    public function test_(){
        $di=\CRUDsader\Instancer::getInstance();
        $t=$di->database;
       
    }
    
}