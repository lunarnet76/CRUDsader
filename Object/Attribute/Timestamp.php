<?php
/**
 * @author      Jean-Baptiste Verrey<jeanbaptiste.verrey@gmail.com>
 * @copyright   2011 Jean-Baptiste Verrey
 * @license     see license.txt
 * @since       21/02/2012
 */
namespace CRUDsader\Object\Attribute {
	class timestamp extends \CRUDsader\Object\Attribute {

		/**
		 * return true if valid, string or false otherwise
		 * @return type 
		 */
		protected function _inputValid()
		{
			if (parent::_inputValid())
				return true;
			if (!preg_match('|[0-9]*|', $this->_value))
				return 'error.invalid';
			return true;
		}

		/**
		 * when writing object from database
		 * @param type $value
		 */
		public function setValueFromDatabase($value)
		{
			if (isset($value)){
				$this->_value = strtotime($value);
			}
		}

		public function toHumanReadable()
		{
			$v = $this->getValue();
			return isset($v)?date('d/m/Y h:i', $v):'';
		}

		public function getValueForDatabase()
		{
			if($this->_value instanceof \CRUDsader\Expression)
				return $this->_value;
			if ($this->inputEmpty())
				return null;
			if(ctype_digit($this->_value))
				return date('Y-m-d h:i:s',$this->_value);
			if(preg_match('|^([0-9]{2})/([0-9]{2})/([0-9]{4})(?:\s([0-9]{2}\:[0-9]{2}))?$|',$this->_value,$match)){
				return $match[3].'-'.$match[2].'-'.$match[1].' '.$match[4];
			}
			$ex = explode('/', (string)$this->_value);
			switch (count($ex)) {
				case 3:
					return $ex[2] . '-' . $ex[1] . '-' . $ex[0];
					break;
				case 2:
					return $ex[1] . '-' . $ex[0] . '-00';
					break;
				case 1:
					return $ex[0] . '-00-00';
					break;
			}
			return null;
		}
	}
}