<?php
class QueryTest extends PHPUnit_Framework_TestCase {
    function setUp() {
        Bootstrap::loadSQLORMDatabase();
    }
    function test_scenario1_() {
        $query = new \Art\Query('
            FROM contact c 
                JOIN hasAddress a ON c ASSOCIATION ha
                JOIN company cmp ON c 
                JOIN address addressCompany ON cmp 
                JOIN hasLogin l ON c 
                JOIN photo p ON c 
            ');/*
               WHERE
                (c.id=?)
             */
        $execute = $query->execute(1);
        //pre($execute);
       // foreach ($execute as $r)
         //   pre($r);
        \Art\Database::getInstance()->getProfiler()->display();
    }
}