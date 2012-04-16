<?php
/**
 * @author      Jean-Baptiste Verrey<jeanbaptiste.verrey@gmail.com>
 * @copyright   2011 Jean-Baptiste Verrey
 * @license     see license.txt
 * @since       0.1
 */
namespace CRUDsader {
	/**
	 *  HTML Form wrapper
	 * @category    Form
	 * @package     CRUDsader
	 */
	Class Form extends Form\Component implements Interfaces\Helpable, Interfaces\Sessionisable, \IteratorAggregate, \ArrayAccess, \CRUDsader\Interfaces\Configurable {
		protected $_url;
		protected $_session;
		protected $_tokenInput = false;
		protected $_tokenInputReceived = false;
		protected $_useSession = true;
		protected $_components = array();
		protected $wrapHtmlTagIsOpened = false;
		protected $_componentIndex = 0;
		protected $_errorComponentIndexes = array();
		protected static $_helpers = array();
		protected static $_formIndex = 0;

		/**
		 * identify the class
		 * @var string
		 */
		protected $_classIndex = 'form';

		/**
		 * @param string $label
		 * @param string $url
		 */
		public function __construct($label = false, $url = false)
		{
			parent::__construct();
			$index = $label !== false ? preg_replace('/[^a-zA-Z0-9_]/', '_', $label) : self::$_formIndex++;
			$this->setHtmlAttributes(array('name' => $index, 'id' => $index, 'action' => $url, 'method' => 'post'));
			$this->setHtmlLabel($label);
			$sessionNamespace = Session::useNamespace('CRUDsader\\Form\\' . $index);
			if (!isset($sessionNamespace->$index))
				$sessionNamespace->$index = array();
			$this->_session = $sessionNamespace->$index;

			if (isset($this->_session->token)) {
				$this->_session->oldToken = $this->_session->token;
				$this->_session->token = md5(uniqid(rand(), true));
			}else
				$this->_session->oldToken = $this->_session->token = md5(uniqid(rand(), true));
			$this->add(new \CRUDsader\Form\Component\Submit(), 'submit')->setHtmlLabel(false);
		}

		// !Form\Component
		/**
		 * @return boolean
		 * @test test_inputRequired
		 */
		public function inputRequired()
		{
			return $this->_inputRequired || !$this->hasInputParent();
		}


		public function view($file, $context = false)
		{
			ob_start();
			require($this->_configuration->view->path . $file . '.php');
			return ob_get_clean();
		}


		/**
		 * @param Block $configuration
		 * @test see parent
		 */
		public function setConfiguration(\CRUDsader\Block $configuration = null)
		{
			$this->_configuration = $configuration;
		}

		/**
		 * @return Block
		 * @test see parent
		 */
		public function getConfiguration()
		{
			return $this->_configuration;
		}

		// !HELPER
		/**
		 * was received via POST
		 * @test test_wasPosted
		 * @return boolean 
		 */
		public function wasPosted()
		{
			return isset($_POST[$this->_htmlAttributes['name']]);
		}

		/**
		 * was received via $_REQUEST
		 * @test test_wasPosted
		 * @return boolean 
		 */
		public function wasRequested()
		{
			return isset($_REQUEST[$this->_htmlAttributes['name']]);
		}

		/**
		 * check if it has already posted once
 		 * @return type 
		 * @test token
		 */
		public function checkToken()
		{
			return $this->_session->oldToken == $this->_tokenInputReceived;
		}

		// ** SESSION **
		/**
		 * activate/deactivate the use of session
		 * @param bool $bool who would have guessed!
		 * @test test_session
		 */
		public function useSession($bool)
		{
			$this->_useSession = $bool;
		}

		/**
		 * 
		 * @return \CRUDsader\Session 
		 * @test test_session
		 */
		public function getSession()
		{
			return $this->_session;
		}

		/*
		 * @test test_session
		 */
		public function resetSession()
		{
			$this->_session->reset();
		}

		/**
		 * add an input or element to the form
		 * @param Form\Component $component
		 * @param string $label
		 * @return Form\Component
		 * @test test_components,test_session
		 */
		public function add(\CRUDsader\Form\Component $component, $label = false, $required = false)
		{
			$index = $label !== false ? preg_replace('/[^a-zA-Z0-9_]/', '_', $label) : $this->_componentIndex++;
			$component->_inputParent = $this;
			if ($component instanceof self)
				$component->_setId($this->_htmlAttributes['name'] . '[' . $index . ']');
			else
				$component->setHtmlAttribute('name', $this->_htmlAttributes['name'] . '[' . $index . ']');
			$component->setHtmlAttribute('id', preg_replace('/[^a-zA-Z0-9_]/', '_', $this->_htmlAttributes['name'] . '[' . $index . ']'));
			$component->setHtmlLabel($label);
			$component->setInputRequired($required);
			$this->_components[$index] = $component;
			return $component;
		}

		/**
		 * remove input or element from form
		 * @param string $index
		 * @test test_components
		 */
		public function remove($index)
		{
			if (!isset($this->_components[$index]))
				throw new FormException('component at index "' . $index . '" does not exist (try alphanum index)');
			$this->_components[$index]->unsetHtmlAttribute('name');
			unset($this->_components[$index]->_inputParent);
			unset($this->_components[$index]);
		}

		// !INTERFACE ArrayAccess
		/**
		 *
		 * @param string $offset
		 * @return bool
		 * @test test_components
		 */
		public function offsetExists($offset)
		{
			return isset($this->_components[$offset]);
		}

		/**
		 * @param string $offset
		 * @return \CRUDsader\Form\Component  
		 * @test test_components
		 */
		public function offsetGet($offset)
		{
			return $this->_components[$offset];
		}

		/**
		 * @param string $offset
		 * @param \CRUDsader\Form\Component  $value
		 * @test test_components
		 */
		public function offsetSet($offset, $value)
		{
			$this->add($value, $offset);
		}

		/**
		 * @param string $offset
		 * @test test_components
		 */
		public function offsetUnset($offset)
		{
			$this->remove($offset);
		}
		
		/**
		 * @return array
		 * @test test_components
		 */
		public function getComponents()
		{
			return $this->_components;
		}

		/**
		 * Iterator to foreach the components
		 * @return ArrayIterator
		 * @test test_components
		 */
		public function getIterator()
		{
			return new \ArrayIterator($this->_components);
		}

		/**
		 * 
		 * @return boolean 
		 */
		public function ok()
		{
			$this->_inputReceived = isset($_REQUEST[$this->_htmlAttributes['name']]);
			if ($this->_inputReceived)
				$this->setValueFromInput($_REQUEST[$this->_htmlAttributes['name']]);
			return $this->inputReceived($_REQUEST) && $this->isValid();
		}

		/**
		 * set input data 
		 * @param array $request
		 */
		public function setValueFromInput($data = null)
		{
			foreach ($this->_components as $index => $component) {
				if (isset($data[$index])) {// $_REQUEST
					if ($this->_useSession) {
						$component->setValueFromInput($data[$index]);
						$this->_session->$index = $data[$index];
					}else
						$component->setValueFromInput($data[$index]);
				} else if ($this->_useSession && isset($this->_session->$index)) {// session
					if ($component->hasParameter('isCheckbox'))
						$component->setValueFromInput(false);
					else
						$component->setValueFromInput($component instanceof self ? $this->_session->$index->toArray() : $this->_session->$index);
				}
			}
			// token
			if (isset($data['token'])) {
				$this->_tokenInputReceived = $data['token'];
			}
		}

		/**
		 * check if there was error when receiving data (i.e. wrong data validation or missing data)
		 * @return bool
		 */
		public function isValid()
		{
			$this->_errorComponentIndexes = array();
			$ret = true;
			if ($this->isEmpty() && !$this->inputRequired()) {
				if ($this->_inputError !== false)
					$this->_errorComponentIndexes['this'] = 'required';
				return $ret && $this->_inputError === false;
			}
			foreach ($this->_components as $name => $component) {
				if ($component->isEmpty()) {
					if ($component->inputRequired()) {
						$this->_errorComponentIndexes[$name] = 'error.form.required';
						$component->setInputError('error.form.required');
						$ret = false;
					}
				} else if (true !== $error = $component->isValid()) {
					$this->_errorComponentIndexes[$name] = $error;
					$component->setInputError($error);
					$ret = false;
				}
			}
			if ($this->_inputError !== false)
				$this->_errorComponentIndexes['this'] = $this->_inputError;
			return $ret && $this->_inputError === false;
		}

		public function getErrors()
		{
			return $this->_errorComponentIndexes;
		}

		/**
		 * the component is reset to a state before he receive anything
		 */
		public function inputReset()
		{
			parent::inputReset();
			$this->resetSession();
			foreach ($this->_components as $component) {
				$component->inputReset();
			}
		}

		/**
		 * wether all the form components are empty
		 * @return <type>
		 */
		public function isEmpty()
		{
			foreach ($this->_components as $name => $component){
				if (!$component->isEmpty() && !$component instanceof \CRUDsader\Form\Component\Submit)
					return false;
			}
			return true;
		}

		/**
		 * for debugging purpose ONLY
		 * @return array
		 */
		public function toArray()
		{
			$ret = array();
			foreach ($this->_components as $name => $component)
				$ret[$name] = $component->toArray();
			return $ret;
		}
		
		/* OUPUTS ************************ */
		public function toInput()
		{
			$html = $this->htmlTag() . ($this->_htmlLabel ? $this->wrapHtml($this->_htmlLabel, 'title') : '') . $this->htmlError();
			foreach ($this->_components as $index => $component) {
				if ($index === 'submit')
					continue;
				$html.=$this->htmlRow($component);
			}
			return $html . (!$this->hasInputParent() && isset($this->_components['submit']) ? $this->htmlRow($this->_components['submit']) : '') . $this->htmlTag();
		}

		public function htmlTag()
		{
			if (!$this->wrapHtmlTagIsOpened) {
				$this->wrapHtmlTagIsOpened = true;
				$htmlAttributes = $this->getHtmlAttributesToHtml();
				$tag = $this->hasInputParent() ? '<fieldset' .(isset($this->_htmlAttributes['class'])?' class='.$this->_htmlAttributes['class']:'').(isset($this->_htmlAttributes['id'])?' id='.$this->_htmlAttributes['id']:''): '<form enctype="multipart/form-data"  ' . $htmlAttributes;
				return $tag . ' ' . ($this->inputRequired() ? 'required="true"' : '') . ' >';
			} else {
				$this->wrapHtmlTagIsOpened = false;
				return $this->hasInputParent() ? '</fieldset>' : '<input type="hidden" name="' . $this->_htmlAttributes['name'] . '[token]" value="' . $this->_session->token . '"/></form>';
			}
		}

		public function htmlError()
		{
			if ($this->_inputError === false)
				return '';
			return $this->wrapHtml(is_bool($this->_inputError) && $this->_inputError ? \CRUDsader\Instancer::getInstance()->i18n->translate('error.form.general') : \CRUDsader\Instancer::getInstance()->i18n->translate($this->_inputError), 'error');
		}

		/**
		 * @todo fix the bug
		 * @param \CRUDsader\Form\Component $component
		 * @return string 
		 */
		public function htmlRow(\CRUDsader\Form\Component $component)
		{
			if ($component instanceof self)
				return $component->toInput();
			$error = $component->getInputError();
			return $this->wrapHtml(($component->_htmlLabel === false ? '' : $component->labeltoHtml()) . $this->wrapHtml($component->toInput(), 'component'.($error?' component_error':'')) . (!$error ? '' : $this->wrapHtml(\CRUDsader\Instancer::getInstance()->i18n->translate($error), 'error')), 'row');
		}
		
		/**
		 * set view path (without base folder)
		 * @test test_view
		 * @param type $viewPath 
		 */
		public function setView($viewIndex)
		{
			$this->_view = $viewIndex;
		}
		
		
		/**
		 * return an HTML view
		 * @param string $file
		 * @param mix $context to specify special vas
		 * @return string 
		 * @test test_view
		 */
		public function getView($context = false)
		{
			ob_start();
			require($this->_configuration->view->path . $this->_view . '.php');
			return ob_get_clean();
		}

		// !ACCESSORS

		/**
		 * @return false|string $url 
		 * @test null
		 */
		public function getUrl()
		{
			return isset($this->_htmlAttributes['action']) ? $this->_htmlAttributes['action'] : false;
		}

		/**
		 * @param string $url 
		 * @test null
		 */
		public function setUrl($url)
		{
			$this->_htmlAttributes['action'] = $url;
		}

		/**
		 * inner function
		 * @param string $name
		 * @return mix
		 * @test null
		 */
		public static function hasHelper($name)
		{
			return isset($this->_helper[$name]);
		}

		/**
		 * inner function
		 * @param string $name
		 * @return mix 
		 * @test null
		 */
		public static function getHelper($name)
		{
			return $this->_helper[$name];
		}

		// !PROTECTED
		protected function _setId($id)
		{
			$this->_htmlAttributes['name'] = $id;
			foreach ($this->_components as $index => $component) {
				if ($component instanceof self)
					$component->_setId($this->_htmlAttributes['name'] . '[' . $index . ']');
				else
					$component->setHtmlAttribute('name', $this->_htmlAttributes['name'] . '[' . $index . ']');
			}
		}
	}
	class FormException extends \CRUDsader\Exception {
		
	}
}