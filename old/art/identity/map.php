<?php

// ensure only that there is no concurent instance of the same object
class Art_Identity_Map{

    protected static $_instance = NULL;
    protected $_objects = NULL;

    protected function __construct() {
        $this->_objects = array();
    }

    public static function getInstance() {
        if (!isset(self::$_instance))
            self::$_instance = new self();
        return self::$_instance;
    }

    public function exists($class, $id) {
        return isset($this->_objects[$class][$id]);
    }

    public function get($class, $id) {
        return $this->_objects[$class][$id];
    }

    public function add(Art_Object &$object) {
        if (!$object->isPersisted())
            throw new Art_Identity_Map_Exception('Object cannot be added as it is not persisted');
        $this->_objects[$object->getClass()][$object->getId()] = $object;
    }

    public function remove(Art_Object $object) {
        unset($this->_objects[$object->getClass()][$object->getId()]);
    }
}

class Art_Identity_Map_Exception extends Exception {

}
?>
