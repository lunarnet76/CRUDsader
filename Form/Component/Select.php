<?php
namespace CRUDsader\Form\Component {
    class Select extends \CRUDsader\Form\Component {

        public function toHTML(){
            $html='<select '.$this->getHtmlAttributesToHtml().' ><option value="">Select</option>';
            foreach($this->_options['choices'] as $k=>$v){
                $html.='<option value="'.$k.'" '.(!\CRUDsader\Expression::isEmpty($this->_inputValue) && $this->_inputValue==$k?'selected="selected"':'').'>'.$v.'</option>';
            }
            return $html.'</select>';
        }
    }
}