<?php
class AdapterIdentifierHiloTest extends PHPUnit_Framework_TestCase {
   public function test_oid_(){
       $instance=\Art\Adapter::factory('identifier');
       $this->assertEquals($instance->getOID('a'),$instance->getOID('b'));
       $this->assertEquals($instance->getOID('c'),$instance->getOID('d'));
       $this->assertEquals($instance->getOID('a'),$instance->getOID('c'));
       $this->assertEquals(strlen($instance->getOID('a')),11);
   } 
}