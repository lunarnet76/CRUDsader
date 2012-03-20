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

		public function formatForDatabase($value)
		{
			return $value ? '1' : '0';
		}

		protected function _inputValid()
		{
			return true;
		}

		public function inputEmpty()
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
			return isset($v) ? (boolean)$v: null;
		}

		public function toHumanReadable()
		{
			return $this->getValue() ? 'yes':'no';
		}

		public function toHtml()
		{
			$this->_htmlAttributes['value'] = 'yes';
			$this->_htmlAttributes['type'] = 'checkbox';
			if ($this->_value == '1')
				$this->_htmlAttributes['checked'] = 'checked';
			return '<input ' . $this->getHtmlAttributesToHtml() . '/>';
		}

		public function inputReceive($data = null)
		{
			
			$this->_value = $data === 'yes' ? true : false;
			
			$this->_inputReceived = true;
			$this->notify();
		}

		public function generateRandom()
		{
			return rand(0, 1);
		}
	}
}
