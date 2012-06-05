<?php
/**
 * @author      Jean-Baptiste Verrey<jeanbaptiste.verrey@gmail.com>
 * @copyright   2011 Jean-Baptiste Verrey
 * @license     see license.txt
 * @since       0.1
 */
namespace CRUDsader\Form {
	/**
	 * @package    CRUDsader\Form
	 * @abstract
	 */
	class Component extends \CRUDsader\MetaClass implements \CRUDsader\Interfaces\Arrayable, \CRUDsader\Interfaces\Parametrable, \SplSubject {
		protected $_parameters = array();
		protected $_observers = array();
		protected $_htmlAttributes = array('type' => 'text');
		protected $_htmlLabel = false;
		protected $_inputParent;
		protected $_value = null;
		protected $_valueDefault = null;
		protected $_inputError = false;
		protected $_inputRequired = false;
		protected $_inputReceived = false;
		protected $_options = array();

		public function __construct(array $options = array())
		{
			parent::__construct();
			$this->_options = $options;
		}

		// ** INTERFACE ** parametrable
		/**
		 * @param string $name
		 */
		public function setParameter($name = false, $value = null)
		{
			$this->_parameters[$name] = $value;
		}

		/**
		 * @param string $name
		 */
		public function unsetParameter($name = false)
		{
			unset($this->_parameters[$name]);
		}

		/**
		 * @param string $name
		 * @return bool
		 */
		public function hasParameter($name = false)
		{
			return isset($this->_parameters[$name]);
		}

		/**
		 * @param string $name
		 * @return mix
		 */
		public function getParameter($name = false)
		{
			return $this->_parameters[$name];
		}

		/**
		 * @return array
		 */
		public function getParameters()
		{
			return $this->_parameters;
		}

		// ** INTERFACE ** SplSubject
		/**
		 * start being observerd by this object
		 * @param \SplObserver $observer 
		 */
		public function attach(\SplObserver $observer)
		{
			$this->_observers[spl_object_hash($observer)] = $observer;
		}

		/**
		 * stop being observed by this object
		 * @param \SplObserver $observer 
		 */
		public function detach(\SplObserver $observer)
		{
			unset($this->_observers[spl_object_hash($observer)]);
		}

		/**
		 * notify all observers that we have been updated
		 */
		public function notify()
		{
			foreach ($this->_observers as $observer)
				$observer->update($this);
		}
		
		/**
		 * list all observers
		 * @return array
		 */
		public function getObservers(){
			return $this->_observers;
		}

		// !HTML
		/**
		 * set an Html attribute tag value
		 * @param string $attributeName
		 * @param string $attributeValue 
		 */
		public function setHtmlAttribute($attributeName, $attributeValue)
		{
			$this->_htmlAttributes[$attributeName] = $attributeValue;
			return $this;
		}

		/**
		 * set an Html attribute tag value
		 * @param string $attributeName
		 * @param string $attributeValue 
		 */
		public function setHtmlAttributes(array $associativeAttributes)
		{
			$this->_htmlAttributes = $associativeAttributes;
			return $this;
		}

		/**
		 * set an Html attribute tag value
		 * @param string $attributeName
		 * @param string $attributeValue 
		 */
		public function unsetHtmlAttribute($attributeName)
		{
			unset($this->_htmlAttributes[$attributeName]);
		}

		/**
		 * get an Html attribute tag value
		 * @param type $attributeName
		 * @return type 
		 */
		public function getHtmlAttribute($attributeName)
		{
			return $this->_htmlAttributes[$attributeName];
		}

		/**
		 * get an Html attribute tag value
		 * @param type $attributeName
		 * @return type 
		 */
		public function hasHtmlAttribute($attributeName)
		{
			return isset($this->_htmlAttributes[$attributeName]);
		}

		/**
		 * get all the Html tag values as a string
		 * @return string 
		 */
		public function getHtmlAttributes()
		{
			return $this->_htmlAttributes;
		}

		/**
		 * get all the Html tag values as a string
		 * @return string 
		 */
		public function getHtmlAttributesToHtml()
		{
			$ret = '';
			foreach ($this->_htmlAttributes as $tagName => $tagValue)
				$ret.=' ' . $tagName . '="' . $tagValue . '"';
			return $ret;
		}

		/**
		 * helper to draw a div
		 * @param \CRUDsader\Form\Component  $component
		 * @param string $cssClass
		 * @return html 
		 */
		public function wrapHtml($component, $cssClass)
		{
			return '<div class="' . $cssClass . '">' . $component . '</div>';
		}

		/**
		 * label
		 * @param string $name
		 * @return \CRUDsader\Form\Component 
		 */
		public function setHtmlLabel($name)
		{
			$this->_htmlLabel = $name;
			return $this;
		}

		/**
		 * @return string
		 */
		public function getHtmlLabel()
		{
			return $this->_htmlLabel;
		}

		/**
		 * @return html
		 */
		public function labeltoHtml()
		{
			return $this->wrapHtml($this->_htmlLabel . ($this->inputRequired() ? '<span class="star">*</span>' : ''), 'label');
		}

		// !FORM
		/**
		 * draw a form input
		 * @return html
		 */
		public function toInput()
		{
			if (isset($this->_value))
				$this->_htmlAttributes['value'] = $this->_value;
			return '<input ' . $this->getHtmlAttributesToHtml() . ' />';
		}

		/**
		 * @param string|bool $error 
		 */
		public function setInputError($error)
		{
			$this->_inputError = $error;
		}

		/**
		 * @return string|bool 
		 */
		public function getInputError()
		{
			return $this->_inputError;
		}

		public function inputReset()
		{
			$this->_inputError = false;
			$this->_value = $this->_valueDefault;
		}

		/**
		 * @return bool
		 */
		public function hasInputParent()
		{
			return isset($this->_inputParent);
		}

		/**
		 * @return self
		 */
		public function getInputParent()
		{
			return $this->_inputParent;
		}

		public function __toString()
		{
			return $this->toInput();
		}

		/**
		 * is the value empty?
		 * @return bool
		 */
		public function isEmpty()
		{
			return empty($this->_value);
		}

		public function setInputRequired($bool)
		{
			$this->_inputRequired = $bool;
			return $this;
		}

		public function inputRequired()
		{
			return $this->_inputRequired;
		}

		/**
		 * is the input received from $_REQUEST ?
		 * @return type 
		 */
		public function inputReceived()
		{
			return $this->_inputReceived;
		}

		public function isModified()
		{
			return $this->_value !== $this->_valueDefault;
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
				$this->_inputReceived = true;
				$this->_value = $data;
			}
			$this->notify();
		}

		/**
		 * set default value
		 * @param mix $data 
		 */
		public function setDefaultValue($data)
		{
			$this->_value = $this->_valueDefault = $data;
		}

		// !GETTER
		public function getValue()
		{
			return $this->_value;
		}

		public function getDefaultValue()
		{
			return $this->_valueDefault;
		}
                
                public function getValueForDatabase(){
                    return $this->_value;
                }

		// !VALIDATION
		/**
		 * return true if valid, string or false otherwise
		 * @return type 
		 */
		public function isValid()
		{
			return $this->_inputError ? $this->_inputError : true;
		}
	}
	class ComponentException extends \CRUDsader\Exception {
		
	}
}