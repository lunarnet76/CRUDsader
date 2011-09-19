<?php
class ObjectTest extends PHPUnit_Framework_TestCase {

    public function setUp() {
        Bootstrap::loadSQLORMDatabase();
    }

    public function test_() {
        $object = new \Art\Object('person');
        $object->name = 'jb';
        \Art\Session::destroy();
        $form = $object->getForm('FROM person p,parent c,hasEmail e,hasWebSite ws');

        $post = array(
            'person' => array(
                'title' => 'Mr.',
                'name' => 'De Niro',
                'hasEmail' => array(
                    array(
                        'address' => 'email1@msn.com'
                    )
                ),
                'hasWebSite' => array(
                    6
                )
            )
        );
        if ($form->receive($post) && !$form->error()) {
            //pre($object->toArray(false));
        }
        echo $form;
        pre($form);
        //pre($object->name);
    }
}
