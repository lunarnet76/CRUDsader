<?php
class Adapter_Test extends PHPUnit_Framework_TestCase {
    
    function setUp(){
        \CRUDsader\Autoload::registerNameSpace('Fakelib', 'Parts/Fakelib/');
        \CRUDsader\Configuration::getInstance()->adapter->classNameSpace = 'Fakelib\\Adapter';
        \CRUDsader\Configuration::getInstance()->adapter->database=array('connector'=>'mysql');
        \CRUDsader\Configuration::getInstance()->adapter->ns='Aclass';
    }
    
    function tearDown(){
        \CRUDsader\Autoload::unregisterNameSpace('Fakelib');
        \CRUDsader\Configuration::getInstance()->adapter->classNameSpace = 'CRUDsader\\Adapter';
        \CRUDsader\Configuration::getInstance()->adapter->database=array('connector'=>'mysqli');
        unset(\CRUDsader\Configuration::getInstance()->adapter->ns);
    }

    function test_factory() {
        $instance1 = \CRUDsader\Adapter::factory('ns');
        $this->assertEquals($instance1 instanceof Fakelib\Adapter\Ns\Aclass, true);
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