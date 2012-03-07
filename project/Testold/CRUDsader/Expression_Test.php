<?php

class Expression_Test extends PHPUnit_Framework_TestCase {
    function test_toString_() {
        $e=new \CRUDsader\Expression('test');
        $this->assertEquals($e->__toString(),'test');
    }
}