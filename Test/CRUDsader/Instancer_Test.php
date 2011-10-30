<?php
class B{
    
}
class InstancerInjector_Test extends PHPUnit_Framework_TestCase {
    
    function setUp(){
        \CRUDsader\Configuration::getInstance()->instances=array(
            'debug'=>array('\\CRUDsader\\Debug',true),
            'debug2'=>array('\\CRUDsader\\Object\\Attribute'),
        );
    }
    
    function tearDown(){
       unset(\CRUDsader\Instancer::getInstance()->adapter);
    }

    function test_() {
        $di=\CRUDsader\Instancer::getInstance();
        $this->assertEquals($di->debug2() instanceof CRUDsader\Object\Attribute,true);
        $this->assertEquals($di->debug instanceof CRUDsader\Debug,true);
        $o=array($di->debug,$di->debug,$di->debug2(),$di->debug2());
        $this->assertEquals($o[0]===$o[1],true);
        $this->assertEquals($o[2]!==$o[3],true);
        
    }
}