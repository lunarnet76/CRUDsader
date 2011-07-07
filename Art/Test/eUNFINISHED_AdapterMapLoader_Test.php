<?php
class AdapterMapLoaderTest extends PHPUnit_Framework_TestCase {
    function test_(){
        $instance=\Art\Adapter::factory(array('map'=>'loader'));
        $c=\Art\Configuration::getInstance();
        $c->map->loader->file='parts/map.xml';
        $instance->setConfiguration($c->map->loader);
        ($instance->validate());
        ($instance->getSchema($c->map->defaults));
    }
 
}