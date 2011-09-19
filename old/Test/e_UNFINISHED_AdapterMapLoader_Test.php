<?php
class AdapterMapLoaderTest extends PHPUnit_Framework_TestCase {
    public function setUp(){
        $c=\Art\Configuration::getInstance();
        $c->adapter->map->loader->xml->file='parts/map.xml';
    }
    
    function test_validate(){
        $instance=\Art\Adapter::factory(array('map'=>'loader'));
        $c=\Art\Configuration::getInstance();
        $this->assertEquals($instance->validate(),true);
        $instance->getSchema($c->map->defaults);
    }
    
    public function tearDown(){
        $c=\Art\Configuration::getInstance();
        $c->adapter->map->loader->xml->file='map.xml';
    }
 
}