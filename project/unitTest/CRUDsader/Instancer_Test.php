<?php
class InstancerConfigMock extends \CRUDsader\Block{
    public function __construct(){
        parent::__construct(array(
            'service1'=>array(
                'class'=>'\\TestNamespace\\A',
                'singleton'=>false
            ),
            'service2'=>array(
                'class'=>'\\TestNamespace\\A',
                'singleton'=>true
            ),
            'service3'=>array(
                'class'=>'AMock',
                'singleton'=>false
            ),
            'service4'=>array(
                'class'=>'BMock',
                'singleton'=>false
            )
        ));
    }
}
class AMock{
    public function __construct($arg){
        $this->arg = $arg;
    }
}
abstract class BMock{
    public static function staticCallTest($arg){
        return $arg;
    }
}
class Instancer_Test extends PHPUnit_Framework_TestCase{
    public function setUp(){
        // do not load automatically the config, use a mock object instead
        \CRUDsader\Instancer::$CONFIGURATION_AUTOLOAD = false;
        // for fakelib
        \CRUDsader\Autoload::registerNameSpace('TestNamespace','Parts/FakeLib/ClassNamespace/');
    }
    
    public function test_getInstance(){
        $sl = \CRUDsader\Instancer::getInstance();
        $this->assertEquals($sl instanceof \CRUDsader\Instancer,true);
        $sl2 = \CRUDsader\Instancer::getInstance();
        $this->assertEquals($sl,$sl2);
    }
    
    /**
     *@depends test_getInstance
     */
    public function test_setConfiguration(){
        $sl = \CRUDsader\Instancer::getInstance();
        $this->assertEquals(isset($sl->getConfiguration()->service1),false);
        $sl->setConfiguration(new InstancerConfigMock());
        $this->assertEquals(isset($sl->getConfiguration()->service1),true);
    }
    
    /**
     *@depends test_setConfiguration
     */
    public function test_instance(){
        $sl = \CRUDsader\Instancer::getInstance();
        $this->assertEquals($sl->instance('service1') instanceof \TestNamespace\A,true);
        
        // singleton
        $i1 = $sl->instance('service2');
        $i2 = $sl->instance('service2');
        $this->assertEquals($i1,$i2);
        
        $i1 = $sl->instance('service1');
        $i2 = $sl->instance('service1');
        $this->assertEquals($i1===$i2,false);
        
        // args
        $arg = 'amockvalue';
        $this->assertEquals($sl->instance('service3',array($arg))->arg,$arg);
        
        // get
        $this->assertEquals($sl->service1 instanceof \TestNamespace\A,true);
        
        // call
        $this->assertEquals($sl->service3($arg) instanceof AMock,true);
        $this->assertEquals($sl->service3($arg)->arg,$arg);
        
    }
    
    public function test_callStatic(){
         $sl = \CRUDsader\Instancer::getInstance();
         $arg = 'amockvalue';
         $this->assertEquals($sl->call('service4','staticCallTest',array($arg)),$arg);
    }
}
