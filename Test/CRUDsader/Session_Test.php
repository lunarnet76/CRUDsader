<?php
class Session_Test extends PHPUnit_Framework_TestCase {
    /**
     */
    function test_start(){
        $instance=\CRUDsader\Session::start();
         $this->assertEquals(isset($_SESSION['CRUDsader']),true);
    }

    /**
     * @depends test_start
     */
    function test_useNamespace(){
        $instance=\CRUDsader\Session::useNameSpace('test');
        $this->assertEquals($instance instanceof \CRUDsader\Session,true);
        $this->assertEquals(isset($_SESSION['CRUDsader']['test']),true);
    }

    /**
     * @depends test_start
     */
    function test_setGeneralNameSpace(){
        $ns='NewGeneralNameSpace';
        $this->assertEquals(isset($_SESSION[$ns]),false);
        \CRUDsader\Session::setGeneralNamespace($ns);
        $this->assertEquals(isset($_SESSION[$ns]),true);
        \CRUDsader\Session::setGeneralNamespace(false);
    }

    /**
     * @depends test_start
     */
    function test_destroy(){
        $instance=\CRUDsader\Session::useNameSpace('test');
        $instance->a='b';
        $this->assertEquals(isset($_SESSION['CRUDsader']['test']),true);
        \CRUDsader\Session::destroy();
        $this->assertEquals(isset($_SESSION['CRUDsader']['test']),false);
    }

}