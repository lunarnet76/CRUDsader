<?php
/**
 * LICENSE: see Art/license.txt
 *
 * @author      Jean-Baptiste Verrey <jeanbaptiste.verrey@gmail.com>
 * @copyright   2011 Jean-Baptiste Verrey
 * @license     http://www.Art.com/license/1.txt
 * @version     $Id$
 * @link        http://www.Art.com/manual/
 * @since       1.0
 */
namespace Art {
    /**
     *  HTML Form wrapper
     * @category    Form
     * @package     Art
     * @todo add handler for checkboxes, as PHP will not create an entry in the $data array in receive($data=false)
     */
    Class Form extends Form\Component implements Interfaces\Helpable, Interfaces\Sessionisable, \IteratorAggregate {
        protected $_url;
        protected $_session;
        
        protected $_useSession = true;
        protected $_components = array();
        protected $_htmlTagIsOpened = false;
        protected $_htmlAttributes = array(
            'method' => 'POST'
        );
        protected $_componentIndex = 0;
        protected static $_formIndex = 0;
        protected static $_helpers = array();

        /**
         * @param string $label
         * @param string $url
         */
        public function __construct($label=false, $url=false) {
            $index=$label !== false ? preg_replace('/[^a-zA-Z0-9_]/', '_', $label) : self::$_formIndex++;
            $this->_setId($index);
            $this->_label = $label;
            $this->_htmlAttributes['action'] = $url;
            $sessionNamespace = Session::useNamespace('Art\\Form\\' . $index);
            if (!isset($sessionNamespace->$index))
                $sessionNamespace->$index = array();
            $this->_session = $sessionNamespace->$index;
            // token
            if (isset($this->_session->token))
                $this->_session->oldToken = $this->_session->token;
            $this->_session->token = md5(uniqid(rand(), true));
        }
        
        public function useSession($bool) {
            $this->_useSession = $bool;
        }
        
        public function getSession(){
            return $this->_session;
        }
        
        public function resetSession(){
            $this->_session->reset();
        }

        /**
         * add an input or element to the form
         * @param Form\Component $component
         * @param string $label
         * @return Form\Component
         */
        public function add(\Art\Form\Component $component, $label=false, $required=false) {
            $index = $label !== false ? preg_replace('/[^a-zA-Z0-9_]/', '_', $label) : $this->_componentIndex++;
            $component->_parent = $this;
            $component->_setId($this->_htmlAttributes['name'] . '[' . $index . ']');
            $component->setLabel($label);
            $component->setRequired($required);
            $this->_components[$index] = $component;
            return $component;
        }
        
        public function _setId($id){
            parent::_setId($id);
            foreach($this->_components as $index=>$component)
                $component->_setId($this->_htmlAttributes['name'] . '[' . $index . ']');
        }

        /**
         * remove input or element from form
         * @param string $index
         */
        public function remove($index) {
            if (!isset($this->_components[$index]))
                throw new FormException('component at index "' . $index . '" does not exist (try alphanum index)');
            unset($this->_components[$index]->_htmlAttributes['name']);
            unset($this->_components[$index]->_parent);
            unset($this->_components[$index]);
        }

        /**
         * Iterator to foreach the components
         * @return ArrayIterator
         */
        public function getIterator() {
            return new \ArrayIterator($this->_components);
        }

        /**
         * wether or not the form has received the data, meaning that at least one of its elements has been received
         * @param bool|null|array $request
         * @return <type>
         */
        public function receive($data=false) {
            $this->_isReceived = false;
            if ($data === false)
                $data = $_REQUEST;
            if ($data === null || (!$this->hasParentComponent() && !isset($data[$this->_htmlAttributes['name']]))) {
                foreach ($this->_components as $index => $component)
                    $component->receive(null);
                return false;
            }
            if (!$this->hasParentComponent())
                $data = $data[$this->_htmlAttributes['name']];
            foreach ($this->_components as $index => $component) {
                if (isset($data[$index])) {
                    if($this->_useSession)
                        $this->_session->$index = $data[$index];
                    $component->receive($data[$index]);
                } else if ($this->_useSession && isset($this->_session->$index)) {
                    $component->receive($this->_session->$index);
                }else
                    $component->receive(null);
            }
            $this->_isReceived = true;
            return true;
        }

        /**
         * the component is reset to a state before he receive anything
         */
        public function reset() {
            parent::reset();
            $this->resetSession();
            foreach ($this->_components as $component)
                $component->reset();
        }

        /**
         * wether all the form components are empty
         * @return <type>
         */
        public function isEmpty() {
            foreach ($this->_components as $component)
                if (!$component->isEmpty())
                    return false;
            return true;
        }

        /**
         * for debugging purpose ONLY
         * @return array
         */
        public function toArray() {
            $ret = array();
            foreach ($this->_components as $name => $component)
                $ret[$name] = $component->toArray();
            return $ret;
        }

        /**
         * check if there was error when receiving data (i.e. wrong data validation or missing data)
         * @return <type>
         */
        public function error() {
            $error = $this->_error;
            foreach ($this->_components as $name => $component) {
                if ($component->isEmpty()) {
                    if ($component->isRequired())
                        $this->_error = 'form_error_required_' . $name;
                }else if (false !== $error = $component->error()) {
                    $this->_error = $error;
                }
            }
            return $this->_error;
        }
        /* OUPUTS ************************ */

        public function toHTML() {
            $html = $this->htmlTag() . $this->htmlLabel() . $this->htmlError();
            foreach ($this->_components as $component) {
                $html.=$this->htmlRow($component);
            }
            return $html . $this->htmlTag();
        }

        public function __toString() {
            return $this->toHTML();
        }

        public function htmlTag() {
            if (!$this->_htmlTagIsOpened) {
                $this->_htmlTagIsOpened = true;
                $htmlAttributes = $this->getHTMLAttributes();
                $tag = $this->hasParentComponent() ? '<fieldset' : '<form enctype="multipart/form-data" ' . $htmlAttributes;
                return $tag . ' required="' . ($this->_isRequired ? 'true' : 'false') . '" ' . $htmlAttributes . '>';
            } else {
                return $this->hasParentComponent() ? '</fieldset>' : '<input type="hidden" name="' . $this->_htmlAttributes['name'] . '[token]" value=""><input type="submit" name="' . $this->_htmlAttributes['name'] . '[submit]" style="height:0;visibility:hidden"></form>';
            }
        }

        public function htmlError() {
            return $this->_html(is_bool($this->_error) && $this->_error ? \Art\I18n::getInstance()->translate('form_error_general') : $this->_error, 'error');
        }

        public function htmlLabel() {
            return $this->_html($this->_label, 'title');
        }

        /**
         * @todo fix the bug
         * @param \Art\Form\Component $component
         * @return string 
         */
        public function htmlRow(\Art\Form\Component $component) {
            if ($component instanceof self)
                return $component->toHTML();
            return $this->_html($this->_html($component->_label, 'label') . $this->_html($component->toHTML(), 'component') . $this->_html($component->getError(), 'error'), 'row');
        }

        /** ACCESSORS ************************* */
        public function getComponents() {
            return $this->_components;
        }

        public function getUrl() {
            return isset($this->_htmlAttributes['action']) ? $this->_htmlAttributes['action'] : false;
        }

        public function setUrl($url) {
            $this->_htmlAttributes['action'] = $url;
        }

        public function setView($viewPath) {
            $this->_view = $viewPath;
        }

        public static function hasHelper($name) {
            return isset($this->_helper[$name]);
        }

        public static function getHelper($name) {
            return $this->_helper[$name];
        }
    }
    class FormException extends \Art\Exception {
        
    }
}