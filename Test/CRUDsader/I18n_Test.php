<?php
class I18n_Test extends PHPUnit_Framework_TestCase {
    
    function test_constructor_() {
        $instance = \CRUDsader\Instancer::getInstance()->i18n;
        $this->assertEquals($instance->hasAdapter('translation'), true);
    }
    
    /**
     * @expectedException \CRUDsader\I18nException
     */
    function test_call_ExceptionFunctionDoesNotExist(){
        $instance = \CRUDsader\Instancer::getInstance()->i18n;
        $instance->unexistant();
    }
    
    function test_call_(){
        $instance = \CRUDsader\Instancer::getInstance()->i18n;
        $this->assertEquals($instance->translate('test'),'test');
    }
}