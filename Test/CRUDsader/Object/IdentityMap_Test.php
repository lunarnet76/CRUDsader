<?php
class Test {
    protected $_isPersisted=false;
    protected $_class=false;
    
    public function __construct($id,$class){
        $this->_isPersisted=$id;
        $this->_class=$class;
    }
    public function isPersisted(){
        return $this->_isPersisted;
    }
    public function getClass(){
        return $this->_class;
    }
}
class ObjectIdentityMap_Test extends PHPUnit_Framework_TestCase {
    
    function test_(){
        $this->assertEquals(\CRUDsader\Object\IdentityMap::exists('person',1),false);
        $test=new Test(1,'person');
        \CRUDsader\Object\IdentityMap::add($test);
        $this->assertEquals(\CRUDsader\Object\IdentityMap::exists('person',1),true);
        \CRUDsader\Object\IdentityMap::remove($test);
        $this->assertEquals(\CRUDsader\Object\IdentityMap::exists('person',1),false);
        \CRUDsader\Object\IdentityMap::add($test);
        $this->assertEquals(\CRUDsader\Object\IdentityMap::exists('person',1),true);
        \CRUDsader\Object\IdentityMap::reset();
        $this->assertEquals(\CRUDsader\Object\IdentityMap::exists('person',1),false);
    }
    
    /**
     * @expectedException CRUDsader\Object\IdentityMapException
     */
    function test_add_ExceptionNotPersisted(){
        $test=new Test(false,'person');
        \CRUDsader\Object\IdentityMap::add($test);
    }
}
    