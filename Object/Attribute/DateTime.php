<?php
/**
 * @author      Jean-Baptiste Verrey<jeanbaptiste.verrey@gmail.com>
 * @copyright   2011 Jean-Baptiste Verrey
 * @license     see license.txt
 * @since       0.1
 */
namespace CRUDsader\Object\Attribute {
	class DateTime extends \CRUDsader\Object\Attribute {

		public function toInput()
		{
			$this->_htmlAttributes['class'] = 'datetime';
			return parent::toInput();
		}

		public function setValueFromDatabase($value)
		{

			if (isset($value)) {
				if (preg_match('|([0-9]{4})-([0-9]{2})-([0-9]{2})\s([0-9]{1,2}):([0-9]{2}):[0-9]{2}|', $value, $m)) {
					$this->_value = $m[3] . '/' . $m[2] . '/' . $m[1] . ' ' . $m[4] . ':' . $m[5];
				}
			}else
				$this->_value = null;
		}

		public function getValueForDatabase()
		{
			if (preg_match('|^([0-9]{2})/([0-9]{2})/([0-9]{4})\s([0-9]{1,2}):([0-9]{2})(:[0-9]{2})?$|', $this->_value, $m)) {
				
				$this->_value = $m[3] . '-' . $m[2] . '-' . $m[1] . ' ' . $m[4] . ':' . $m[5];
			}
			return $this->_value;
		}

		public function generateRandom($data = null)
		{
			return date("Y-m-d H:i:s", strtotime('- ' . rand(0, 1000) . ' days'));
		}
	}
}