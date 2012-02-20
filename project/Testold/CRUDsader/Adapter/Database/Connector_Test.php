<?php
class AdapterDatabaseConnector_Test extends PHPUnit_Framework_TestCase {

    function test_exception_() {
        $instance = AdapterDatabaseConnectorMysqliInstancer::getInstance(new Database_Config());
        $error = false;
        $query = 'SELECT * FROM notatable';
        try {
            $instance->query($query);
        } catch (Exception $e) {
            $error = $e->getSQL() == $query;
        }
        $this->assertEquals($error, true);
    }
}