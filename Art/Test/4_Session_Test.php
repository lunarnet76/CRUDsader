<?php
class ASession extends PHPUnit_Framework_TestCase {
    /**
     */
    function test_start(){
        $instance=\Art\Session::start();
         $this->assertEquals(isset($_SESSION['Art']),true);
    }

    /**
     * @depends test_start
     */
    function test_useNamespace(){
        $instance=\Art\Session::useNameSpace('test');
        $this->assertEquals($instance instanceof \Art\Session,true);
        $this->assertEquals(isset($_SESSION['Art']['test']),true);
    }

    /**
     * @depends test_start
     */
    function test_setGeneralNameSpace(){
        $ns='NewGeneralNameSpace';
        $this->assertEquals(isset($_SESSION[$ns]),false);
        \Art\Session::setGeneralNamespace($ns);
        $this->assertEquals(isset($_SESSION[$ns]),true);
        \Art\Session::setGeneralNamespace(false);
    }

    /**
     * @depends test_start
     */
    function test_destroy(){
        $instance=\Art\Session::useNameSpace('test');
        $instance->a='b';
        $this->assertEquals(isset($_SESSION['Art']['test']),true);
        \Art\Session::destroy();
        $this->assertEquals(isset($_SESSION['Art']['test']),false);
    }

}