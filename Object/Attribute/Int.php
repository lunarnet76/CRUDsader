<?php
/**
 * @author      Jean-Baptiste Verrey<jeanbaptiste.verrey@gmail.com>
 * @copyright   2011 Jean-Baptiste Verrey
 * @license     see license.txt
 * @since       0.1
 */
namespace CRUDsader\Object\Attribute {
    class Int extends \CRUDsader\Object\Attribute {

        public function formatForDatabase($value) {
            return filter_var($value, FILTER_SANITIZE_NUMBER_INT);
        }
	
	public function getValue(){
		$v = parent::getValue();
		return !isset($v) ? null : (int)$v;
	}

        protected function _inputValid() {
            if ($this->_value instanceof \CRUDsader\Expression)
                $ret=true;
            else
                 $ret=filter_var($this->_value, FILTER_VALIDATE_INT)!==false;
            return $ret ? $ret : 'invalid';
        }
        
        public function inputEmpty(){
            return empty($this->_value) && $this->_value!==0  && $this->_value!=='0';
        }
        
        public function generateRandom() {
            return rand(0,1000);
        }
    }
}
