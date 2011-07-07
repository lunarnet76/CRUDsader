<?php
\Art\Configuration::getInstance()->map->file=dirname(__FILE__).'/parts/map.xml';
class MapTest extends PHPUnit_Framework_TestCase {

    function test_validateSchema_(){
        $map=\Art\Map::getInstance();
        $this->assertEquals($map->validateSchema(),true);
    }
    
    
    function test_class_(){
        $map=\Art\Map::getInstance();
        $this->assertEquals($map->classExists('employee'),true);
        $this->assertEquals($map->classExists('contact'),true);
        $this->assertEquals($map->classExists('alienVsPredator'),false);
        
        $this->assertEquals($map->classGetDatabaseTable('employee'),'employee');
        $this->assertEquals($map->classGetDatabaseTable('contact'),'Tcontact');
        $this->assertEquals($map->classHasAssociation('employee','hasAddress'),true);
        $this->assertEquals($map->classHasAssociation('employee','alienVsPredator'),false);
    }

}