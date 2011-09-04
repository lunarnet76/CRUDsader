<?php

class ADatabaseSelect extends PHPUnit_Framework_TestCase {
    function test_instance(){
        $q=new \Art\Database\Select();
    }

    /**
     * @depends test_instance
     * @expectedException \Art\Database\SelectException
     */
    function test_call_ExceptionMethodDoesNotExist(){
        $q=new \Art\Database\Select();
        $q->__call('unexistant',array());
    }

    /**
     * @depends test_instance
     */
    function test_call_(){
        $q=new \Art\Database\Select();
        $q->__call('join',array(
            'fromAlias'=>'o',
            'fromColumn'=>'s',
            'toAlias'=>'e',
            'toColumn'=>'f',
            'toTable'=>'o',
            'type'=>'k'
        ));
    }

     /**
     * @depends test_instance
     * @expectedException \Art\Database\SelectException
     */
    function test_call_ExceptionArgumentsNotAnArray(){
        $q=new \Art\Database\Select();
        $q->__call('join','');
    }

     /**
     * @depends test_instance
     */
    function test_call_normal(){
        $q=new \Art\Database\Select();
        $join=array(
            'fromAlias'=>'o',
            'fromColumn'=>'s',
            'toAlias'=>'e',
            'toColumn'=>'f',
            'toTable'=>'o',
            'type'=>'k'
        );
        $q->join($join);
        $a=$q->getAttributes();
        $this->assertEquals($a['join'][0],$join);
    }
    
}