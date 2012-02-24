<?php
/**
 * @author      Jean-Baptiste Verrey<jeanbaptiste.verrey@gmail.com>
 * @copyright   2011 Jean-Baptiste Verrey
 * @license     see license.txt
 * @since       0.10.6
 */
namespace CRUDsader\Object\Attribute {
    class Text extends \CRUDsader\Object\Attribute\String {
        public function toHTML() {
            if (isset($this->_htmlAttributes['value']))
                unset($this->_htmlAttributes['value']);
            return '<textarea ' . $this->getHtmlAttributesToHtml() . '>' . (!\CRUDsader\Expression::isEmpty($this->_inputValue) ? $this->_inputValue : '') . '</textarea>';
        }
        
        protected function _inputValid() {
            return true;
        }
	
	public function formatForDatabase($value) {
            return filter_var($value, FILTER_SANITIZE_STRING);
        }
    }
}
