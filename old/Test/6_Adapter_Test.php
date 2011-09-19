<?php
require('parts/6/adapter.php');
class AdapterTest extends PHPUnit_Framework_TestCase {

    function test_factory() {
        \Art\Configuration::getInstance()->adapter->test = 'TestAdapter';
        $instance1 = \Art\Adapter::factory('test');
        $this->assertEquals($instance1 instanceof \Art\Adapter\Test\TestAdapter, true);
    }

    function test_factoryArray_() {
        $instance1 = \Art\Adapter::factory(array('database' => 'connector'));
        $this->assertEquals($instance1 instanceof \Art\Adapter\Database\Connector\Mysqli, true);
    }

    function test_factoryIsNotSingleton_() {
        $instance1 = \Art\Adapter::factory(array('database' => 'connector'));
        $instance2 = \Art\Adapter::factory(array('database' => 'connector'));
        $this->assertEquals($instance1 === $instance2, false);
    }

    function test_configuration() {
        $c = \Art\Configuration::getInstance();
        $configuration=array(
          'option1'=>'value1'  
        );
        $c->loadArray(array(
            'adapter' => array(
                'database' => array(
                    'connector' => array(
                        'mysqli' =>$configuration
                    )
                )
            )
        ));
        $instance1 = \Art\Adapter::factory(array('database' => 'connector'));
        $this->assertEquals($instance1->getConfiguration()->toArray(),$configuration);
    }

    /**
     * @expectedException \Art\AdapterException
     */
    function test_factory_DoesNotExistException() {
        \Art\Adapter::factory('unexistant');
    }
}