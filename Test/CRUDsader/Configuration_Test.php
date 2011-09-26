<?php
class CRUDsaderConfigurationInstancer extends \CRUDsader\Configuration {
    public static function getDefaults() {
        return parent::$_defaults;
    }
}
class Configuration_Test extends PHPUnit_Framework_TestCase {

    function test_instance() {
        $instance = \CRUDsader\Configuration::getInstance();
        $this->assertEquals($instance instanceof \CRUDsader\Configuration, true);
        $instance2 = \CRUDsader\Configuration::getInstance();
        $this->assertEquals($instance2 instanceof \CRUDsader\Configuration, true);
        $this->assertEquals($instance, $instance2);
    }

    function test_instance_defaults() {
        $instance = \CRUDsader\Configuration::getInstance();
        $this->assertEquals($instance->toArray(), CRUDsaderConfigurationInstancer::getDefaults());
    }

    /**
     * @depends test_instance
     */
    function test_loadFile_() {
        $instance = \CRUDsader\Configuration::getInstance();
        $instance->load('Parts/Fakefile/configuration.ini', false);
        $this->assertEquals($instance->a instanceof \CRUDsader\Block, true);
        $instance = \CRUDsader\Configuration::getInstance();
        $instance->load('Parts/Fakefile/configuration.ini', 'namespace2');
        $this->assertEquals($instance->a, 'l');
    }
    
    /**
     * @expectedException CRUDsader\ConfigurationException
     */
    function test_loadFile_ExceptionUnexistantFile(){
        $instance = \CRUDsader\Configuration::getInstance();
        $instance->load('Parts/Fakefile/doesnotexist.ini', false);
    }
    
    /**
     * @expectedException CRUDsader\ConfigurationException
     */
    function test_loadFile_ExceptionBadNamespace(){
        $instance = \CRUDsader\Configuration::getInstance();
        $instance->load('Parts/Fakefile/badNamespace.ini', false);
    }

    /**
     * @depends test_instance
     */
    function test_loadMultipleFile_() {
        $instance = \CRUDsader\Configuration::getInstance();
        $instance->load('Parts/Fakefile/configuration.ini', 'namespace2');
        $this->assertEquals($instance->a, 'l');
        $this->assertEquals(isset($instance->z), false);
        $instance->load('Parts/Fakefile/configuration2.ini', 'namespace2');
        $this->assertEquals($instance->a, '7');
        $this->assertEquals(isset($instance->z), true);
        $this->assertEquals(isset($instance->b), true);
    }
    
}