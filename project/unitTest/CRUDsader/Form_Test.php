<?php
class Form_Test extends PHPUnit_Framework_TestCase {

    function test_constructor() {
        $form = new \CRUDsader\Form();
        $this->assertEquals($form->getHtmlAttributes(), array('id' => 0, 'name' => 0, 'method' => 'post', 'action' => false));


        $form2 = $form->add(new \CRUDsader\Form());
        $form3 = $form2->add(new \CRUDsader\Form());

        $i1 = $form2->add(new \CRUDsader\Form\Component(), 'i1', true);
        $i2 = $form3->add(new \CRUDsader\Form\Component(), 'i2', true);
        $i3 = $form3->add(new \CRUDsader\Form\Component(), 'i3', true);
        $i4 = $form3->add(new \CRUDsader\Form\Component(), 'i4', false);
        $i5 = $form3->add(new \CRUDsader\Form\Component(), 'i5', false);
        $form3->remove('i5');
        $_REQUEST = array(
            '0' => array(
                '0' => array(
                    '0' => array(
                        'i2' => 'i2v',
                        'i3' => 'i3v',
                        'i4' => 'i4v',
                    ),
                    'i1' => 'i1v'
                ),
                'token' => $form->getSession()->token
            )
        );
        $receive = $form->inputReceive();
        $valid = $form->inputValid();
        $token = $form->checkToken();
        $this->assertEquals($receive, true);
        $this->assertEquals($valid, true);
        $this->assertEquals($token, true);
        if ($receive && $valid && $token) {
            $this->assertEquals($i1->getInputValue(), 'i1v');
            $this->assertEquals($i2->getInputValue(), 'i2v');
            $this->assertEquals($i3->getInputValue(), 'i3v');
            $this->assertEquals($i4->getInputValue(), 'i4v');
        }

        $_REQUEST = array(
            '0' => array(
                '0' => array(
                    '0' => array(
                        'i2' => '',
                        'i3' => 'i3v',
                        'i4' => 'i4v',
                    ),
                    'i1' => 'i1v'
                ),
                'token' => $form->getSession()->token
            )
        );
        $receive = $form->inputReceive();
        $valid = $form->inputValid();
        $token = $form->checkToken();
        $this->assertEquals($receive, true);
        $this->assertEquals($valid, false);
        $this->assertEquals($token, true);

        $_REQUEST = array(
            '0' => array(
                '0' => array(
                    '0' => array(
                        'i2' => '',
                        'i3' => 'i3v',
                        'i4' => 'i4v',
                    ),
                    'i1' => 'i1v'
                ),
                'token' => ''
            )
        );
        $receive = $form->inputReceive();
        $valid = $form->inputValid();
        $token = $form->checkToken();
        $this->assertEquals($receive, true);
        $this->assertEquals($valid, false);
        $this->assertEquals($token, false);

        $_REQUEST = array();
        $receive = $form->inputReceive();
        $this->assertEquals($receive, false);
    }
}