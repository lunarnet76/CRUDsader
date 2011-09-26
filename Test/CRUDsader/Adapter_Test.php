<?php
class Adapter_Test extends PHPUnit_Framework_TestCase {
    
    function setUp(){
        \CRUDsader\Autoload::registerNameSpace('Fakelib', 'Parts/Fakelib/');
        \CRUDsader\Configuration::getInstance()->adapter->classNameSpace = 'Fakelib\\Adapter';
        \CRUDsader\Configuration::getInstance()->adapter->i18n = 'ini';
        \CRUDsader\Configuration::getInstance()->adapter->database=array('connector'=>'mysql');
    }

    function test_factory() {
        $instance1 = \CRUDsader\Adapter::factory('i18n');
        $this->assertEquals($instance1 instanceof Fakelib\Adapter\I18n\Ini, true);
    }

    function test_factoryArray_() {
        $instance1 = \CRUDsader\Adapter::factory(array('database'=>'connector'));
        $this->assertEquals($instance1 instanceof Fakelib\Adapter\Database\Connector\Mysql, true);
    }

    function test_factoryIsNotSingleton_() {
        $instance1 = \CRUDsader\Adapter::factory(array('database' => 'connector'));
        $instance2 = \CRUDsader\Adapter::factory(array('database' => 'connector'));
        $this->assertEquals($instance1 === $instance2, false);
    }

    function test_configuration() {
        $c = \CRUDsader\Configuration::getInstance();
        $configuration = array(
            'option1' => 'value1'
        );
        $c->loadArray(array(
            'adapter' => array(
                'database' => array(
                    'connector' => array(
                        'mysql' => $configuration
                    )
                )
            )
        ));
        $instance1 = \CRUDsader\Adapter::factory(array('database' => 'connector'));
        $this->assertEquals($instance1->getConfiguration()->toArray(), $configuration);
    }

    /**
     * @expectedException \CRUDsader\AdapterException
     */
    function test_factory_DoesNotExistException() {
        \CRUDsader\Adapter::factory('unexistant');
    }
}