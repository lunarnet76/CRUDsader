<?php
/**
 * @author      Jean-Baptiste Verrey<jeanbaptiste.verrey@gmail.com>
 * @copyright   2011 Jean-Baptiste Verrey
 * @license     see license.txt
 * @since       0.1
 */
namespace CRUDsader\Object\Attribute {
    class Date extends \CRUDsader\Object\Attribute {

        /**
         * return true if valid, string or false otherwise
         * @return type 
         */
        protected function _inputValid() {
            if (parent::_inputValid())
                return true;
            if(!preg_match('|[0-9]{2}/[0-9]{2}/[0-9]{4}|', $this->_inputValue))
                return 'invalid';
            return true;
        }
    }
}