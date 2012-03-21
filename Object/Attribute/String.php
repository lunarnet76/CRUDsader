<?php
/**
 * @author      Jean-Baptiste Verrey<jeanbaptiste.verrey@gmail.com>
 * @copyright   2011 Jean-Baptiste Verrey
 * @license     see license.txt
 * @since       0.1
 */
namespace CRUDsader\Object\Attribute {
    class String extends \CRUDsader\Object\Attribute {

        public function formatForDatabase($value) {
            return filter_var($value, FILTER_SANITIZE_STRING);
        }

        public function formatFromDatabase($value) {
            return $value;
        }
        
        public function toInput(){
            $this->_htmlAttributes['maxlength']=$this->_options['length'];
            return parent::toInput();
        }

        public function isValid() {
            if (true!== $error = parent::isValid())
				return $error;
            if (isset($this->_options['length']) && strlen(filter_var($this->_value, FILTER_SANITIZE_STRING)) > $this->_options['length'])
                return 'error.string.too-long-' . $this->_options['length'];
            return true;
        }
    }
}
