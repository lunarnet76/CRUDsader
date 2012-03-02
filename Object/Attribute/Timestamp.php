<?php
/**
 * @author      Jean-Baptiste Verrey<jeanbaptiste.verrey@gmail.com>
 * @copyright   2011 Jean-Baptiste Verrey
 * @license     see license.txt
 * @since       21/02/2012
 */
namespace CRUDsader\Object\Attribute {
    class timestamp extends \CRUDsader\Object\Attribute {

        /**
         * return true if valid, string or false otherwise
         * @return type 
         */
        protected function _inputValid() {
            if (parent::_inputValid())
                return true;
            if (!preg_match('|[0-9]*|', $this->_inputValue))
                return 'error.invalid';
            return true;
        }

        /**
         * when writing object from database
         * @param type $value
         */
        public function setValueFromDatabase($value) {
            if (\CRUDsader\Expression::isEmpty($value))
                $this->_inputValue = \CRUDsader\Instancer::getInstance()->{'expression.null'};
            else {
                $this->_inputValue = strtotime($value);
            }
        }
	
	public function toHumanReadable(){
		$v = $this->getValue();
		if(\CRUDsader\Expression::isEmpty($v))
			return '';
		return date('d/m/Y h:i',$v);
	}
    }
}