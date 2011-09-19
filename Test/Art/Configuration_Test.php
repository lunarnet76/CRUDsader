<?php
class ArtConfigurationInstancer extends \Art\Configuration {
    public static function getDefaults() {
        return parent::$_defaults;
    }
}
class ConfigurationTest extends PHPUnit_Framework_TestCase {

    function test_instance() {
        $instance = \Art\Configuration::getInstance();
        $this->assertEquals($instance instanceof \Art\Configuration, true);
        $instance2 = \Art\Configuration::getInstance();
        $this->assertEquals($instance2 instanceof \Art\Configuration, true);
        $this->assertEquals($instance, $instance2);
    }

    function test_instance_defaults() {
        $instance = \Art\Configuration::getInstance();
        $this->assertEquals($instance->toArray(), ArtConfigurationInstancer::getDefaults());
    }

    /**
     * @depends test_instance
     */
    function test_loadFile_() {
        $instance = \Art\Configuration::getInstance();
        $instance->load('Parts/Fakefile/configuration.ini', false);
        $this->assertEquals($instance->a instanceof \Art\Block, true);
        $instance = \Art\Configuration::getInstance();
        $instance->load('Parts/Fakefile/configuration.ini', 'namespace2');
        $this->assertEquals($instance->a, 'l');
    }
    
    /**
     * @expectedException Art\ConfigurationException
     */
    function test_loadFile_ExceptionUnexistantFile(){
        $instance = \Art\Configuration::getInstance();
        $instance->load('Parts/Fakefile/doesnotexist.ini', false);
    }
    
    /**
     * @expectedException Art\ConfigurationException
     */
    function test_loadFile_ExceptionBadNamespace(){
        $instance = \Art\Configuration::getInstance();
        $instance->load('Parts/Fakefile/badNamespace.ini', false);
    }

    /**
     * @depends test_instance
     */
    function test_loadMultipleFile_() {
        $instance = \Art\Configuration::getInstance();
        $instance->load('Parts/Fakefile/configuration.ini', 'namespace2');
        $this->assertEquals($instance->a, 'l');
        $this->assertEquals(isset($instance->z), false);
        $instance->load('Parts/Fakefile/configuration2.ini', 'namespace2');
        $this->assertEquals($instance->a, '7');
        $this->assertEquals(isset($instance->z), true);
        $this->assertEquals(isset($instance->b), true);
    }
    
}