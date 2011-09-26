<?php
namespace Art\Object {
    class Collection implements \Art\Interfaces\Initialisable, \ArrayAccess,\Iterator {
        protected $_initialised = false;
        protected $_class = null;
        protected $_classInfos = null;
        protected $_objects = array();
        protected $_iterator = 0;
        protected $_objectIndexes = array();

        public function __construct($className) {
            $this->_class = $className;
            $this->_classInfos = \Art\Map::getInstance()->classGetInfos($this->_class);
        }

        public function toArray($full=false) {
            $ret = array('class' => $this->_class, 'initialised' => $this->_initialised ? 'yes' : 'no', 'objects' => array(), 'indexMap' => $this->_objectIndexes);
            $ret['objects']=array('modified'=>$this->_isModified?'yes':'no');
            foreach ($this->_objects as $k => $object){
                $ret['objects'][$k.':'.$this->_objects[$k]->isPersisted().'@'.$this->_objects[$k]->getLinkedAssociationId()] = $object->toArray($full);
            }
            return $full ? $ret : $ret['objects'];
        }

        public function isInitialised() {
            return $this->_initialised;
        }
        
        public function newObject(){
            // check max
            $this->_objects[++$this->_iterator]=new \Art\Object($this->_class);
            return $this->_objects[$this->_iterator];
        }

        // ITERATOR

        public function offsetSet($index, $value) {
            /*if (!$value instanceof Art_Object || $value->getClass() != $this->_class)
                throw new Art_Object_Collection_Exception('you can add only object of class "' . $this->_class . '"');
            if (isset($index)) {
                $this->_objects[$index] = $this->_transformObject($value);
                $this->_objectIndexes[] = $index;
                return $this->_objects[$index];
            } else {
                $value = $this->_transformObject($value);
                $this->_objects[] = $value;
                end($this->_objects);
                $this->_objectIndexes[] = key($this->_objects);
                return $value;
            }*/
        }
        
        public function offsetUnset($offset) {
            
        }

        public function offsetGet($index) {
            if (isset($this->_objects[$index]))
                return $this->_objects[$index];
            throw new CollectionException('no object at index "' . $index . '"');
        }

        public function offsetExists($index) {
            return isset($this->_objects[$index]);
        }
        
        public function findById($index){
            if (isset($this->_objectIndexes[$index])){
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
    class CollectionException extends \Art\Exception{}
}