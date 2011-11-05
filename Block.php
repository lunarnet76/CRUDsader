<?php
/**
 * @author      Jean-Baptiste Verrey<jeanbaptiste.verrey@gmail.com>
 * @copyright   2011 Jean-Baptiste Verrey
 * @license     see license.txt
 * @since       0.1
 */
namespace CRUDsader {
    /**
     * basic block that handles array and add some functionalities
     * @package CRUDsader
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
        public function __construct(array $array=null) {
            if (isset($array))
                $this->loadArray($array);
        }

        /**
         * load an array of parameters, without emptying the existing ones
         * @param array $array of parameters
         * @param bool $replaceIfExists replace parameter if it exists
         * @test test_loadArray
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

        /**
         * 
         * @param string $var
         * @return mix
         * @test test_accessors
         */
        public function __isset($var) {
            return isset($this->_properties[$var]);
        }

        /**
         *
         * @param string $var
         * @return mix 
         * @test test_accessors
         */
        public function __get($var) {
            return isset($this->_properties[$var]) ? $this->_properties[$var] : null;
        }

        /**
         *
         * @param string $var
         * @param mix $value 
         * @test test_accessors
         */
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

        /**
         *
         * @param string $var 
         * @test test_accessors
         */
        public function __unset($var) {
            if ($this->_locked)
                throw new BlockException('Parameter <b>' . $var . '</b> is locked');
            unset($this->_properties[$var]);
        }

        /**
         * empty the parameters
         * @test test_reset
         */
        public function reset() {
            if ($this->_locked)
                throw new BlockException('Parameters are locked');
            $this->_properties = array();
        }

        /**
         * count the parameters
         * @return int
         * @test test_count_
         */
        public function count() {
            return count($this->_properties);
        }

        /**
         * return the parameters as an array
         * @test test_toArray
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
         * @test test_lock_
         */
        public function lock() {
            $this->_locked = true;
            foreach ($this->_properties as $property)
                if ($property instanceof self)
                    $property->lock();
        }

        /**
         * unlock modification
         * @test test_unlock_
         */
        public function unLock() {
            $this->_locked = false;
            foreach ($this->_properties as $property)
                if ($property instanceof self)
                    $property->unLock();
        }

        
        function rewind() {
            reset($this->_properties);
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
            return current($this->_properties)!==false;
        }
    }
    class BlockException extends \CRUDsader\Exception {
        
    }
}