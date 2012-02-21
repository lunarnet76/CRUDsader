<?php

class Expression_Test extends PHPUnit_Framework_TestCase {
    function test_toString_() {
        $e=new \CRUDsader\Expression('test');
        $this->assertEquals($e->__toString(),'test');
    }
    
    function test_isEmpty() {
        $this->assertEquals(\CRUDsader\Expression::isEmpty(''),true);
        $this->assertEquals(\CRUDsader\Expression::isEmpty(false),true);
        $this->assertEquals(\CRUDsader\Expression::isEmpty(array()),true);
        $this->assertEquals(\CRUDsader\Expression::isEmpty(null),true);
        $this->assertEquals(\CRUDsader\Expression::isEmpty(new \CRUDsader\Expression\Nil),true);
    }
}