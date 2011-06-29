<?php
class Art_Object_Associated extends Art_Object {
    protected $_association = NULL;
    protected $_specialSaveObject = NULL;
    protected $_associationClassObject = NULL;
    protected $_toBeTransformed = false;
    protected $_specialIsAssociationClass = false;

    public static function transform(Art_Object_Collection_Association $association, Art_Object $object) {
        $class = $association->getClass();
        if ($object->getClass() != $class)
            throw new Art_Object_Associated_Exception('object must be of class ' . $class);
        $new = new Art_Object_Associated($class, $association);
        $params = get_object_vars($object);
        foreach ($params as $var => $value) {
                $new->$var = $object->$var;
        }
        $new->_instanceId=self::$_instanceIdCount++;
        if ($object->hasParent() && isset($object->_parent))
            $object->getParent()->setChild($new);
        return $new;
    }

    public function isToBeTransformed(){
        return $this->_toBeTransformed;
    }

    public function setToBeTransformed($bool=true) {
        $this->_toBeTransformed = $bool;
    }

    public function __set($attributeName, $value) {
        parent::__set($attributeName, $value);
        if ($attributeName == 'id' && $this->_toBeTransformed) {
            $this->_association->notifyChangeInAssociationClass($value, $this);
        }
    }

    public function randomize() {
        parent::randomize();
        if ($this->hasAssociationClass())
            $this->getAssociationClass()->randomize();
    }

    public function __construct($className, Art_Object_Collection_Association $association) {
        $this->_association = $association;
        if (parent::$_writeMode && !$className)
            $className = Art_Object_Association::NO_CLASS;
        parent::__construct($className);
    }

    protected function _checkIfSameObjectExistsInDatabaseWhere() {
        $where = parent::_checkIfSameObjectExistsInDatabaseWhere();
        $database = Art_Database::getInstance();
        if ($this->_association instanceof Art_Object_Collection_Composition && !$this->_association->hasAssociationClass())
            $where['composition-key'][] = $database->quoteIdentifier($this->_association->getLinkedObject()->getClass()) . '=' . $database->quote($this->_association->getLinkedObject()->getId());
        return $where;
    }

    protected function _getAlias() {
        return $this->_association->getLinkedObject()->_getAlias() . '_' . $this->_class;
    }

    protected function _getAttributesForForm(Art_Data_Form $form) {
        parent::_getAttributesForForm($form);
        if ($this->hasAssociationClass() && !$this->getAssociationClass() instanceof Art_Object_Collection_Association_Class)
            $this->getAssociationClass()->_getAttributesForForm($form);
        return $form;
    }

    public function setAssociationClassObject($object) {
        if (parent::$_writeMode)
            $this->_associationClassObject = Art_Object_Association::transform($this, $object); // $object instanceof Art_Object?Art_Object_Association::transform($this, $object):  Art_Object_Collection_Association_Class::transform($object,$this);
    }

    public function getAssociationObject() {
        return $this->_association;
    }

    public function __get($attributeName) {
        if ($attributeName == 'association' && $this->hasAssociationClass()) {
            return $this->getAssociationClass();
        }else
            return parent::__get($attributeName);
    }

    public function hasAssociationClass() {
        return $this->_association->hasAssociationClass();
    }

    public function getAssociationClass() {
        if (!$this->_associationClassObject) {
            if ($this->_association->getCardinality() == 'many-to-many')
                $this->_associationClassObject = new Art_Object_Collection_Association_Class($this, $this->_association->getDefinition());
            else
                $this->_associationClassObject = new Art_Object_Association($this);
        }
        return $this->_associationClassObject;
    }

    public function getAssociationClassName() {
        return $this->_association->getAssociationClassName();
    }

    public function toArray() {
        $ret = parent::toArray();
        $ret['to'] = $this->_association->getLinkedObject()->getClass() . '[' . $this->_association->getLinkedObject()->getId() . '] ' . $this->_association->getLinkedObject()->_instanceId;
        if ($this->hasAssociationClass() && $this->_associationClassObject)
            $ret['associationClass'] = $this->_associationClassObject->toArray();
        return $ret;
    }

    public function specialIsAssociationClass($bool){
        $this->_specialIsAssociationClass=$bool;
    }

    protected function _isModifiedEnoughToBeSaved(){
        return parent::_isModifiedEnoughToBeSaved();
    }

    public function save(Art_Work_Unit $unitOfWork=null) {
        $isPersisted = $this->_isPersisted;
        if (!isset($this->_specialSaveObject))
            parent::save($unitOfWork);
        else {
            $this->_id = $this->_specialSaveObject;
            $this->_isPersisted = true;
        }
        if ($this->_association instanceof Art_Object_Collection_Composition) {
            // ref in association class
            if ($this->_association->hasAssociationClass()) {
                $this->getAssociationClass()->save($unitOfWork);
                // ref in composed table
            }
        } else {
            if ($this->_association->getCardinality() == 'one-to-one' && !$this->_association->hasAssociationClass()) {
                $unitOfWork->update($this->_association->getLinkedObject()->getTableName(), array($this->_association->getName() => $this->_id), 'id=' . $this->_association->getLinkedObject()->getId());
            } else{
                if ($this->_association->hasAssociationClass()) {
                    $this->getAssociationClass()->save($unitOfWork);
                } else {
                    if (!$isPersisted || $this->_specialIsAssociationClass)
                        $unitOfWork->insert($this->_association->getTableName(), array('id' => Art_Object::_getOID(), $this->_class => $this->_id, $this->_association->getLinkedObject()->getClass() => $this->_association->getLinkedObject()->getId()));
                }
            }
        }
    }

    public function setSaveId($id) {
        if (parent::$_writeMode)
            $this->_specialSaveObject = $id;
    }

    public function delete(Art_Work_Unit $unitOfWork=null) {
        if ($this->_association instanceof Art_Object_Collection_Composition) {
            // ref in association class
            if ($this->_association->hasAssociationClass()) {
                $this->getAssociationClass()->delete($unitOfWork);
                // ref in composed table
            }
        } else {
            if ($this->_association->getCardinality() == 'one-to-one' && !$this->_association->hasAssociationClass()) {
                $unitOfWork->update($this->_association->getLinkedObject()->getTableName(), array($this->_class => new Art_Database_Expression('NULL')), $this->_class . '=' . $this->_id);
            } else
            if (isset($this->_associationClassObject))
                $this->_associationClassObject->delete($unitOfWork);
        }
    }
}
?>
