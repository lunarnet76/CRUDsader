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
			if (!preg_match('|[0-9]*|', $this->_inputValue))
				return 'error.invalid';
			return true;
		}

		/**
		 * when writing object from database
		 * @param type $value
		 */
		public function setValueFromDatabase($value)
		{
			if (\CRUDsader\Expression::isEmpty($value))
				$this->_inputValue = \CRUDsader\Instancer::getInstance()->{'expression.null'};
			else {
				$this->_inputValue = strtotime($value);
			}
		}

		public function toHumanReadable()
		{
			$v = $this->getValue();
			if (\CRUDsader\Expression::isEmpty($v))
				return '';
			return date('d/m/Y h:i', $v);
		}

		public function getValueForDatabase()
		{
			if($this->_inputValue instanceof \CRUDsader\Expression)
				return $this->_inputValue;
			if ($this->inputEmpty())
				return new \CRUDsader\Expression\Nil;
			if(ctype_digit($this->_inputValue))
				return date('Y-m-d h:i:s',$this->_inputValue);
			if(preg_match('|^([0-9]{2})/([0-9]{2})/([0-9]{4})(?:\s([0-9]{2}\:[0-9]{2}))?$|',$this->_inputValue,$match)){
				return $match[3].'-'.$match[2].'-'.$match[1].' '.$match[4];
			}
			$ex = explode('/', (string)$this->_inputValue);
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
			return new \CRUDsader\Expression\Nil;
		}
	}
}