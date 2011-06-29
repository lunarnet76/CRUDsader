<?php

class ArtAdapterDatabaseConnectorMysqliInstancer extends \Art\Adapter\Database\Connector\Mysqli {

    public static function getInstance($params=null) {
        return new \Art\Adapter\Database\Connector\Mysqli($params);
    }
}
class AAdapterDatabaseConnectorArtAdapterDatabaseConnectorMysqliInstancer extends PHPUnit_Framework_TestCase {

    /**
     * @expectedException \Art\Adapter\Database\Connector\MysqliException
     */
    function test_connect_ExceptionCantSelect() {
        $instance = ArtAdapterDatabaseConnectorMysqliInstancer::getInstance(new Database_BadNameConfig());
        $instance->connect();
    }

    /**
     * @expectedException \Art\Adapter\Database\Connector\MysqliException
     */
    function test_connect_ExceptionCantConnect() {
        $instance = ArtAdapterDatabaseConnectorMysqliInstancer::getInstance(new Database_BadHostConfig());
        $instance->connect();
    }

    function test_connect_() {
        $instance = ArtAdapterDatabaseConnectorMysqliInstancer::getInstance(new Database_Config());
        $instance->connect();
    }

    function test_disconnect_() {
        $instance = ArtAdapterDatabaseConnectorMysqliInstancer::getInstance(new Database_Config());
        $instance->connect();
        $instance->disconnect();
    }

    function test_query_() {
        $instance = ArtAdapterDatabaseConnectorMysqliInstancer::getInstance(new Database_Config());
        $instance->connect();
        $q = $instance->query('SELECT * FROM employee', 'select');
        $wentInTheLoop = 0;
        foreach ($q as $k => $row) {
            $wentInTheLoop++;
            if ($wentInTheLoop == 1) {
                $this->assertEquals($row['id'], 1);
                $this->assertEquals($row['name'], 'jb');
            }
            if ($wentInTheLoop == 2) {
                $this->assertEquals($row['id'], 2);
                $this->assertEquals($row['name'], 'robert');
            }
        }
        $this->assertEquals($wentInTheLoop, 2);
        $this->assertEquals($q->count(), 2);
        $instance->disconnect();
    }

    /**
     * @expectedException \Art\Adapter\Database\Connector\MysqliException
     */
    function test_query_ExceptionBadQuery() {
        $instance = ArtAdapterDatabaseConnectorMysqliInstancer::getInstance(new Database_Config());
        $instance->connect();
        $q = $instance->query('SELECT * FROM unexistant', 'select');
    }
}