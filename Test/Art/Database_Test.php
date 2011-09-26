<?php
class Database_Test extends PHPUnit_Framework_TestCase {
    
    function setUp(){
        Bootstrap::setUpORMDatabase();
        \Art\Configuration::getInstance()->database->host=DATABASE_HOST;
        \Art\Configuration::getInstance()->database->user=DATABASE_USER;
        \Art\Configuration::getInstance()->database->password=DATABASE_PASSWORD;
        \Art\Configuration::getInstance()->database->name=DATABASE_NAME;
    }
    
    function tearDown(){
        Bootstrap::tearDownDatabase();
    }

    function test_constructor_() {
        $instance = \Art\Database::getInstance();
        $this->assertEquals($instance->hasAdapter('connector'), true);
        $this->assertEquals($instance->hasAdapter('descriptor'), true);
        $this->assertEquals($instance->getAdapter('connector') instanceof \Art\Adapter\Database\Connector, true);
        $this->assertEquals($instance->getAdapter('descriptor') instanceof \Art\Adapter\Database\Descriptor, true);
    }
    
    function test_query_(){
        $instance = \Art\Database::getInstance();
        $q=$instance->query('SELECT * FROM Tperson','select');
        $this->assertEquals($q instanceof \Art\Adapter\Database\Rows,true);
    }
    
    function test_queryStatment_(){
        $instance = \Art\Database::getInstance();
        $instance->prepareQueryStatement('SELECT * FROM Tperson WHERE PKpersonid>?','select');
        $q=$instance->executeQueryStatement(array(0));
        $this->assertEquals(is_array($q),true);
        $this->assertEquals(count($q),3);
    }
    
    /**
     * @expectedException \Art\DatabaseException
     */
    function test_call_ExceptionDoesNotExist(){
        $instance = \Art\Database::getInstance();
        $instance->unexistant();
    }
    
    function test_callDescriptor_(){
        $instance = \Art\Database::getInstance();
        $this->assertEquals($instance->quote('test'),'"test"');
    }
    
    function test_callConnector_(){
        $instance = \Art\Database::getInstance();
        $this->assertEquals($instance->isConnected(),true);
    }
}