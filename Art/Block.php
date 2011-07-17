<?php
/**
 * LICENSE: see Art/license.txt
 *
 * @author      Jean-Baptiste Verrey<jeanbaptiste.verrey@gmail.com>
 * @copyright   2011 Jean-Baptiste Verrey
 * @license     http://www.Art.com/license/1.txt
 * @version     $Id$
 * @link        http://www.Art.com/manual/
 * @since       1.0
 */
namespace Art {
    /**
     * for configurable objects
     * @package    Art
     */
    class Block implements \Iterator, Interfaces\Arrayable {
        /**
         * @var array
         */
        protected $_properties = array();
        /**
         * @var bool
         */
        protected $_locked = false;
        /**
         * @var int
         */
        protected $_iterator = 0;

        /**
         * @param array $array of parameters
         */
        protected function __construct(array $array=null) {
            if (isset($array))
                $this->loadArray($array);
        }

        /**
         * load an array of parameters, without emptying the existing ones
         * @param array $array of parameters
         * @param bool $replaceIfExists replace parameter if it exists
         */
        public function loadArray(array $array, $replaceIfExists=true) {
            if ($this->_locked)
                throw new BlockException('Parameters are locked');
            foreach ($array as $key => $value)
                if (is_array($value)) {
                    if (isset($this->_properties[$key]) && $this->_properties[$key] instanceof self)
                        $this->_properties[$key]->loadArray($value, $replaceIfExists);
                    else
                        $this->_properties[$key] = new self($value);
                }else {
                    if ((isset($this->_properties[$key]) && $replaceIfExists) || !isset($this->_properties[$key]))
                        $this->_properties[$key] = $value;
                }
        }

        public function __isset($var) {
            return isset($this->_properties[$var]);
        }

        public function __get($var) {
            return isset($this->_properties[$var]) ? $this->_properties[$var] : null;
        }

        public function __set($var, $value) {
            if ($this->_locked)
                throw new BlockException('Parameter <b>' . $var . '</b> is locked');
            if (is_array($value)) {
                if (isset($this->_properties[$var]) && $this->_properties[$var] instanceof self)
                    $this->_properties[$var]->loadArray($value);
                else
                    $this->_properties[$var] = new self($value);
            }else
                $this->_properties[$var] = $value;
        }

        public function __unset($var) {
            if ($this->_locked)
                throw new BlockException('Parameter <b>' . $var . '</b> is locked');
            unset($this->_properties[$var]);
        }

        /**
         * empty the parameters
         */
        public function reset() {
            if ($this->_locked)
                throw new BlockException('Parameters are locked');
            $this->_properties = array();
        }

        /**
         * count the parameters
         * @return int
         */
        public function count() {
            return count($this->_properties);
        }

        /**
         * return the parameters as an array
         */
        public function toArray() {
            $return = array();
            foreach ($this->_properties as $key => $value)
                if ($value instanceof self) {
                    $return[$key] = $value->toArray();
                }else
                    $return[$key] = $value;
            return $return;
        }

        /**
         * lock modification
         */
        public function lock() {
            $this->_locked = true;
            foreach ($this->_properties as $property)
                if ($property instanceof self)
                    $property->lock();
        }

        /**
         * unlock modification
         */
        public function unLock() {
            $this->_locked = false;
            foreach ($this->_properties as $property)
                if ($property instanceof self)
                    $property->unLock();
        }

        function rewind() {
            rewind($this->_properties);
        }

        function current() {
            return current($this->_properties);
        }

        function key() {
            return key($this->_properties);
        }

        function next() {
            next($this->_properties);
        }

        function valid() {
            return valid($this->_properties);
        }
    }
    class BlockException extends \Art\Exception {
        
    }
}