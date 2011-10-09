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
            $this->_htmlAttributes['validator'] = $this->javascriptValidator();
            return '<textarea ' . $this->getHtmlAttributesToHtml() . '>'.(!\CRUDsader\Expression::isEmpty($this->_inputValue)?$this->_inputValue:'').'</textarea>';
        }
    }
}
