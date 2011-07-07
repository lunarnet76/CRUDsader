<?php
class QueryTest extends PHPUnit_Framework_TestCase {
    function setUp(){
        
            \Art\Configuration::getInstance()->adapter->map->loader->xml->file=dirname(__FILE__).'/parts/map.xml';
    }
    function test_scenario1_() {
        /*
          [1] => SELECT
          [2] => fields
          [3] => FROM
          [4] => employee
          [5] => e
          [6] =>  JOIN tabled(name) td  ON e ASSOCIATION  employee2supplier b
          [7] => WHERE
          [8] => (e.t=? AND e.v=?) OR ?
          [9] => ORDER BY
          [10] => e.t,v.g
          [11] => LIMIT
          [12] => 10
         */

        $query = new \Art\Query('
            SELECT * FROM employee e JOIN hasAddress a ON e 
            WHERE
                (e.id=?)
            ');
        $query->execute(1);
    }
}