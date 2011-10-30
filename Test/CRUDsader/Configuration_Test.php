<?php
class CRUDsaderConfigurationInstancer extends \CRUDsader\Configuration {
    public static function getDefaults() {
        return parent::$_defaults;
    }
}
// this class get rid of the defaults
class CRUDsaderConfigurationInstancerNoDefaults extends \CRUDsader\Configuration {
    public static function getInstance() {
        return new self();
    }
}
use CRUDsader as c;
class Configuration_Test extends PHPUnit_Framework_TestCase {

    function test_instance() {
        $instance = c\Instancer::getInstance()->configuration;
        $this->assertEquals($instance instanceof \CRUDsader\Configuration, true);
        $instance2 = c\Instancer::getInstance()->configuration;
        $this->assertEquals($instance2 instanceof \CRUDsader\Configuration, true);
        $this->assertEquals($instance, $instance2);
    }
    
    
    function test_instance_defaults() {
        $instance = c\Instancer::getInstance()->configuration;
        $this->assertEquals($instance->toArray(), CRUDsaderConfigurationInstancer::getDefaults());
    }
    
    function test_load_(){
        $instance = \CRUDsader\Configuration::getInstance();
        $instance->load(array('file'=>'Parts/Fakefile/configuration.ini','section'=>false));
    }
}