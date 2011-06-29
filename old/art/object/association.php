<?php
class Art_Object_Association extends Art_Object_Associated {
    protected $_associatedObject = NULL;
    const NO_CLASS='__ASSOCIATION__';

    public static function transform(Art_Object_Associated $object, Art_Object $associationObject) {
        $class = $object->getAssociationObject()->getAssociationClassName();
        if (!$class && Art_Object::$_writeMode)
            $class = Art_Object_Association::NO_CLASS;
        if ($associationObject->getClass() != $class)
            throw new Art_Object_Associated_Exception('object must be of class ' . $class);
        $new = new self($object);
        $params = get_object_vars($associationObject);
        foreach ($params as $var => $value)
            $new->$var = $value;
        
        $new->_instanceId = self::$_instanceIdCount++;
        return $new;
    }

    public function __construct(Art_Object_Associated $associatedObject) {
        $this->_associatedObject = $associatedObject;
        parent::__construct($associatedObject->_association->getAssociationClassName(), $associatedObject->_association);
    }

    public function randomize() {
        Art_Object::randomize();
    }

    public function getTableName() {
        return $this->_associatedObject->_association->getTableName();
    }

    protected function _isModifiedEnoughToBeSaved() {
        return $this->_associatedObject->_association->getLinkedObject()->_isPersisted && $this->_associatedObject->_isPersisted;
    }

    protected function _getAttributesForForm(Art_Data_Form $form) {
        Art_Object::_getAttributesForForm($form);
    }

    protected function _getParamsForSave() {
        $ret = Art_Object::_getParamsForSave();
        $linked = $this->_associatedObject->_association->getLinkedObject();
        $ret[$linked instanceof Art_Object_Association && Art_Mapper::getInstance()->classIsAssociationClass($linked->getClass()) ? $linked->_association->getName() : $linked->getClass()] = $linked->getId();
        $ret[$this->_associatedObject->getClass()] = $this->_associatedObject->getId();
        return $ret;
    }

    protected function _getAlias() {
        return $this->_associatedObject->getAlias() . '_' . $this->_class;
    }

    protected function _checkIfSameObjectExistsInDatabaseWhere() {
        $where = parent::_checkIfSameObjectExistsInDatabaseWhere();
        if ($this->_association->getCardinality() != 'many-to-many') {
            $database = Art_Database::getInstance();
            $where['key'][] = $database->quoteIdentifier($this->_associatedObject->_association->getLinkedObject()->getClass()) . '=' . $database->quote($this->_associatedObject->_association->getLinkedObject()->getId());
            $where['key'][] = $database->quoteIdentifier($this->_associatedObject->getClass()) . '=' . $database->quote($this->_associatedObject->getId());
        }
        return $where;
    }

    public function toArray() {
        $ret = parent::toArray();
        $ret['from'] = $this->_associatedObject->getClass() . '[' . $this->_associatedObject->getId() . ']';
        return $ret;
    }

    public function save(Art_Work_Unit $unitOfWork=null) {
        Art_Object::save($unitOfWork);
    }

    public function delete(Art_Work_Unit $unitOfWork=null) {
        Art_Object::delete($unitOfWork);
    }
}
?>
