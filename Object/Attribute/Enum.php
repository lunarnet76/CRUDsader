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
        
    }
}
