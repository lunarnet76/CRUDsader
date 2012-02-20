<?php

class Exception_Test extends PHPUnit_Framework_TestCase {
    /**
     * @expectedException \CRUDsader\Exception
     */
    function test_() {
        throw new \CRUDsader\Exception();
    }
}