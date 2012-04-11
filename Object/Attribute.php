<?php
/**
 * @author      Jean-Baptiste Verrey<jeanbaptiste.verrey@gmail.com>
 * @copyright   2011 Jean-Baptiste Verrey
 * @license     see license.txt
 * @since       0.1
 */
namespace CRUDsader\Object {
	class Attribute extends \CRUDsader\Form\Component {
		protected $_name;

		/**
		 * list fields to include in array
		 * @var string
		 */
		protected $_toArray = array(
		    '_name', '_parameters', '_htmlAttributes', '_htmlLabel', '_value', '_valueDefault', '_options'
		);

		public function __construct($name = false, $options = array())
		{
			parent::__construct($options);
			$this->_name = $name;
		}

		// !SETTER
		// null => not received, '' => received
		public function setValueFromInput($data = null)
		{
			if($data === null){
				$this->_value = $this->_valueDefault;
				$this->_inputReceived = false;
			}
			else if ($data === '') {
				$this->_inputReceived = true;
				$this->_value = null;
			}else{
				$this->_value = $data;
			}
			$this->notify();
		}

		/**
		 * when writing object from database
		 * @param type $value
		 */
		public function setValueFromDatabase($value)
		{
			if (!isset($value)) {
				$this->_value = $this->_valueDefault;
			}else
				$this->_valueDefault = $this->_value = $value;
		}

		// !GETTER
		public function getValueForDatabase()
		{
			return $this->_value;
		}

		
		public function toHtml()
		{
			$v = $this->getValue();
			return isset($v) ? $v : '';
		}

		// !FORM
		/**
		 * @return type 
		 */
		public function toInput()
		{
			$this->_htmlAttributes['validator'] = $this->javascriptValidator();
			return parent::toInput();
		}

		public function javascriptValidator()
		{
			return '';
		}

		/**
		 * generate a random value for the attribute
		 * @return string
		 */
		public function generateRandom($object = null)
		{
			return '{'.base_convert(rand(10e16, 10e20), 10, 36).'}';
		}
	}
}