<?php
class CRUDsaderConfigurationInstancer extends \CRUDsader\Configuration {

    public static function getDefaults() {
        return parent::$_defaults;
    }
}
class CRUDsaderConfigurationInstancer2 extends \CRUDsader\Configuration {
    public static $_instance = null;

    protected function __construct() {
        // parent::__construct(self::$_defaults);
    }

    public static function getInstance() {
        if (!isset(self::$_instance))
            self::$_instance = new self();
        return self::$_instance;
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
        $instance = CRUDsaderConfigurationInstancer2::getInstance();
        $instance->load('Parts/Fakefile/configuration.ini', false);
        pre($instance->toArray());
        exit;
        $this->assertEquals($instance->a instanceof \CRUDsader\Block, true);
        $instance = \CRUDsader\Configuration::getInstance();
        $instance->load('Parts/Fakefile/configuration.ini', 'namespace2');
        $this->assertEquals($instance->a, 'l');
        $instance->load('Parts/Fakefile/configuration.ini', 'namespace3');
        $this->assertEquals($instance->m->n->o->p->q, '6 aand cowejf owejfo wejog wejog jwe END');
    }

    /**
     * @expectedException CRUDsader\ConfigurationException
     */
    function test_loadFile_ExceptionUnexistantFile() {
        $instance = \CRUDsader\Configuration::getInstance();
        $instance->load('Parts/Fakefile/doesnotexist.ini', false);
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