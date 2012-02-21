<?php
class AMock extends \CRUDsader\MetaClass{
    protected $_hasDependencies = array(
        'metaClassMock1'
    );
}
class BMock{
    
}
class CMock extends \CRUDsader\MetaClass{
    public $a='a',$b='b',$c='c';
    protected $_toArray = array('a');
}

class ConfigMock extends \CRUDsader\Block{
    public function __construct($args = array('d'=>'v')){
        parent::__construct($args);
    }
}

class DMock extends \CRUDsader\MetaClass{
    protected $_classIndex = 'i18n';
}

class MetaClass_Test extends PHPUnit_Framework_TestCase {
    
    public function setUp(){
        $cfg = \CRUDsader\Instancer::getInstance()->getConfiguration();
        $cfg->metaClassMock1 = array(
            'class'=>'BMock',
            'singleton'=>false
        );
        $cfg->metaClassMock2 = array(
            'class'=>'CMock',
            'singleton'=>false
        );
        \CRUDsader\Instancer::getInstance()->setConfiguration($cfg);
    }
    
    public function test_dependencies(){
        $dependency = 'metaClassMock1';
        $a = new AMock();
        $this->assertEquals($a->hasDependency($dependency),true);
        $this->assertEquals($a->getDependency($dependency) instanceof BMock,true);
        
        
        $dependency = 'metaClassMock2';
        $this->assertEquals($a->hasDependency($dependency),false);
        $a->setDependency($dependency,'metaClassMock2');
        $this->assertEquals($a->hasDependency($dependency),true);
        $this->assertEquals($a->getDependency($dependency) instanceof CMock,true);
    }
    
    public function test_toArray(){
        $c = new CMock();
        $this->assertEquals($c->toArray(),array('a'=>'a'));
    }
    
    public function test_configuration(){
        $a = new AMock();
        $config = new ConfigMock();
        $a->setConfiguration($config);
        $this->assertEquals($a->getConfiguration(),$config);
    }
    
    public function test_autoconfiguration(){
        $d = new DMock();
        $this->assertEquals($d->getConfiguration(),\CRUDsader\Instancer::getInstance()->configuration->i18n);// random one
    }
}
