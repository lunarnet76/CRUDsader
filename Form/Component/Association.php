<?php
namespace CRUDsader\Form\Component {
    class Association extends \CRUDsader\Form\Component {

        public function toHTML(){
            $query=new \CRUDsader\Query('FROM '.$this->_options['class'].(\CRUDsader\Instancer::getInstance()->map->classHasParent($this->_options['class'])?',parent':''));
            $html='<select '.$this->getHtmlAttributesToHtml().' ><option value="">Select</option>';
            foreach($query->fetchAll() as $object){
                $html.='<option value="'.$object->isPersisted().'" '.(!$this->_inputValue instanceof \CRUDsader\Expression\Nil && $this->_inputValue==$object->isPersisted()?'selected="selected"':'').'>'.$object.'</option>';
            }
            return $html.'</select>';
        }
	
	public function inputEmpty() {
            return \CRUDsader\Expression::isEmpty($this->_inputValue);
        }
    }
}