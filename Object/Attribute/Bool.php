<?php
/**
 * @author      Jean-Baptiste Verrey<jeanbaptiste.verrey@gmail.com>
 * @copyright   2011 Jean-Baptiste Verrey
 * @license     see license.txt
 * @since       0.10.6
 */
namespace CRUDsader\Object\Attribute {
	class Bool extends \CRUDsader\Object\Attribute {
		protected $_parameters = array('isCheckbox' => true);

		public function getValueForDatabase()
		{
			return $this->_value ? '1' : '0';
		}


		public function isEmpty()
		{
			return false;
		}

		public function setValueFromDatabase($val)
		{
			if (!isset($val))
				$val = null;
			$this->_value = $val == 1 ? true : false;
		}

		public function getValue()
		{
			$v = parent::getValue();
			return isset($v) ? (boolean)$v: false;
		}

		public function toHtml()
		{
			return $this->getValue() ? 'yes':'no';
		}

		public function toInput()
		{
			$this->_htmlAttributes['value'] = 'yes';
			$this->_htmlAttributes['type'] = 'checkbox';
			if ($this->_value == '1')
				$this->_htmlAttributes['checked'] = 'checked';
			return '<input ' . $this->getHtmlAttributesToHtml() . '/>';
		}

		public function setValueFromInput($data = null)
		{
			$this->_value = $data === 'yes' || $data === true ? true : false;
			
			$this->_inputReceived = true;
			$this->notify();
		}

		public function generateRandom()
		{
			return rand(0, 1) == 1 ?'yes':'no';
		}
	}
}
