<?php
class CRUDsaderSingletonInstancer extends CRUDsader\Singleton {
    
}

class Singleton_Test extends PHPUnit_Framework_TestCase {

    function test_getInstance_() {
        $instance = CRUDsaderSingletonInstancer::getInstance();
        $this->assertEquals($instance instanceof CRUDsaderSingletonInstancer, true);
        $this->assertEquals($instance, CRUDsaderSingletonInstancer::getInstance());
    }
}
