<?php
class Autoload_Test extends PHPUnit_Framework_TestCase {
    const FOLDER_NAMESPACE='Parts/FakeLib/ClassNamespace/';
    const FOLDER_NO_NAMESPACE='Parts/FakeLib/NoNamespace/';

    // test for getNamespaces,registerNameSpace,hasNameSpace,getNamespace,unregisterNameSpace
    public function test_Namespaces() {
        $namespace = 'arandomnamespace';
        $randomFolder = 'anotherrandomfolder';
        $arandomFolder = 'arandomfolder';
        
        $this->assertEquals(\CRUDsader\Autoload::getNamespaces(), array('CRUDsader'=>'../CRUDsader/'));// because of the bootstrap
        \CRUDsader\Autoload::registerNameSpace($namespace, $arandomFolder);
        $this->assertEquals(\CRUDsader\Autoload::getNamespaces(), array('CRUDsader'=>'../CRUDsader/',$namespace => $arandomFolder));
        $this->assertEquals(\CRUDsader\Autoload::hasNameSpace($namespace), true);
        \CRUDsader\Autoload::registerNameSpace($namespace, $randomFolder);
        $this->assertEquals(\CRUDsader\Autoload::getNamespace($namespace), $randomFolder);
        \CRUDsader\Autoload::unregisterNameSpace($namespace);
        $this->assertEquals(\CRUDsader\Autoload::hasNamespace($namespace), false);
        $this->assertEquals(\CRUDsader\Autoload::getNamespaces(), array('CRUDsader'=>'../CRUDsader/'));
    }

    // test for hasClass,includeClass,unincludeClass
    public function test_includeClass() {
        $class = 'Test\1';
        $path = 'Test\1';
        $this->assertEquals(\CRUDsader\Autoload::hasClass($class), false);
        \CRUDsader\Autoload::includeClass($class, $path);
        $this->assertEquals(\CRUDsader\Autoload::hasClass($class), true);
        \CRUDsader\Autoload::unincludeClass($class, $path);
        $this->assertEquals(\CRUDsader\Autoload::hasClass($class), false);
    }

    /**
     * @expectedException \CRUDsader\AutoloadException
     */
    public function test_isloadable() {
        $class = 'TestNamespace\A';
        new $class;
    }

    public function test_autoLoad() {
        $class = 'TestNamespace\C';
        \CRUDsader\Autoload::registerNameSpace('TestNamespace',self::FOLDER_NAMESPACE);
        \CRUDsader\Autoload::autoload($class);
        $instance = new $class;
         
        
        $class2 = 'TestNamespace\E';
        \CRUDsader\Autoload::unregisterNameSpace('TestNamespace');
        \CRUDsader\Autoload::includeClass($class2, self::FOLDER_NAMESPACE.'E.php');
        \CRUDsader\Autoload::autoload($class2);
        $instance = new $class2;
    }

    /**
     * @expectedException  CRUDsader\AutoloadException
     */
    public function test_autoLoad_ExceptionNamespaceDoesNotExist() {
        $class = 'UnexistingNamespace\C';
        \CRUDsader\Autoload::autoload($class);
        $instance = new $class;
    }
    
    public function test_simpleAutoload_(){
        $class='Parts_Fakelib_NoNamespace_Simple';
        \CRUDsader\Autoload::simpleAutoload($class);
        $instance = new $class;
    }
    

}