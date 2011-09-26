<?php

class Expression_Test extends PHPUnit_Framework_TestCase {
    function test_toString_() {
        $e=new \Art\Expression('test');
        $this->assertEquals($e->__toString(),'test');
    }
    
    function test_isEmpty() {
        $this->assertEquals(\Art\Expression::isEmpty(''),true);
        $this->assertEquals(\Art\Expression::isEmpty(false),true);
        $this->assertEquals(\Art\Expression::isEmpty(array()),true);
        $this->assertEquals(\Art\Expression::isEmpty(null),true);
        $this->assertEquals(\Art\Expression::isEmpty('NULL'),true);
    }
}