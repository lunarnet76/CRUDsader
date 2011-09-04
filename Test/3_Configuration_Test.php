<?php
class ArtConfigurationInstancer extends \Art\Configuration {

    public static function getDefaults() {
        return parent::$_defaults;
    }
}
class AConfiguration extends PHPUnit_Framework_TestCase {

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
    function test_loadFile_ExceptionNamespace() {
        $instance = \Art\Configuration::getInstance();
        $instance->load('parts/3/configuration.ini', false);
        $this->assertEquals($instance->a instanceof \Art\Block, true);
        $instance = \Art\Configuration::getInstance();
        $instance->load('parts/3/configuration.ini', 'namespace2');
        $this->assertEquals($instance->a, 'l');
    }

    /**
     * @depends test_instance
     */
    function test_loadMultipleFile_() {
        $instance = \Art\Configuration::getInstance();
        $instance->load('parts/3/configuration.ini', 'namespace2');
        $this->assertEquals($instance->a, 'l');
        $this->assertEquals(isset($instance->z), false);
        $instance->load('parts/3/configuration2.ini', 'namespace2');
        $this->assertEquals($instance->a, '7');
        $this->assertEquals(isset($instance->z), true);
        $this->assertEquals(isset($instance->b), true);
    }
}