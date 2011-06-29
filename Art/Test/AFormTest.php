<?php
class ArtFormComponentRandom extends \Art\Form\Component {
    protected $_data=false;
    public function error() {
        return $this->_data=='error';
    }

    public function isEmpty() {
         return !$this->_isReceived;
    }

    public function toArray() {

    }

    public function receive($data=false) {
       $this->_isReceived=!empty($data);
       $this->_data=$data;
       return $this->_isReceived;
    }
    
    public function reset(){}

    public function toHTML() {
            
    }
}
class AForm extends PHPUnit_Framework_TestCase {

    function test_Constructor_() {
        $label = 'testform1';
        $url = 'http://www.osef.com';
        $form = new \Art\Form($label, $url);
        $this->assertEquals($form->getLabel(), $label);
        $this->assertEquals($form->getUrl(), $url);
        $this->assertEquals($form->getId(), $label);

        $label = 'fq fqw fq a !';
        $labelTransformed = 'fq_fqw_fq_a__';
        $form = new \Art\Form($label);
        $this->assertEquals($form->getId(), $labelTransformed);

        $form = new \Art\Form();
        $this->assertEquals($form->getId(), 0);
    }

    public function test_addAndRemove_() {
        $index = 'index1';
        $form = new \Art\Form();
        $form->add(new ArtFormComponentRandom(), $index);
        $this->assertEquals(array_key_exists($index,$form->getComponents()),true);
        $form->remove($index);
        $this->assertEquals(array_key_exists($index,$form->getComponents()),false);

        $index = 'index 1';
        $indexTransformed='index_1';
        $form->add(new ArtFormComponentRandom(), $index);
        $this->assertEquals(array_key_exists($index,$form->getComponents()),false);
        $this->assertEquals(array_key_exists($indexTransformed,$form->getComponents()),true);
        $form->remove($indexTransformed);
        $this->assertEquals(array_key_exists($index,$form->getComponents()),false);
        $this->assertEquals(array_key_exists($indexTransformed,$form->getComponents()),false);
    }

    /**
     * @expectedException \Art\Form_Component_Exception
     */
    public function test_addAndRemove_ExceptionDoesNotExist(){
         $form = new \Art\Form();
         $form->remove('unexistant');
    }

    function test_receive_() {
        $label = 'testform1';
        $form = new \Art\Form($label);
        $form->useSession(false);

        $c1 = $form->add(new ArtFormComponentRandom(), 'comment1');
        $c2 = $form->add(new ArtFormComponentRandom(), 'comment2');

        $form2 = $form->add(new \Art\Form());
        $form2->useSession(false);
        $c3 = $form2->add(new ArtFormComponentRandom(), 'comment3');

        $request = array(
            $label => array(
                'comment1' => 'value1',
                4 => array(
                    'comment3' => 'value3'
                )
            )
        );
        $this->assertEquals($form->receive($request),true);
        $this->assertEquals($form2->isReceived(),true);
        $this->assertEquals($c1->isReceived(),true);
        $this->assertEquals($c2->isReceived(),false);
        $this->assertEquals($c3->isReceived(),true);
        
        $request = array();
        $this->assertEquals($form->receive($request),false);
        $this->assertEquals($form2->isReceived(),false);
        $this->assertEquals($c1->isReceived(),false);
        $this->assertEquals($c2->isReceived(),false);
        $this->assertEquals($c3->isReceived(),false);
    }

    function test_reset_(){
        $form = new \Art\Form($label);
        $c1 = $form->add(new ArtFormComponentRandom(), 'comment1');
        $c2 = $form->add(new ArtFormComponentRandom(), 'comment2');
        $request = array(
            $label => array(
                'comment1' => 'value1',
                'comment2' => 'value2',
                4 => array(
                    'comment3' => 'value3'
                )
            )
        );
        $form->receive($request);
        $this->assertEquals($c1->isReceived(),true);

         $request = array(
            $label => array(

            )
        );
        $form->receive($request);
        $this->assertEquals($c1->isReceived(),true);// session

        $form->reset();
         $form->receive($request);
        $this->assertEquals($c1->isReceived(),false);
    }

    function test_isEmpty_(){
        $label = 'testform1';
        $form = new \Art\Form($label);
        $form->useSession(false);
        $this->assertEquals($form->isEmpty(),true);
        $c1 = $form->add(new ArtFormComponentRandom(), 'comment1');
        $this->assertEquals($form->isEmpty(),true);
        $this->assertEquals($c1->isEmpty(),true);

        $request = array(
            $label => array(
                'comment1' => 'value1'
            )
        );
        $form->receive($request);
        $this->assertEquals($c1->isEmpty(),false);
    }

    function test_error_(){
        $label = 'testform1';
        $form = new \Art\Form($label);
        $form->useSession(false);
        $c1 = $form->add(new ArtFormComponentRandom(), 'comment1');
        $form->receive();
        $this->assertEquals($form->error(),false);
        $request = array(
            $label => array(
                'comment1' => 'value1'
            )
        );
        $form->receive($request);
        $this->assertEquals($form->error(),false);
         $request = array(
            $label => array(
                'comment1' => 'error'
            )
        );
        $form->receive($request);
        $this->assertEquals($form->error(),true);

        $form2 = new \Art\Form($label);
        $form2->useSession(false);
        $c1 = $form2->add(new ArtFormComponentRandom(), 'comment1',true);
        $request=array($label=>array());
        $form2->receive($request);
        $this->assertEquals($form2->error(),true);
    }
}