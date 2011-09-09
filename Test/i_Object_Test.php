<?php
class ObjectTest extends PHPUnit_Framework_TestCase {
    public function setUp(){
        Bootstrap::loadSQLORMDatabase();
    }
    public function test_(){
        $object=new \Art\Object('contact');
        $object->name='jb';
        $form=$object->getForm('FROM contact c,hasEmail e,hasGroup g');
       
       /* $post=array('contact'=>array('name'=>'notjb'));
        if($form->receive($post) && !$form->error()){
            echo 'ok';
            pre($object->name);
        }*/
            echo $form;
        //pre($object->name);
    }
}
