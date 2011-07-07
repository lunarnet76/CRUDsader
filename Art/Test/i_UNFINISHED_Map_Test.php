<?php
\Art\Configuration::getInstance()->adapter->map->loader->xml->file=dirname(__FILE__).'/parts/map.xml';
class MapTest extends PHPUnit_Framework_TestCase {

    function test_validateSchema_(){
        $map=\Art\Map::getInstance();
        $this->assertEquals($map->validate(),true);
    }
    
    
    function test_class_(){
        $map=\Art\Map::getInstance();
        print_r($map);
        $this->assertEquals($map->classExists('employee'),true);
        $this->assertEquals($map->classExists('contact'),true);
        $this->assertEquals($map->classExists('alienVsPredator'),false);
        
        $this->assertEquals($map->classGetDatabaseTable('employee'),'employee');
        $this->assertEquals($map->classGetDatabaseTable('contact'),'Tcontact');
        $this->assertEquals($map->classHasAssociation('employee','hasAddress'),true);
        $this->assertEquals($map->classHasAssociation('employee','alienVsPredator'),false);
    }
}