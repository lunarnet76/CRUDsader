<?php
namespace CRUDsader\Object {
    class Collection implements \CRUDsader\Interfaces\Initialisable,\CRUDsader\Interfaces\Arrayable, \ArrayAccess, \Iterator {
        protected $_initialised = false;
        protected $_class = null;
        protected $_classInfos;
        protected $_objects = array();
        protected $_iterator = 0;
        protected $_objectIndexes = array();

        public function __construct($className) {
            $this->_class = $className;
            $this->_classInfos = \CRUDsader\Map::getInstance()->classGetInfos($this->_class);
        }

        public function toArray($full=false) {
            $ret = array('class' => $this->_class, 'initialised' => $this->_initialised ? 'yes' : 'no', 'objects' => array(), 'indexMap' => $this->_objectIndexes);
            $ret['objects'] = array('modified' => $this instanceof \CRUDsader\Object\Collection\Association && $this->_isModified ? 'yes' : 'no');
            foreach ($this->_objects as $k => $object) {
                $ret['objects'][$k . ':' . $this->_objects[$k]->isPersisted() . '@' . $this->_objects[$k]->getLinkedAssociationId()] = $object->toArray($full);
            }
            return $full ? $ret : $ret['objects'];
        }
        
        public function count(){
            return count($this->_objects);
        }
        
        public function isInitialised() {
            return $this->_initialised;
        }

        public function newObject() {
            $class=$this->_classInfos['definition']['phpClass'];
            $this->_objects[++$this->_iterator] = new $class($this->_class);
            return $this->_objects[$this->_iterator];
        }

        // ITERATOR

        public function offsetSet($index, $value) {
            if (!$value instanceof \CRUDsader\Object || $value->getClass() != $this->_class)
                throw new CollectionException('you can add only object of class "' . $this->_class . '"');
            $this->_initialised=true;
            $this->_objects[] = $value;
            if ($value->isPersisted()) 
                $this->_objectIndexes[$value->isPersisted()] = count($this->_objects)-1;
            return $value;
        }
        
        public function offsetUnset($index) {
            if(isset($this->_objectIndexes[$index])){
                unset($this->_objects[$this->_objectIndexes[$index]]);
                unset($this->_objectIndexes[$index]);
            }
        }

        public function offsetGet($index) {
            if (isset($this->_objects[$index]))
                return $this->_objects[$index];
            throw new CollectionException('no object at index "' . $index . '"');
        }

        public function offsetExists($index) {
            return isset($this->_objects[$index]);
        }

        public function findById($index) {
            if (isset($this->_objectIndexes[$index])) {
                return $this->_objects[$this->_objectIndexes[$index]];
            }
            throw new CollectionException('no object with id "' . $index . '"');
        }

        /**
         * rewind the iterator,   reset the write mode iterator at the same time
         */
        public function rewind() {
            $this->_iterator = 0;
        }

        /**
         * return the current object
         * @return Crudsader_Object
         */
        public function current() {
            return $this->_objects[$this->_iterator];
        }

        /**
         * return the key of the current object, if the object has been persisted it give its id
         * @return string
         */
        public function key() {
            return $this->_objects[$this->_iterator]->hasId() ? $this->_objects[$this->_iterator]->getId() : false;
        }

        /**
         * go to the next object
         */
        public function next() {
            ++$this->_iterator;
        }

        /**
         * return if the current object exists
         * @return bool
         */
        public function valid() {
            return isset($this->_objects[$this->_iterator]);
        }
    }
    class CollectionException extends \CRUDsader\Exception {
        
    }
}