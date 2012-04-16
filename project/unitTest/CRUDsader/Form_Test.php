<?php
class Form_Test extends PHPUnit_Framework_TestCase {

	public function test_inputRequired()
	{
		$form = new \CRUDsader\Form('test_inputRequired');
		$this->assertEquals($form->inputRequired(),true);// because it has no parents		
		$form->setInputRequired(true);
		$this->assertEquals($form->inputRequired(),true);
		$form->setInputRequired(false);
		$this->assertEquals($form->inputRequired(),true);//  it still has no parents
		
		
		$form2 = new \CRUDsader\Form('test_inputRequired');
		$form->add($form2);
		$this->assertEquals($form2->inputRequired(),false);	
		$form2->setInputRequired(true);
		$this->assertEquals($form2->inputRequired(),true);
		$form2->setInputRequired(false);
		$this->assertEquals($form2->inputRequired(),false);
		
	}
	
	public function test_wasPosted()
	{
		$form = new \CRUDsader\Form('test_wasPosted');
		$this->assertEquals($form->wasPosted(),false);
		$_POST['test_wasPosted']='test';
		$this->assertEquals($form->wasPosted(),true);
	}
	
	public function test_wasRequested()
	{
		$form = new \CRUDsader\Form('test_wasRequested');
		$this->assertEquals($form->wasRequested(),false);
		$_REQUEST['test_wasRequested']='test';
		$this->assertEquals($form->wasRequested(),true);
	}
	
	public function test_token(){
		$form = new \CRUDsader\Form('test_token');
		$this->assertEquals($form->checkToken(),false);
		$form->setValueFromInput(array('token'=>$form->getSession()->token));
		$this->assertEquals($form->checkToken(),true);
	}
	
	
	/**
	 *@todo 
	 */
	public function test_session(){//usessesion getsseion sessionreset 
		
	}
	
	public function test_components(){ // add remove offsetExists offsetGet  offsetSet offsetUnset getIterator
		$form = new \CRUDsader\Form('test_components');
		
		$c1 = new \CRUDsader\Form\Component();
		$c2 = new \CRUDsader\Form\Component();
		$c3 = new \CRUDsader\Form\Component();
		$this->assertEquals(count($form->getComponents()),1);// because of submit
		$this->assertEquals($form->offsetExists('c1'),false);
		$form->add($c1,'c1');
		$this->assertEquals(count($form->getComponents()),2);
		$this->assertEquals($form->offsetExists('c1'),true);
		
		$this->assertEquals($form['c1'],$c1);
		$form->remove('c1');
		
		$this->assertEquals(count($form->getComponents()),1);// because of submit
		
		$iterator= $form->getIterator();
		foreach($iterator as $component){
			$this->assertEquals($component instanceof CRUDsader\Form\Component\Submit,true);
		}
	}
	
	
	public function test_view(){
		$form = new \CRUDsader\Form('test_view');
		\CRUDsader\Instancer::getInstance()->configuration->form->view->path = 'Parts/Form/';
		$form->setView('test.view');
		$this->assertEquals($form->getView('test.view'),str_replace('unitTest/CRUDsader','unitTest/Parts/Form/',__DIR__).'test.view.php');// the view contains only the path to the file
	}
	
	

	function test_constructor()
	{
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

		$form->setValueFromInput($_REQUEST[0]);
		$valid = $form->isValid();
		$token = $form->checkToken();
		$this->assertEquals($valid, true);
		$this->assertEquals($token, true);
		if ($valid && $token) {
			$this->assertEquals($i1->getValue(), 'i1v');
			$this->assertEquals($i2->getValue(), 'i2v');
			$this->assertEquals($i3->getValue(), 'i3v');
			$this->assertEquals($i4->getValue(), 'i4v');
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
		$form->setValueFromInput($_REQUEST[0]);
		$valid = $form->isValid();
		$token = $form->checkToken();
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
		$form->setValueFromInput($_REQUEST[0]);
		$valid = $form->isValid();
		$token = $form->checkToken();
		$this->assertEquals($valid, false);
		$this->assertEquals($token, false);

	}
}