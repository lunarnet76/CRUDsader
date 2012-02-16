<?php
class AdapterI18nTranslationNoneInstancer extends \CRUDsader\Adapter\I18n\Translation\None{
    public static function getInstance(){
        return new parent();
    }
}
class AdapterI18nTranslationNone_Test extends PHPUnit_Framework_TestCase {
    
    function test_(){
        $instance=AdapterI18nTranslationNoneInstancer::getInstance(); 
        $this->assertEquals($instance->translate('test'),'test');
    }
}