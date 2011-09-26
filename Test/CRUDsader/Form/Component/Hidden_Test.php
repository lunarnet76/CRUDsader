<?php
class FormComponentHidden_Test extends PHPUnit_Framework_TestCase {
    function test_(){
        $component=new  \CRUDsader\Form\Component\Hidden();
        $component->receiveInput('value');
        $component->setHtmlAttribute('name', 'test');
    }
}