<?php
use CRUDsader as c;
class Database_Test extends PHPUnit_Framework_TestCase {
    public $di;
    
    function setUp(){
        Bootstrap::setUpORMDatabase();
        $this->di=c\Instancer::getInstance();
        $instance = $this->di->database;
        $block=new \CRUDsader\Block(array(
            'host'=>DATABASE_HOST,
            'user'=>DATABASE_USER,
            'password'=>DATABASE_PASSWORD,
            'name'=>DATABASE_NAME,
        ));
        $instance->setConfiguration($block);
    }
    
    function tearDown(){
        Bootstrap::tearDownDatabase();
    }

    function test_constructor_() {
        $instance = $this->di->database;
        $this->assertEquals($instance->hasAdapter('connector'), true);
        $this->assertEquals($instance->hasAdapter('descriptor'), true);
        $this->assertEquals($instance->getAdapter('connector') instanceof \CRUDsader\Adapter\Database\Connector, true);
        $this->assertEquals($instance->getAdapter('descriptor') instanceof \CRUDsader\Adapter\Database\Descriptor, true);
    }
    
    function test_query_(){
        $instance = $this->di->database;
        $q=$instance->query('SELECT * FROM Tperson','select');
        $this->assertEquals($q instanceof \CRUDsader\Adapter\Database\Rows,true);
    }
    
    function test_queryStatment_(){
        $instance = $this->di->database;
        $instance->prepareQueryStatement('SELECT * FROM Tperson WHERE PKpersonid>?','select');
        $q=$instance->executeQueryStatement(array(0));
        $this->assertEquals(is_array($q),true);
        $this->assertEquals(count($q),3);
    }
    
    /**
     * @expectedException \CRUDsader\DatabaseException
     */
    function test_call_ExceptionDoesNotExist(){
        $instance = $this->di->database;
        $instance->unexistant();
    }
    
    function test_callDescriptor_(){
        $instance = $this->di->database;
        $this->assertEquals($instance->quote('test'),'"test"');
    }
    
    function test_callConnector_(){
        $instance = $this->di->database;
        $this->assertEquals($instance->isConnected(),true);
    }
}