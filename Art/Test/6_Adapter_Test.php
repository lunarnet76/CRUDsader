<?php
class AAdapter extends PHPUnit_Framework_TestCase {
    function test_get(){
        $instance1=\Art\Adapter::factory(array('database'=>'connector'));
        $this->assertEquals($instance1 instanceof \Art\Adapter\Database\Connector\Mysqli,true);
        $instance1=\Art\Adapter::factory(array('database'=>'connector'));
        $instance2=\Art\Adapter::factory(array('database'=>'connector'));
        $this->assertEquals($instance1===$instance2,false);
    }

    function test_configuration(){
        $c=\Art\Configuration::getInstance();
        $c->loadArray(array(
            'adapter'=>array(
                'database'=>array(
                    'connector'=>array(
                        'mysqli'=>array(
                            'option1'=>'value1'
                        )
                    )
                )
            )
        ));
         $instance1=\Art\Adapter::factory(array('database'=>'connector'));
    }
}