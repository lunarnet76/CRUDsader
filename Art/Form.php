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
    Class Form extends Form\Component implements Interfaces\Helpable, \IteratorAggregate {
        protected $_url;
        protected $_session;
        protected $_useSession = true;
        protected $_components = array();
        protected $_view = 'default';
        protected static $_componentIndex = 0;
        protected static $_helpers = array();

        /**
         * @param string $label
         * @param string $url
         */
        public function __construct($label=false, $url=false) {
            $index = $label !== false ? preg_replace('/[^a-zA-Z0-9_]/', '_', $label) : self::$_componentIndex++;
            $this->_setId($index);
            $this->_label = $label;
            $this->_url = $url;
            $sessionNamespace = Session::useNamespace('Art\\Form\\' . $this->_id);
            if (!isset($sessionNamespace->{$this->_id}))
                $sessionNamespace->{$this->_id} = array();
            $this->_session = $sessionNamespace->{$this->_id};
            // token
            if (isset($this->_session->token))
                $this->_session->oldToken = $this->_session->token;
            $this->_session->token = md5(uniqid(rand(), true));
        }

        /**
         * add an input or element to the form
         * @param Form\Component $component
         * @param string $label
         * @return Form\Component
         */
        public function add(\Art\Form\Component $component, $label=false, $required=false) {
            $index = $label !== false ? preg_replace('/[^a-zA-Z0-9_]/', '_', $label) : self::$_componentIndex++;
            $this->_components[$index] = $component;
            $component->_setId($this->_id . '[' . $index . ']');
            $component->_parent = $this;
            $component->setLabel($label);
            $component->setRequired($required);
            return $component;
        }

        /**
         * remove input or element from form
         * @param string $index
         */
        public function remove($index) {
            if (!isset($this->_components[$index]))
                throw new FormException('component at index "' . $index . '" does not exist (try alphanum index)');
            unset($this->_components[$index]->_id);
            unset($this->_components[$index]->_parent);
            unset($this->_components[$index]);
        }

        /**
         * Iterator to foreach the components
         * @return ArrayIterator
         */
        public function getIterator() {
            return new ArrayIterator($this->_components);
        }

        /**
         * wether or not the form has received the data, meaning that at least one of its elements has been received
         * @param bool|null|array $request
         * @return <type>
         */
        public function receive($data=false) {
            $this->_isReceived = false;
            if ($data === false)
                $data = $_POST;
            if ($data === null || (!$this->hasParent() && !isset($data[$this->_id]))) {
                foreach ($this->_components as $index => $component)
                    $component->receive(null);
                return false;
            }
            if (!$this->hasParent())
                $data = $data[$this->_id];
            foreach ($this->_components as $index => $component) {
                if (isset($data[$index])) {
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
            $this->_session->reset();
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
                }else if (false !== $error = $component->error())
                    $this->_error = $error;
            }
            return $this->_error;
        }
        /* OUPUTS ************************ */

        public function toHTML() {
            $html = $this->htmlTag() . $this->htmlError() . $this->htmlLabel();
            foreach ($this->_components as $component)
                $html.=$this->htmlRow($component);
            return $html . $this->htmlTag();
        }

        public function htmlTag() {
            static $htmlTagIsOpened = false;
            if (!$htmlTagIsOpened) {
                $htmlTagIsOpened = true;
                $htmlAttributes = $this->getHTMLAttributes();
                $tag = $this->hasParent() ? '<fieldset' : '<form enctype="multipart/form-data" action="' . $this->_url . '"';
                return $tag . ' required="' . ($this->_isRequired ? 'true' : 'false') . '" class="' . $this->_css . '" ' . $htmlAttributes . ' id="' . $this->_id . '">';
            } else {
                return $this->hasParent() ? '</fieldset>' : '</form>';
            }
        }

        public function htmlError() {
            return $this->_html($this->_error, 'error');
        }

        public function htmlLabel() {
            return $this->_html($this->_label, 'title');
        }

        public function htmlRow(\Art\Form\Component $component) {
            return $this->_html($this->_html($component->_label, 'label') . $this->_html($component->toHTML(), 'component'), 'row');
        }
        /*         * ACCESSORS ************************* */

        public function getComponents() {
            return $this->_components;
        }

        public function getUrl() {
            return $this->_url;
        }

        public function setUrl($url) {
            $this->_url = $url;
        }

        public function setView($viewPath) {
            $this->_view = $viewPath;
        }

        public function useSession($bool) {
            $this->_useSession = $bool;
        }

        public static function hasHelper($name) {
            return isset($this->_helper[$name]);
        }

        public static function getHelper($name) {
            return $this->_helper[$name];
        }
    }
    class FormException extends \Art\Exception{
        
    }
}