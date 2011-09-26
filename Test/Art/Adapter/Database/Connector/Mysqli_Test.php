<?php
class AdapterDatabaseConnectorMysqli_Test extends PHPUnit_Framework_TestCase {

    function setUp() {
        Bootstrap::setUpDatabase();
    }

    function tearDown() {
        Bootstrap::tearDownDatabase();
    }

    /**
     * @expectedException \Art\Adapter\Database\Connector\MysqliException
     */
    function test_connect_ExceptionCantSelect() {
        $instance = AdapterDatabaseConnectorMysqliInstancer::getInstance(new Database_BadNameConfig());
        $instance->connect();
    }

    /**
     * @expectedException \Art\Adapter\Database\Connector\MysqliException
     */
    function test_connect_ExceptionCantConnect() {
        $instance = AdapterDatabaseConnectorMysqliInstancer::getInstance(new Database_BadHostConfig());
        $instance->connect();
    }

    function test_connect_() {
        $instance = AdapterDatabaseConnectorMysqliInstancer::getInstance(new Database_Config());
        $instance->connect();
        $this->assertEquals($instance->isConnected(), true);
    }

    function test_disconnect_() {
        $instance = AdapterDatabaseConnectorMysqliInstancer::getInstance(new Database_Config());
        $instance->connect();
        $this->assertEquals($instance->isConnected(), true);
        $instance->disconnect();
        $this->assertEquals($instance->isConnected(), false);
    }

    function test_escape_() {
        $instance = AdapterDatabaseConnectorMysqliInstancer::getInstance(new Database_Config());
        $this->assertEquals($instance->escape('"test"'), '\"test\"');
    }

    function test_query_() {
        \Art\Autoload::registerNameSpace('Fakelib', 'Parts/Fakelib/');
        \Art\Configuration::getInstance()->adapter->classNameSpace = 'Fakelib\\Adapter';
        \Art\Configuration::getInstance()->adapter->database = array('rows' => 'mysqli');
        $instance = AdapterDatabaseConnectorMysqliInstancer::getInstance(new Database_Config());
        $instance->connect();
        $q = $instance->query('SELECT * FROM employee', 'select');
        $this->assertEquals($q->count(), 2);
        $q = $instance->query('UPDATE employee SET name="bernard"', 'update');
        $this->assertEquals($q, 2);
        $q = $instance->query('SHOW TABLES', 'other'); // useless query, just to test the return value
        $this->assertEquals($q, true);
    }

    /**
     * @expectedException \Art\Adapter\Database\Connector\MysqliException
     */
    function test_query_ExceptionBadQuery() {
        $instance = AdapterDatabaseConnectorMysqliInstancer::getInstance(new Database_Config());
        $instance->connect();
        $q = $instance->query('SELECT * FROM unexistant', 'select');
    }

    /**
     * @expectedException \Art\Adapter\Database\Connector\MysqliException
     */
    function test_queryStatement_ExceptionPreparationFail() {
        $instance = AdapterDatabaseConnectorMysqliInstancer::getInstance(new Database_Config());
        $instance->prepareQueryStatement('INSERT INTO employee(id,name,unexistant) VALUES(?,?,?)');
    }

    function test_queryStatement_() {
        $instance = AdapterDatabaseConnectorMysqliInstancer::getInstance(new Database_Config());
        $instance->prepareQueryStatement('INSERT INTO employee(id,name,login) VALUES(?,?,?)', 'insert');
        $inserted = array(3, 'robert', '1');
        $insertedAssoc = array('id' => 3, 'name' => 'robert', 'login' => '1');
        $this->assertEquals($instance->executeQueryStatement($inserted), 1);
        $instance->prepareQueryStatement('SELECT * FROM employee WHERE id>?', 'select');
        $r = $instance->executeQueryStatement(array(0));
        $this->assertEquals(count($r), 3);
        $this->assertEquals($r[2], $insertedAssoc);
    }

    /**
     * @expectedException \Art\Adapter\Database\Connector\MysqliException
     */
    function test_executeQueryStatement_ExceptionNotPrepared() {
        $instance = AdapterDatabaseConnectorMysqliInstancer::getInstance(new Database_Config());
        $instance->executeQueryStatement(array(0));
    }

    /**
     * @expectedException \Art\Adapter\Database\Connector\MysqliException
     */
    function test_commit_ExceptionNotStarted() {
        $instance = AdapterDatabaseConnectorMysqliInstancer::getInstance(new Database_Config());
        $instance->commit();
    }

    /**
     * @expectedException \Art\Adapter\Database\Connector\MysqliException
     */
    function test_rollBack_ExceptionNotStarted() {
        $instance = AdapterDatabaseConnectorMysqliInstancer::getInstance(new Database_Config());
        $instance->rollBack();
    }

    function test_transaction_() {
        $instance = AdapterDatabaseConnectorMysqliInstancer::getInstance(new Database_Config());
        $q = $instance->query('SELECT * FROM employee', 'select');
        $this->assertEquals($q->count(), 2);

        $instance->beginTransaction();
        $instance->query('DELETE FROM employee', 'delete');
        $instance->rollBack();
        $q = $instance->query('SELECT * FROM employee', 'select');
        $this->assertEquals($q->count(), 2);

        $instance->beginTransaction();
        $instance->query('DELETE FROM employee', 'delete');
        $instance->commit();
        $q = $instance->query('SELECT * FROM employee', 'select');
        $this->assertEquals($q->count(), 0);
    }
}