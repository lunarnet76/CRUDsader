<?php
/**
 * @author      Jean-Baptiste Verrey<jeanbaptiste.verrey@gmail.com>
 * @copyright   2011 Jean-Baptiste Verrey
 * @license     see license.txt
 * @since       0.1
 */
namespace CRUDsader\Object\Attribute {
	class Float extends \CRUDsader\Object\Attribute {
		public function isValid()
		{
			
			if (true !== $error = parent::isValid())
				return $error;
			return filter_var($this->_value, FILTER_VALIDATE_FLOAT) !== false;
		}

		public function getValue()
		{
			$v = parent::getValue();
			
			return isset($v) ? (float) ($v) : $v;
		}

		public function generateRandom($object = null)
		{
			return rand(0, 1000) . '.' . rand(0, 100);
		}
	}
}
