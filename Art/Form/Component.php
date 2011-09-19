<?php
/**
 * LICENSE: see Art/license.txt
 *
 * @author     Jean-Baptiste Verrey<jeanbaptiste.verrey@gmail.com>
 * @copyright   2011 Jean-Baptiste Verrey
 * @license     http://www.Art.com/license/2.txt
 * @version     $Id$
 * @link        http://www.Art.com/manual/
 * @since       1.0
 */
namespace Art\Form {
    /**
     * @category   Form
     * @package    Art
     * @abstract
     */
    abstract class Component implements \Art\Interfaces\Arrayable, \SplSubject {
        protected $_label = false;
        protected $_observers = array();
        protected $_parent;
        protected $_value;
        protected $_error = false;
        protected $_isRequired = false;
        protected $_isReceived = false;
        protected $_htmlAttributes = array();
        protected $_extras = array();

        public function setCss($cssClass) {
            $this->_htmlAttributes['class'] = $cssClass;
        }
        
        public function setExtra($name,$value){
            $this->_extras[$name]=$value;
        }
        
        public function getExtra($name){
            return $this->_extras[$name];
        }
        
        public function hasExtra($name){
            return isset($this->_extras[$name]);
        }

        public function setError($error) {
            $this->_error = $error;
        }

        public function getValue() {
            return $this->_value;
        }

        public function getError() {
            return $this->_error;
        }

        public function setLabel($name) {
            $this->_label = $name;
        }

        public function setRequired($bool) {
            $this->_isRequired = $bool;
        }

        public function isRequired() {
            return $this->_isRequired;
        }

        public function isReceived() {
            return $this->_isReceived;
        }

        public function hasParentComponent() {
            return isset($this->_parent);
        }

        public function getParentComponent() {
            return $this->_parent;
        }

        public function getId() {
            return $this->_htmlAttributes['id'];
        }

        public function getLabel() {
            return $this->_label;
        }

        public function reset() {
            $this->_error = false;
        }

        public function setHTMLAttribute($attributeName, $attributeValue) {
            $this->_htmlAttributes[$attributeName] = $attributeValue;
        }

        public function getHTMLAttributes() {
            $ret = '';
            foreach ($this->_htmlAttributes as $attributeName => $attributeValue) {
                $ret.=$attributeName . '="' . $attributeValue . '" ';
            }
            return $ret;
        }

        abstract public function error();

        abstract public function isEmpty();

        abstract public function receive($data=false);

        abstract public function toHTML();

        protected function _setId($id) {
            $this->_htmlAttributes['name']=$id;
            $this->_htmlAttributes['id'] = $id;
        }

        public function toArray() {
            return $this->_value;
        }

        protected function _html($component, $type) {
            return '<div class="' . $type . '">' . $component . '</div>';
        }

        public function attach(\SplObserver $observer) {
            $this->_observers[spl_object_hash($observer)] = $observer;
        }

        public function detach(\SplObserver $observer) {
            unset($this->_observers[spl_object_hash($observer)]);
        }

        public function notify() {
            foreach ($this->_observers as $observer)
                $observer->update($this);
        }
    }
    class ComponentException extends \Art\Exception {
        
    }
}