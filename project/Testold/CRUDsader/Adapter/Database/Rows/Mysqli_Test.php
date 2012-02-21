<?php

class AdapterDatabaseRowsMysqli_Test extends PHPUnit_Framework_TestCase {

    function setUp() {
        Bootstrap::setUpDatabase();
    }

    function tearDown() {
        Bootstrap::tearDownDatabase();
    }

    function test_() {
        $instance = AdapterDatabaseConnectorMysqliInstancer::getInstance(new Database_Config());
        $q = $instance->query('SELECT * FROM employee', 'select');
        foreach($q as $index=>$r){
            if($r[0]=='1'){
                $this->assertEquals($r[1],'jb');
                $this->assertEquals($r[2],'1');
            }else{
                $this->assertEquals($r[1],'robert');
                $this->assertEquals($r[2],null);
            }
        }
        foreach($q as $index=>$r){
            if($r[0]=='1'){
                $this->assertEquals($r[1],'jb');
                $this->assertEquals($r[2],'1');
            }else{
                $this->assertEquals($r[1],'robert');
                $this->assertEquals($r[2],null);
            }
        }
    }
}