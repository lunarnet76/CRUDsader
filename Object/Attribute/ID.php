<?php
/**
 * @author      Jean-Baptiste Verrey<jeanbaptiste.verrey@gmail.com>
 * @copyright   2011 Jean-Baptiste Verrey
 * @license     see license.txt
 * @since       28/03/2012
 */
namespace CRUDsader\Object\Attribute {
	class ID extends \CRUDsader\Object\Attribute {

		public function formatForDatabase($value)
		{
			return filter_var($value, FILTER_SANITIZE_NUMBER_INT);
		}

		public function getValue()
		{
			$v = parent::getValue();
			return empty($v) ? null : (int) $v;
		}

		public function isValid()
		{
			if ($this->_value === null)
				return true;
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
		
		public function setValueFromInput($data = null)
		{
			if($data === null){
				$this->_value = null;
				$this->_inputReceived = false;
			}
			else if ($data === '') {
				$this->_inputReceived = true;
				$this->_value = null;
			}else{
				$this->_inputReceived = true;
				$this->_value = $data;
			}
			$this->notify();
		}
	}
}
