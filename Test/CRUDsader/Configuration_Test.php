<?php
class CRUDsaderConfigurationInstancer extends \CRUDsader\Configuration {
    public static function getDefaults() {
        return parent::$_defaults;
    }
}
// this class get rid of the singleton
class CRUDsaderConfigurationInstancerNoDefaults extends \CRUDsader\Configuration {
    public static function getInstance() {
        return new self();
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
    
    function test_load_(){
        $instance = \CRUDsader\Configuration::getInstance();
        $instance->load('yaml',array('file'=>'Parts/Fakefile/configuration.ini','section'=>false));
    }
}