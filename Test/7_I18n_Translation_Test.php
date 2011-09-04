<?php
class AI18n extends PHPUnit_Framework_TestCase {

    function test_() {
        $instance = \Art\I18n::getInstance();
        $this->assertEquals($instance->hasAdapter('translation'),true);
        $this->assertEquals($instance->getAdapter('translation') instanceof \Art\Adapter,true);
    }

    function test_adapterDefault(){
        $instance = \Art\I18n::getInstance();
        $this->assertEquals($instance->getAdapter('translation') instanceof \Art\Adapter\I18n\Translation\Transparent,true);
        $this->assertEquals($instance->translate('atext'),'atext');
    }
}