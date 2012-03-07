<?php
namespace CRUDsader\Form\Component {
    class Select extends \CRUDsader\Form\Component {

        public function toHtml(){
            $html='<select '.$this->getHtmlAttributesToHtml().' ><option value="">Select</option>';
            foreach($this->_options['choices'] as $k=>$v){
                $html.='<option value="'.$k.'" '.(isset($this->_value) && $this->_value==$k?'selected="selected"':'').'>'.$v.'</option>';
            }
            return $html.'</select>';
        }
    }
}