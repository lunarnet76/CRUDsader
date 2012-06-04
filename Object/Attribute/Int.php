<?php
/**
 * @author      Jean-Baptiste Verrey<jeanbaptiste.verrey@gmail.com>
 * @copyright   2011 Jean-Baptiste Verrey
 * @license     see license.txt
 * @since       0.1
 */
namespace CRUDsader\Object\Attribute {
	class Int extends \CRUDsader\Object\Attribute {


		public function getValue()
		{
			$v = parent::getValue();
			return !isset($v) ? null : (int) $v;
		}

		public function isValid()
		{
			if (true !== $error = parent::isValid())
				return $error;
			$ret = filter_var($this->_value, FILTER_VALIDATE_INT) !== false;
			return $ret ? $ret : 'invalid';
		}

		public function isEmpty()
		{
			return empty($this->_value) && $this->_value !== 0 && $this->_value !== '0';
		}

		public function generateRandom($object = null)
		{
			return rand(0, 1000);
		}
	}
}
