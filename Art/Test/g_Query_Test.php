<?php
class QueryTest extends PHPUnit_Framework_TestCase {
    function setUp() {
        \Art\Configuration::getInstance()->adapter->map->loader->xml->file = dirname(__FILE__) . '/parts/map.xml';
    }
    function test_scenario1_() {
        $query = new \Art\Query('
            SELECT * FROM contact c JOIN hasAddress a ON c JOIN 
            WHERE
                (e.id=?)
            ');
        $execute = $query->execute(1);
        foreach ($execute as $r)
            pre($r);
    }
}