<?php
/**
 * @author      Jean-Baptiste Verrey<jeanbaptiste.verrey@gmail.com>
 * @copyright   2011 Jean-Baptiste Verrey
 * @license     see license.txt
 * @since       0.10.6
 */
namespace CRUDsader\Object\Attribute {
	class Email extends \CRUDsader\Object\Attribute {

		/**
		 * return true if valid, string or false otherwise
		 * @return type 
		 */
		protected function _inputValid()
		{
			$strict = !isset($this->_options['strict']) || $this->_options['strict'];
			if (strlen($this->_inputValue) > 320)
				return 'error.email.tooLong';
			if (strpos($this->_inputValue, '@') === false)
				return 'error.email.noAtSign';
			$rf822 = $strict ?
				'/^([.0-9a-z_-]+)@(([0-9a-z-]+\.)+[0-9a-z]{2,4})$/i' :
				'/^([*+!.&#$¦\'\\%\/0-9a-z^_`{}=?~:-]+)@(([0-9a-z-]+\.)+[0-9a-z]{2,4})$/i'
			;
			if (!preg_match($rf822,$this->_inputValue))
				return 'error.email.invalid';
			return true;
		}
	}
}
