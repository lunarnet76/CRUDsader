<?php
class ArtSingletonInstancer extends Art\Singleton {
    
}

class SingletonTest extends PHPUnit_Framework_TestCase {

    function test_getInstance_() {
        $instance = ArtSingletonInstancer::getInstance();
        $this->assertEquals($instance instanceof ArtSingletonInstancer, true);
        $this->assertEquals($instance, ArtSingletonInstancer::getInstance());
    }
}