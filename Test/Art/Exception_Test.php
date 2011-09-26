<?php

class Exception_Test extends PHPUnit_Framework_TestCase {
    /**
     * @expectedException \Art\Exception
     */
    function test_() {
        throw new \Art\Exception();
    }
}