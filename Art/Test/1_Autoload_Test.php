<?php
class AAutoload extends PHPUnit_Framework_TestCase {

    public function test_Namespaces() {
        $namespace = 'arandomnamespace';
        $randomFolder = 'anotherrandomfolder';
        $arandomFolder = 'arandomfolder';
        
        $this->assertEquals(\Art\Autoload::getNamespaces(), array('Art'=>'../'));// because of the bootstrap
        \Art\Autoload::registerNameSpace($namespace, $arandomFolder);
        $this->assertEquals(\Art\Autoload::getNamespaces(), array('Art'=>'../',$namespace => $arandomFolder));
        $this->assertEquals(\Art\Autoload::hasNameSpace($namespace), true);
        \Art\Autoload::registerNameSpace($namespace, $randomFolder);
        $this->assertEquals(\Art\Autoload::getNamespace($namespace), $randomFolder);
        \Art\Autoload::unregisterNameSpace($namespace);
        $this->assertEquals(\Art\Autoload::hasNamespace($namespace), false);
        $this->assertEquals(\Art\Autoload::getNamespaces(), array('Art'=>'../'));
    }

    public function test_includeClass() {
        $class = 'Test\1';
        $path = 'Test\1';
        $this->assertEquals(\Art\Autoload::hasClass($class), false);
        \Art\Autoload::includeClass($class, $path);
        $this->assertEquals(\Art\Autoload::hasClass($class), true);
        \Art\Autoload::unincludeClass($class, $path);
        $this->assertEquals(\Art\Autoload::hasClass($class), false);
    }

    public function test_isloadable() {
        $class = 'TestNamespace\A';
        // the autoloader does not know where is it yet
        $this->assertEquals(\Art\Autoload::isLoadable($class), false);
        \Art\Autoload::registerNameSpace('TestNamespace','parts/1/ClassNamespace/');
        // file is in parts/1/ClassNamespace/A.php
        $this->assertEquals(\Art\Autoload::isLoadable($class), true);
    }

    /**
     * @expectedException Art\AutoloadException
     */
    public function test_loadClass_FailNamespaceDoesNotExist() {
        $className = 'Unexistant\B';
        \Art\Autoload::load($className);
    }

    /**
     * @expectedException Art\AutoloadException
     */
    public function test_loadClass_FailFileDoesNotExist() {
        \Art\Autoload::registerNameSpace('TestNamespace','parts/1/ClassNamespace/');
        $className = 'TestNamespace\unexistant';
        \Art\Autoload::load($className);
        $instance = new $class;
    }

    public function test_loadClass() {
        \Art\Autoload::registerNameSpace('TestNamespace', 'parts/1/ClassNamespace/');
        $class = 'TestNamespace\A';
        \Art\Autoload::load($class);
        $instance = new $class;
        
        $class = '\TestNamespace\F';
        \Art\Autoload::load($class);
        $instance = new $class;
    }

    public function test_autoLoad() {
        $class = 'TestNamespace\C';
        \Art\Autoload::registerNameSpace('TestNamespace','parts/1/ClassNamespace/');
        \Art\Autoload::autoload($class);
        $instance = new $class;
         
        
        $class2 = 'TestNamespace\E';
        \Art\Autoload::unregisterNameSpace('TestNamespace');
        \Art\Autoload::includeClass($class2, 'parts/1/ClassNamespace/E.php');
        \Art\Autoload::autoload($class2);
        $instance = new $class2;
        
       
    }

    /**
     * @expectedException  Art\AutoloadException
     */
    public function test_autoLoad_ExceptionNamespaceDoesNotExist() {
        $class = 'UnexistingNamespace\C';
        \Art\Autoload::autoload($class);
        $instance = new $class;
    }

    /**
     * @expectedException PHPUnit_Framework_Error
     */
    public function test_autoLoad_failFileDoesNotExist() {
        \Art\Autoload::registerNameSpace('TestNamespace','parts/1/ClassNamespace/');
        \Art\Autoload::registerNameSpace(false,'parts/1/NoNamespace/');
        $class = '\TestNamespace\D';
        \Art\Autoload::autoload($class);
        \Art\Autoload::autoload('H');
        \Art\Autoload::autoload('H_Bis');
    }
    
    public function test_simpleAutoload_(){
        $class='Parts_1_NoNamespace_Simple';
        \Art\Autoload::simpleAutoload($class);
        $instance = new $class;
    }

}