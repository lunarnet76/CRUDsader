<?php
/**
 * @author      Jean-Baptiste Verrey<jeanbaptiste.verrey@gmail.com>
 * @copyright   2011 Jean-Baptiste Verrey
 * @license     see license.txt
 * @since       0.1
 */
namespace CRUDsader\Object\Attribute {
    class Float extends \CRUDsader\Object\Attribute {

        public function formatForDatabase($value) {
            return filter_var($value, FILTER_SANITIZE_NUMBER_FLOAT);
        }

        protected function _inputValid() {
            if ($this->_inputValue instanceof \CRUDsader\Expression)
                $ret=true;
            else
                 $ret=filter_var($this->_inputValue, FILTER_VALIDATE_FLOAT)!==false;
            return $ret;
        }
	
	public function getValue(){
		$v = parent::getValue();
		return (float)($v);
	}
        
        
        public function generateRandom() {
            return rand(0,1000).'.'.rand(0,100);
        }
    }
}
