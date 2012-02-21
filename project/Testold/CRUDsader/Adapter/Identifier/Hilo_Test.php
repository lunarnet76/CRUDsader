<?php
class AdapterIdentifierHiloInstancer extends  \CRUDsader\Adapter\Identifier\Hilo{
    public static function getInstance(){
        return new parent();
    }
    
}
class AdapterIdentifierHilo_Test extends PHPUnit_Framework_TestCase {
    
    function test_getOID(){
        $instance= AdapterIdentifierHiloInstancer::getInstance();
        $this->assertEquals($instance->getOID(array('class'=>'person')),'1'.date('ysdmhi'));
        $this->assertEquals($instance->getOID(array('class'=>'person')),'2'.date('ysdmhi'));
    }
}