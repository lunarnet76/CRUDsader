<?php
/**
 * @author      Jean-Baptiste Verrey<jeanbaptiste.verrey@gmail.com>
 * @copyright   2011 Jean-Baptiste Verrey
 * @license     see license.txt
 * @since       0.1
 */
namespace CRUDsader\Object {
	class Collection implements \CRUDsader\Interfaces\Initialisable, \CRUDsader\Interfaces\Arrayable, \ArrayAccess, \Iterator {
		protected $_initialised = false;
		protected $_class = null;
		protected $_classInfos;
		protected $_objects = array();
		protected $_iterator = 0;
		protected $_objectIndexes = array();

		public function __construct($className)
		{
			$this->_class = $className;
			$this->_classInfos = \CRUDsader\Instancer::getInstance()->map->classGetInfos($this->_class);
		}

		public function toArray($full = false)
		{
			if (!$this->_initialised)
				return array('initialised' => 'false');
			$ret = array('class' => $this->_class, 'initialised' => $this->_initialised ? 'yes' : 'no', 'objects' => array(), 'indexMap' => $this->_objectIndexes);
			$ret['objects'] = array('modified' => $this instanceof \CRUDsader\Object\Collection\Association && $this->_isModified ? 'yes' : 'no');
			foreach ($this->_objects as $k => $object) {
				$ret['objects']['index:' . $k . ',id:' . $this->_objects[$k]->isPersisted() . ',linked id:' . $this->_objects[$k]->getLinkedAssociationId() . ' ' . (isset($this->_objectsToBeDeleted) && isset($this->_objectsToBeDeleted[$k]) ? '@to be deleted' : '')] = $object->toArray($full);
			}
			return $full ? $ret : $ret['objects'];
		}

		public function toJson($base = true)
		{
			if (!$this->_initialised)
				return array();
			$ret = array();
			foreach ($this->_objects as $k => $object) {
				$ret[$k] = $object->toJson();
			}
			$ret = $base ? array(($base === true ? $this->_class : $base) => $ret) : $ret;
			return $ret;
		}

		public function receiveArray(array $array)
		{
			foreach ($array as $objectArray) {
				if (isset($objectArray[$this->_class]))// special json
					$objectArray = $objectArray[$this->_class];
				pre($objectArray);
				$o = $this->newObject();
				$o->receiveArray($objectArray,true);
				pre($o->toJson());
			}
		}

		public function getLast()
		{
			$count = count($this->_objects);
			return $count ? $this->_objects[$count - 1] : null;
		}

		public function count()
		{
			return count($this->_objects);
		}

		public function isEmpty()
		{
			return empty($this->_objects);
		}

		public function isInitialised()
		{
			return $this->_initialised;
		}

		public function newObject()
		{
			$this->_objects[++$this->_iterator] = \CRUDsader\Object::instance($this->_class);
			return $this->_objects[$this->_iterator];
		}

		// ITERATOR

		public function offsetSet($index, $value)
		{
			if (!$value instanceof \CRUDsader\Object || $value->getClass() != $this->_class)
				throw new CollectionException('you can add only object of class "' . $this->_class . '"');
			$this->_initialised = true;
			$index = isset($index) ? $index : count($this->_objects);
			$this->_objects[$index] = $value;
			if ($value->isPersisted())
				$this->_objectIndexes[$value->isPersisted()] = $index;
			return $this->_objects[$index];
		}

		public function offsetUnset($index)
		{
			if (isset($this->_objectIndexes[$index])) {
				unset($this->_objects[$this->_objectIndexes[$index]]);
				unset($this->_objectIndexes[$index]);
			} else if (isset($this->_objects[$index])) {
				unset($this->_objects[$index]);
			}
		}

		public function offsetGet($index)
		{
			if (isset($this->_objects[$index]))
				return $this->_objects[$index];
			throw new CollectionException('no object at index "' . $index . '"');
		}

		public function offsetExists($index)
		{
			return isset($this->_objects[$index]);
		}

		public function findById($index)
		{
			if (isset($this->_objectIndexes[$index])) {
				return $this->_objects[$this->_objectIndexes[$index]];
			}
			throw new CollectionException('no object with id "' . $index . '"');
		}

		public function hasObjectId($index)
		{
			return isset($this->_objectIndexes[$index]);
		}

		/**
		 * rewind the iterator,   reset the write mode iterator at the same time
		 */
		public function rewind()
		{
			$this->_iterator = 0;
		}

		/**
		 * return the current object
		 * @return Crudsader_Object
		 */
		public function current()
		{
			return $this->_objects[$this->_iterator];
		}

		/**
		 * return the key of the current object, if the object has been persisted it give its id
		 * @return string
		 */
		public function key()
		{
			return $this->_objects[$this->_iterator]->isPersisted() ? $this->_objects[$this->_iterator]->getId() : false;
		}

		/**
		 * go to the next object
		 */
		public function next()
		{
			++$this->_iterator;
		}

		/**
		 * return if the current object exists
		 * @return bool
		 */
		public function valid()
		{
			return isset($this->_objects[$this->_iterator]);
		}
	}
	class CollectionException extends \CRUDsader\Exception {
		
	}
}