<?php
namespace Art\Form\Component {
    class Composition extends \Art\Form\Component {

        public function toHTML(){
            $query=new \Art\Query('SELECT * FROM '.$this->_options['class']);
            $html='<select '.$this->getHtmlAttributesToHtml().' ><option value="">Select</option>';
            foreach($query->fetchAll() as $object){
                $html.='<option value="'.$object->isPersisted().'" '.(!$this->_inputValue instanceof \Art\Expression\Nil && $this->_inputValue==$object->isPersisted()?'selected="selected"':'').'>'.$object.'</option>';
            }
            return $html.'</select>';
        }
    }
}