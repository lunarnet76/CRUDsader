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
		return \CRUDsader\Expression::isEmpty($v) ? null : (int)$v;
	}

        protected function _inputValid() {
            if ($this->_inputValue instanceof \CRUDsader\Expression)
                $ret=true;
            else
                 $ret=filter_var($this->_inputValue, FILTER_VALIDATE_INT)!==false;
            return $ret ? $ret : 'invalid';
        }
        
        public function inputEmpty(){
            return empty($this->_inputValue) && $this->_inputValue!==0  && $this->_inputValue!=='0';
        }
        
        public function generateRandom() {
            return rand(0,1000);
        }
    }
}
