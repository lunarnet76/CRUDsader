<?php
class AExpression extends PHPUnit_Framework_TestCase {

    function test_() {
        $text = 'asgewg wegew gwe gwe gwe';
        $instance = new \Art\Expression($text);
        $this->assertEquals($instance->__toString(), $text);
    }

    function test_Null_() {
        $instance = new \Art\Expression\Void();
        $this->assertEquals($instance->__toString(), 'NULL');
    }

    function test_Now_() {
        $instance = new \Art\Expression\Now();
        $this->assertEquals($instance->__toString(), date('Y-m-d'));
    }
}
?>
