<?php

class Art_Object_Parent extends Art_Object {

    protected $_child;

    public function __construct($className, Art_Object $child) {
        $this->_child = $child;
        parent::__construct($className);
    }

    protected function _getOID() {
        return $this->_child->_id;
    }

    public function setChild(Art_Object $object){
        $this->_child=$object;
    }

    public function getChild(){
        return $this->_child;
    }

    protected function _getParamsForSave() {
        $params = parent::_getParamsForSave();
        $params['polymorphism'] = $this->_child->_class;
        return $params;
    }

}

?>
