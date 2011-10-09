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

        protected function _inputValid() {
            if ($this->_inputValue instanceof \CRUDsader\Expression)
                return true;
            else if (strlen(filter_var($this->_inputValue, FILTER_SANITIZE_STRING)) > $this->_options['length'])
                return 'string_error_too_long_' . $this->_options['length'];
            return true;
        }
    }
}