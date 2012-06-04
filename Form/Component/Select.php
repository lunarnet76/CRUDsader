<?php
namespace CRUDsader\Form\Component {
    class Select extends \CRUDsader\Form\Component {

        public function toInput(){#
            $html='<select '.$this->getHtmlAttributesToHtml().' ><option value="-1">Select</option>';
            foreach($this->_options['choices'] as $k=>$v){
                $html.='<option value="'.$k.'" '.(isset($this->_value) && $this->_value==$k?'selected="selected"':'').'>'.$v.'</option>';
            }
            return $html.'</select>';
        }
	
	public function isEmpty()
	{
		return $this->_value == -1;
	}
    }
}