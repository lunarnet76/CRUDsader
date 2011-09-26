<?php
class I18n_Test extends PHPUnit_Framework_TestCase {
    
    function test_constructor_() {
        $instance = \Art\I18n::getInstance();
        $this->assertEquals($instance->hasAdapter('translation'), true);
    }
    
    /**
     * @expectedException \Art\I18nException
     */
    function test_call_ExceptionFunctionDoesNotExist(){
        $instance = \Art\I18n::getInstance();
        $instance->unexistant();
    }
    
    function test_call_(){
        $instance = \Art\I18n::getInstance();
        $this->assertEquals($instance->translate('test'),'test');
    }
}