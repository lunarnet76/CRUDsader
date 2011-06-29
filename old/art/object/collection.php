<?php

class Art_Object_Collection extends ArrayIterator {

    protected $_class;
    protected $_objects = array();
    protected $_objectsIds = array();
    protected $_mandatory = false;
    protected $_current;
    protected $_iterator = 0;
    protected $_instanceId;
    protected static $_instanceIdCount = 0;

    public static function instance(Art_Object $object, $associationName) {
        $definition = Art_Mapper::getInstance()->classGetAssociation($object->getClass(), $associationName);
        return $definition['composition'] ? new Art_Object_Collection_Composition($object, $definition) : new Art_Object_Collection_Association($object, $definition);
    }

    public function __construct($class) {
        $this->_class = $class;
        $this->_instanceId = self::$_instanceIdCount++;
    }

    public function getIterator(){
        return $this->_iterator;
    }

    public function getForm($baseClassName=false) {
        $form = new Art_Data_Form($this->_class);
        $form->setLabel($baseClassName);
        for ($i = 0; $i < 3; $i++) {
            if (isset($this->_objectsIds[$i])) {
                $form->add($this->_objects[$this->_objectsIds[$i]]->getForm($baseClassName, $i));
            } else {
                $form->add($this->getCurrentObject()->getForm($baseClassName, $i));
                $this->next();
            }
        }
        return $form;
    }

    protected function _randomizeGetMax($maxObject) {
        return $maxObject;
    }

    public function randomize($maxObject=10) {
        $this->rewind();
        $t = $this->_randomizeGetMax($maxObject);
        if ($t > 0)
            for ($i = 0; $i < $t; $i++) {
                $this->getCurrentObject()->randomize();
                $this->next();
            }
    }

    public function getClass() {
        return $this->_class;
    }

    public function setMandatory($bool) {
        $this->_mandatory = $bool;
    }

    public function isMandatory() {
        return $this->_mandatory;
    }

    protected function _transformObject(Art_Object $value) {
        return $value;
    }

    public function offsetSet($index, $value) {
        if (!$value instanceof Art_Object || $value->getClass() != $this->_class)
            throw new Art_Object_Collection_Exception('you can add only object of class "' . $this->_class . '"');
        if (isset($index)) {
            $this->_objects[$index] = $this->_transformObject($value);
            $this->_objectsIds[] = $index;
        } else {
            $value = $this->_transformObject($value);
            $this->_objects[] = $value;
            end($this->_objects);
            $this->_objectsIds[] = key($this->_objects);
        }
    }

    public function offsetGet($index) {
        if (isset($this->_objects[$index]))
            return $this->_objects[$index];
        if (isset($this->_objectsIds[$index]) && isset($this->_objects[$this->_objectsIds[$index]]))
            return $this->_objects[$this->_objectsIds[$index]];
        throw new Art_Object_Collection_Exception('no object at index "' . $index.'"');
    }

    public function offsetExists($index) {
        return isset($this->_objects[$index]);
    }

    public function getCurrentObject() {
        if (!$this->valid()) {
            $object = $this->_instanceObject();
            $this->_objects[] = $object;
            $value = count($this->_objects) - 1;
            $this->_objectsIds[] = $value;
            return $object;
        }
        return $this->current();
    }

    protected function _instanceObject() {
        return new Art_Object($this->_class);
    }

    public function __set($attributeName, $value) {
        $this->getCurrentObject()->$attributeName = $value;
    }

    public function getDuplicates(){
        $duplicates=array();
        $ret=false;
        foreach ($this->_objects as $k => $o) {
            foreach ($this->_objects as $k2 => $o2) {
                if ($k2 == $k)continue;
                $equals = $o->equals($o2);
                if ($equals !== true){
                    $duplicates[$k][$k2]=$equals;
                    $ret=$equals;
                    continue;
                }
            }
        }
        return array('objects'=>$duplicates,'equals'=>$ret);
    }

    public function save(Art_Work_Unit $unitOfWork=null) {
            $duplicates=$this->getDuplicates();
            if($duplicates['equals'])
                throw new Exception($this->_class . '_duplicate_' . $duplicates['equals']);
            foreach($this->_objects as $o)
                $o->save($unitOfWork);
    }

    public function delete(Art_Work_Unit $unitOfWork=null) {
        foreach ($this->_objects as $object)
            $object->delete($unitOfWork);
    }

    public function __get($attributeName) {
        return $this->getCurrentObject()->$attributeName;
    }


    public function getInstanceId(){
        return $this->_instanceId;
    }
    // HELPER
    public function toArray() {
        $ret = array('class' => $this->_class . '(' . get_class($this) . ')','instanceId'=>$this->_instanceId, 'objects' => array());
        foreach ($this->_objects as $id => $object)
            $ret['objects'][$id] = $object->toArray();
        return $ret;
    }

    public function __toString() {
        return $this->getCurrentObject()->__toString();
    }

    public function count() {
        return count($this->_objects);
    }

    public function getFirst() {
        if(!isset($this->_objectsIds[0]))throw new Exception('no first object');
        return $this->_objects[$this->_objectsIds[0]];
    }

    public function getLast() {
        $index=count($this->_objectsIds)-1;
        if(!isset($this->_objectsIds[$index]))throw new Exception('no last object');
        return $this->_objects[$this->_objectsIds[$index]];
    }

    public function reset() {
        $this->_objects = array();
    }

    // ITERATOR

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
        return $this->_objects[$this->_objectsIds[$this->_iterator]];
    }

    /**
     * return the key of the current object, if the object has been persisted it give its id
     * @return string
     */
    public function key() {
        return $this->_objects[$this->_objectsIds[$this->_iterator]]->hasId() ? $this->_objects[$this->_objectsIds[$this->_iterator]]->getId() : false;
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
        return isset($this->_objectsIds[$this->_iterator]) && isset($this->_objects[$this->_objectsIds[$this->_iterator]]);
    }

}

?>