<?php

class Art_Object_Collection_Association_Class extends Art_Object_Collection_Association {
    public function __construct(Art_Object_Associated $object, $definition) {
        $this->_definition = $definition;
        $this->_linkedToObject = $object;
        Art_Object_Collection::__construct($this->_definition['class']);
    }

    public function _instanceObject() {
        return new Art_Object_Association($this->_linkedToObject, $this->_definition);
    }

    protected function _transformObject(Art_Object $value) {
        if($value instanceof Art_Object_Association)return $value;
        return Art_Object_Association::transform($this->_linkedToObject, $value);
    }
    
    public function save(Art_Work_Unit $unitOfWork=null) {
        if (!count($this->_objects))
            throw new Art_Object_Collection_Association_Class_Exception('Association class must be defined to save the many-to-many relationship between "' . $this->_definition['from'] . '" and "' . $this->_definition['to'] . '"');
    
        Art_Object_Collection::save($unitOfWork);
    }

}

?>
