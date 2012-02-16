<?php
class ObserverRandom implements \SplObserver{
    public $notified=false;
    public function update(\SplSubject $s){
        $this->notified=true;
    }
}
class FormComponent_Test extends PHPUnit_Framework_TestCase {
    function test_interfaceParameters_(){
        $component=new  \CRUDsader\Form\Component();
        $this->assertEquals($component->hasParameter('test'),false);
        $component->setParameter('test','test');
        $this->assertEquals($component->hasParameter('test'),true);
        $component->setParameter('test2','test2');
        $this->assertEquals($component->hasParameter('test2'),true);
        $component->unsetParameter('test2');
        $this->assertEquals($component->hasParameter('test2'),false);
        $this->assertEquals($component->getParameters(),array('test'=>'test'));
    }
    
    function test_interfaceObserver_(){
        $component=new  \CRUDsader\Form\Component();
        $ob=new ObserverRandom();
        $component->attach($ob);
        $this->assertEquals($ob->notified,false);
        $component->notify();
        $this->assertEquals($ob->notified,true);
        $ob->notified=false;
        $component->detach($ob);
        $component->notify();
        $this->assertEquals($ob->notified,false);
    }
    
    function test_htmlAttributes_(){
        $component=new  \CRUDsader\Form\Component();
        $component->setHtmlAttribute('class','test');
        $this->assertEquals($component->getHtmlAttribute('class'),'test');
        $this->assertEquals($component->hasHtmlAttribute('class'),true);
        $component->unsetHtmlAttribute('class');
        $this->assertEquals($component->hasHtmlAttribute('class'),false);
        $component->setHtmlAttribute('class','test');
        $component->setHtmlAttribute('id','test2');
        $this->assertEquals($component->getHtmlAttributes(),array('class'=>'test','id'=>'test2','type'=>'text'));
        $this->assertEquals($component->getHtmlAttributesToHtml(),' type="text" class="test" id="test2"');
    }
    
    function test_htmlLabel_(){
        $component=new  \CRUDsader\Form\Component();
        $component->setHtmlLabel('test');
        $this->assertEquals($component->getHtmlLabel(),'test');
    }
    
    function test_toHTML_(){
        $component=new  \CRUDsader\Form\Component();
        $component->setHtmlAttribute('class','test');
        $component->setHtmlAttribute('id','test2');
        $this->assertEquals($component->toHtml(),'<input  type="text" class="test" id="test2" value=""/>');
    }
    
    function test_form_(){
        $component=new  \CRUDsader\Form\Component();
        $this->assertEquals($component->inputReceived(),false);
        $component->inputReceive('test');
        $this->assertEquals($component->inputReceived(),true);
        $this->assertEquals($component->getInputValue(),'test');
        $this->assertEquals($component->inputEmpty(),false);
        $component->inputReceive('');
        $this->assertEquals($component->inputEmpty(),true);
        $component->inputReceive('test');
        $this->assertEquals($component->inputValid(),true);
        
        $component->setInputError('error');
        $this->assertEquals($component->inputValid(),'error');
        
        
        $component->setInputRequired(true);
        $this->assertEquals($component->inputRequired(),true);
    }
}