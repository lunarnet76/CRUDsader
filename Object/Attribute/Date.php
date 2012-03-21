<?php
/**
 * @author      Jean-Baptiste Verrey<jeanbaptiste.verrey@gmail.com>
 * @copyright   2011 Jean-Baptiste Verrey
 * @license     see license.txt
 * @since       0.1
 */
namespace CRUDsader\Object\Attribute {
	class Date extends \CRUDsader\Object\Attribute {

		/**
		 * return true if valid, string or false otherwise
		 * @return type 
		 */
		protected function isValid()
		{
			if (true!== $error = parent::isValid())
				return $error;
			if (!preg_match('|[0-9]{2}/[0-9]{2}/[0-9]{4}|', $this->_value))
				return 'error.invalid';
			return true;
		}

		/**
		 * when writing object from database
		 * @param type $value
		 */
		public function setValueFromDatabase($value)
		{
			if (!isset($value))
				$this->_value = null;
			else {
				$ex = explode('-', $value);
				switch (count($ex)) {
					case 3:
						$this->_value = ($ex[2] == '00' ? '' : $ex[2] . '/') . ($ex[1] == '00' ? '' : $ex[1] . '/') . $ex[0];
						break;
					case 2:
						$this->_value = $ex[1] . '/' . $ex[0];
						break;
					case 1:
						$this->_value = $ex[0];
						break;
				}
			}
		}

		public function getValueForDatabase()
		{
			if ($this->isEmpty())
				return null;
			$ex = explode('/', $this->_value);
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

		public function generateRandom()
		{
			return date("Y-m-d H:i:s", strtotime('- ' . rand(0, 1000) . ' days'));
		}

		public function toInput()
		{
			$this->_htmlAttributes['class'] = 'date';
			return parent::toInput();
		}
	}
}