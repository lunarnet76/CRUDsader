<?php
/**
 * @author      Jean-Baptiste Verrey<jeanbaptiste.verrey@gmail.com>
 * @copyright   2011 Jean-Baptiste Verrey
 * @license     see license.txt
 * @since       0.1
 */
namespace CRUDsader\Object\Attribute {
    class Enum extends \CRUDsader\Object\Attribute {

        protected function _inputValid() {
            if ($this->_inputValue instanceof \CRUDsader\Expression)
                $ret=true;
            else
                 $ret=in_array($this->_inputValue,$this->_options['choices']);
            return $ret;
        }
	
	public function toHTML(){
		$html = '<select '.$this->getHtmlAttributesToHtml().'><option value="-1">choose</option>';
		
		foreach($this->_options['choices'] as $k=>$v){
			$html.= '<option id="'.$k.'" '.(!$this->inputEmpty() && $this->_inputValue == $k ? 'selected="selected"':'').'>'.$v.'</option>';
		}
		
		$html.='</select>';
		
		return $html;
	}
	
	public function inputEmpty() {
            return $this->_inputValue instanceof \CRUDsader\Expression\Nil || $this->_inputValue == -1;
        }
        
    }
}
