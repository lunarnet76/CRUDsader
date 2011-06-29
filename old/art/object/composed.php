<?php
class Art_Object_Composed extends Art_Object_Associated{
    public static function transform(Art_Object_Collection_Composition $association, Art_Object $object) {
        $class = $association->getClass();
        if ($object->getClass() != $class)
            throw new Art_Object_Associated_Exception('object must be of class ' . $class);
        $new = new self($class,$association);
        $params = get_object_vars($object);
        foreach ($params as $var => $value)
            $new->$var = $value;
        return $new;
    }
    
    public function getForm($baseClassName=false, $number=false, $isParent=false,$isRequired=false) {
        return Art_Object::getForm($baseClassName,$number,$isParent,$isRequired);
    }

     public function delete(Art_Work_Unit $unitOfWork) {
        Art_Object::delete($unitOfWork);
        parent::delete($unitOfWork);
     }

     public function  _getParamsForSave() {
        $ret=parent::_getParamsForSave();
        $ret[$this->_association->getLinkedObject()->getClass()] = $this->_association->getLinkedObject()->getId();
        return $ret;
    }

     public function save(Art_Work_Unit $unitOfWork) {
        parent::save($unitOfWork);
        if($this->isEmpty())
            $this->delete($unitOfWork);
    }
}
?>
