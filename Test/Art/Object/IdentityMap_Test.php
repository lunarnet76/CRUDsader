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
        $this->assertEquals(\Art\Object\IdentityMap::exists('person',1),false);
        $test=new Test(1,'person');
        \Art\Object\IdentityMap::add($test);
        $this->assertEquals(\Art\Object\IdentityMap::exists('person',1),true);
        \Art\Object\IdentityMap::remove($test);
        $this->assertEquals(\Art\Object\IdentityMap::exists('person',1),false);
        \Art\Object\IdentityMap::add($test);
        $this->assertEquals(\Art\Object\IdentityMap::exists('person',1),true);
        \Art\Object\IdentityMap::reset();
        $this->assertEquals(\Art\Object\IdentityMap::exists('person',1),false);
    }
    
    /**
     * @expectedException Art\Object\IdentityMapException
     */
    function test_add_ExceptionNotPersisted(){
        $test=new Test(false,'person');
        \Art\Object\IdentityMap::add($test);
    }
}
    