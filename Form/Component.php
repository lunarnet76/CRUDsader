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
        protected $_htmlAttributes = array('type'=>'text');
        protected $_htmlLabel = false;
        protected $_inputParent;
        protected $_inputValue=null;
        protected $_inputValueDefault=null;
        protected $_inputError = false;
        protected $_inputRequired = false;
        protected $_inputReceived = false;
        protected $_options = array();
        
        public function __construct(array $options=array()){
            $this->_options=$options;
            $this->_inputValue=new \CRUDsader\Expression\Nil;
            $this->_inputValueDefault=new \CRUDsader\Expression\Nil;
        }

        // ** INTERFACE ** parametrable
        /**
         * @param string $name
         */
        public function setParameter($name=false, $value=null) {
            $this->_parameters[$name] = $value;
        }

        /**
         * @param string $name
         */
        public function unsetParameter($name=false) {
            unset($this->_parameters[$name]);
        }

        /**
         * @param string $name
         * @return bool
         */
        public function hasParameter($name=false) {
            return isset($this->_parameters[$name]);
        }

        /**
         * @param string $name
         * @return mix
         */
        public function getParameter($name=false) {
            return $this->_parameters[$name];
        }

        /**
         * @return array
         */
        public function getParameters() {
            return $this->_parameters;
        }

        // ** INTERFACE ** SplSubject
        /**
         * start being observerd by this object
         * @param \SplObserver $observer 
         */
        public function attach(\SplObserver $observer) {
            $this->_observers[spl_object_hash($observer)] = $observer;
        }

        /**
         * stop being observed by this object
         * @param \SplObserver $observer 
         */
        public function detach(\SplObserver $observer) {
            unset($this->_observers[spl_object_hash($observer)]);
        }

        /**
         * notify all observers that we have been updated
         */
        public function notify() {
            foreach ($this->_observers as $observer)
                $observer->update($this);
        }

        // ** Html **
        /**
         * set an Html attribute tag value
         * @param string $attributeName
         * @param string $attributeValue 
         */
        public function setHtmlAttribute($attributeName, $attributeValue) {
            $this->_htmlAttributes[$attributeName] = $attributeValue;
            return $this;
        }

        /**
         * set an Html attribute tag value
         * @param string $attributeName
         * @param string $attributeValue 
         */
        public function setHtmlAttributes(array $associativeAttributes) {
            $this->_htmlAttributes = $associativeAttributes;
            return $this;
        }

        /**
         * set an Html attribute tag value
         * @param string $attributeName
         * @param string $attributeValue 
         */
        public function unsetHtmlAttribute($attributeName) {
            unset($this->_htmlAttributes[$attributeName]);
        }

        /**
         * get an Html attribute tag value
         * @param type $attributeName
         * @return type 
         */
        public function getHtmlAttribute($attributeName) {
            return $this->_htmlAttributes[$attributeName];
        }

        /**
         * get an Html attribute tag value
         * @param type $attributeName
         * @return type 
         */
        public function hasHtmlAttribute($attributeName) {
            return isset($this->_htmlAttributes[$attributeName]);
        }

        /**
         * get all the Html tag values as a string
         * @return string 
         */
        public function getHtmlAttributes() {
            return $this->_htmlAttributes;
        }

        /**
         * get all the Html tag values as a string
         * @return string 
         */
        public function getHtmlAttributesToHtml() {
            $ret = '';
            foreach ($this->_htmlAttributes as $tagName => $tagValue)
                $ret.=' ' . $tagName . '="' . $tagValue . '"';
            return $ret;
        }

        public function wrapHtml($component, $cssClass) {
            return '<div class="' . $cssClass . '">' . $component . '</div>';
        }

        public function setHtmlLabel($name) {
            $this->_htmlLabel = $name;
            return $this;
        }

        public function getHtmlLabel() {
            return $this->_htmlLabel;
        }
        
        public function labeltoHtml() {
            return $this->wrapHtml($this->_htmlLabel.($this->inputRequired()?'<span class="star">*</span>':''), 'label');
        }

        public function toHtml() {
            $this->_htmlAttributes['value']=(!\CRUDsader\Expression::isEmpty($this->_inputValue)?$this->_inputValue:'');
            return '<input ' . $this->getHtmlAttributesToHtml() . '/>';
        }

        // ** FORM **
        public function inputReceive($data=null) {
            $this->_inputValue = $data;
            $this->_inputReceived = true;
            $this->notify();
        }
        
        public function inputReceiveDefault($data){
            $this->_inputValue=$this->_inputValueDefault=$data;
        }

        public function inputReceived() {
            return $this->_inputReceived;
        }

        public function getInputValue() {
            return $this->_inputValue;
        }

        public function inputEmpty() {
            return empty($this->_inputValue);
        }

        public function setInputRequired($bool) {
            $this->_inputRequired = $bool;
            return $this;
        }

        public function inputRequired() {
            return $this->_inputRequired;
        }

        /**
         * return true if valid, string or false otherwise
         * @return type 
         */
        public function inputValid() {
            return $this->_inputError ? $this->_inputError : $this->_inputValid();
        }

        /**
         * return true if valid, string or false otherwise
         * @return type 
         */
        protected function _inputValid() {
            return true;
        }

        public function setInputError($error) {
            $this->_inputError = $error;
        }

        public function getInputError() {
            return $this->_inputError;
        }

        public function inputReset() {
            $this->_inputError = false;
            $this->_inputValue=new \CRUDsader\Expression\Nil;
        }

        public function hasInputParent() {
            return isset($this->_inputParent);
        }

        public function getInputParent() {
            return $this->_inputParent;
        }


        public function __toString() {
            return $this->toHTML();
        }
    }
    class ComponentException extends \CRUDsader\Exception {
        
    }
}