<?php
class Adapter_Test extends PHPUnit_Framework_TestCase {
    
    function setUp(){
        \Art\Autoload::registerNameSpace('Fakelib', 'Parts/Fakelib/');
        \Art\Configuration::getInstance()->adapter->classNameSpace = 'Fakelib\\Adapter';
        \Art\Configuration::getInstance()->adapter->i18n = 'ini';
        \Art\Configuration::getInstance()->adapter->database=array('connector'=>'mysql');
    }

    function test_factory() {
        $instance1 = \Art\Adapter::factory('i18n');
        $this->assertEquals($instance1 instanceof Fakelib\Adapter\I18n\Ini, true);
    }

    function test_factoryArray_() {
        $instance1 = \Art\Adapter::factory(array('database'=>'connector'));
        $this->assertEquals($instance1 instanceof Fakelib\Adapter\Database\Connector\Mysql, true);
    }

    function test_factoryIsNotSingleton_() {
        $instance1 = \Art\Adapter::factory(array('database' => 'connector'));
        $instance2 = \Art\Adapter::factory(array('database' => 'connector'));
        $this->assertEquals($instance1 === $instance2, false);
    }

    function test_configuration() {
        $c = \Art\Configuration::getInstance();
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
        $instance1 = \Art\Adapter::factory(array('database' => 'connector'));
        $this->assertEquals($instance1->getConfiguration()->toArray(), $configuration);
    }

    /**
     * @expectedException \Art\AdapterException
     */
    function test_factory_DoesNotExistException() {
        \Art\Adapter::factory('unexistant');
    }
}