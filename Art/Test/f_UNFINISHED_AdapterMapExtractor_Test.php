<?php
class AdapterMapExtractorTest extends PHPUnit_Framework_TestCase {

    public function setUp(){
        $c=\Art\Configuration::getInstance();
        $c->adapter->map->loader->xml->file='parts/map.xml';
    }
    
    public function test_create() {
        $instance = AdapterMapExtractorInstancer::getInstance();
        $instanceLoader=\Art\Adapter::factory(array('map'=>'loader'));
        $instance->setConfiguration(\Art\Configuration::getInstance()->map->defaults);
        $c=\Art\Configuration::getInstance();
        $instance->create($instanceLoader->getSchema($c->map->defaults));
    }
}