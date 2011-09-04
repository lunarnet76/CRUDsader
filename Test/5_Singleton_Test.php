<?php

class Singletoned extends Art\Singleton{}

class ASingleton extends PHPUnit_Framework_TestCase {

    function test_getInstance_(){
        $instance=Singletoned::getInstance();
        $this->assertEquals($instance instanceof Singletoned,true);
    }
    
     /**
     * @expectedException FATAL_ERROR
    function test_noClone_(){
        $instance=Singletoned::getInstance();
        $instance2=clone $instance;
    }
      * 
      */
    
    /**
     * @expectedException FATAL_ERROR
     
    function test_constructor_ErrorNoConstructorAllowed(){
        new Singletoned();
    }
     *
     */

}