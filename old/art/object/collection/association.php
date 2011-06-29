<?php
class Art_Object_Collection_Association extends Art_Object_Collection {
    protected $_definition;
    protected $_linkedToObject;
    protected $_associationClassObject = false;
    protected $_associationClassObjectLast = array();
    protected $_formData = array();
    protected $_writeModelastAssociatedObjectId = false;
    protected static $_writeMode = false;

    public static function setWriteMode($bool) {
        self::$_writeMode = $bool;
    }

    public function getDefinition() {
        return $this->_definition;
    }

    public function notifyChangeInAssociationClass($id, $caller) {
        if ($this->_associationClassObject && $id != $this->_writeModelastAssociatedObjectId) {
            $caller->setId($this->_writeModelastAssociatedObjectId ? $this->_writeModelastAssociatedObjectId : $id);
            $this->_associationClassObject = new Art_Object_Associated($this->_definition['class'], $this);
            $this->_associationClassObject->setToBeTransformed();
            $this->_associationClassObject->setId($id);
        }
        $this->_writeModelastAssociatedObjectId = $id;
    }

    public function __construct(Art_Object $object, $definition) {
        $this->_definition = $definition;
        $this->_linkedToObject = $object;
        parent::__construct($this->_definition['to']);
    }

    protected function _randomizeGetMax($maxObject) {
        return rand(0, $this->_definition['cardinality'] == 'one-to-one' ? 1 : 3);
    }

    public function getName() {
        return $this->_definition['name'];
    }

    public function getForm($baseClassName=false, $number=false) {
        $baseClassName.='_' . $this->_class;
        // form
        $form = new Art_Data_Form($baseClassName . $number);
        $form->setView('association');
        $max = $this->_definition['cardinality'] == 'one-to-one' ? 1 : 3;
        $objects = array();
        $this->rewind();
        for ($i = 0; $i < $max; $i++) {
            if ($this->valid()) {
                $this->_formData[$i] = new Art_Data('object', array('class' => $this->_class, 'default' => $this->_objectsIds[$i]), array(), $this, $i);
            } else {
                $this->_formData[$i] = new Art_Data('object', array('class' => $this->_class), array(), $this, $i);
            }
            $form->add($this->_formData[$i], $baseClassName . '_object', $this->_definition['mandatory']);
            $this->next();
        }
        $this->rewind();
        return $form;
    }

    public function notify($index, $isEmpty) {
        if ($isEmpty)
            return;
        Art_Object::setWriteMode(true);
        if (isset($this->_objects[$index]))
            $this->_objects[$index]->setSaveId($this->_formData[$index]->getValue());
        else {
            $this->getCurrentObject()->setSaveId($this->_formData[$index]->getValue());
            $this->next();
        }
        Art_Object::setWriteMode(false);
    }

    public function getCardinality() {
        return $this->_definition['cardinality'];
    }

    public function getLinkedObject() {
        return $this->_linkedToObject;
    }

    public function isMandatory() {
        return $this->_definition['mandatory'];
    }

    public function getTableName() {
        return Art_Mapper::getInstance()->getAssociationTableName($this->_definition['from'], $this->_definition['to'], $this->_definition['name']);
    }

    public function __get($attributeName) {
        if (self::$_writeMode) {
            if ($attributeName == 'association' && $this->hasAssociationClass()) {
                if (!$this->_associationClassObject) {
                    $this->_associationClassObject = new Art_Object_Associated($this->_definition['class'], $this);
                    $this->_associationClassObject->setToBeTransformed();
                }
                return $this->_associationClassObject;
            } else {
                // empty associations
                if (!$this->_current)
                    return $this->_instanceObject()->$attributeName;
                else
                    return $this->_objects[$this->_current]->$attributeName;
            }
        } else {
            if ($attributeName == 'association' && $this->hasAssociationClass())
                return $this->getCurrentObject()->getAssociationClass();
            else
                return parent::__get($attributeName);
        }
    }

    protected function _instanceObject() {
        return new Art_Object_Associated($this->_class, $this);
    }

    public function __set($attributeName, $value) {
        if (self::$_writeMode) {
            if ($attributeName == 'id') {
                if (empty($value)) {
                    $this->_current = false;
                    return;
                }
                if (!isset($this->_objects[$value])) {
                    $this->_objects[$value] = $this->_instanceObject();
                    $this->_objects[$value]->id = $value;
                    $this->_objectsIds[] = $value;
                }
                $this->_current = $value;
                if ($this->_associationClassObject) {
                    if ($this->getCardinality() == 'many-to-many') {
                        if (!isset($this->_objects[$this->_current]->association[$this->_associationClassObject->getId()]))
                            $this->_objects[$this->_current]->association[$this->_associationClassObject->getId()] = $this->_associationClassObject;
                        $this->_associationClassObject = $this->_objects[$this->_current]->association[$this->_associationClassObject->getId()];
                    } else {
                        $this->_objects[$this->_current]->setAssociationClassObject($this->_associationClassObject);
                        $this->_associationClassObject = false;
                    }
                }
            } else {
                if (!$this->_current)
                    return;
                $this->_objects[$this->_current]->$attributeName = $value;
            }
        } else {
            parent::__set($attributeName, $value);
        }
    }

    public function hasAssociationClass() {
        return $this->_definition['class'] || self::$_writeMode;
    }

    public function toArray() {
        $ret = parent::toArray();
        $ret['type'] = ($this->_definition['composition'] ? 'composition' : '') . ' ' . $this->_definition['cardinality'] . ' ' . ($this->_definition['class'] ? 'AC' : '');
        return $ret;
    }

    public function getAssociationClassName() {
        return $this->_definition['class'];
    }

    public function offsetSet($index, $value) {
        if ($this->_definition['cardinality'] == 'one-to-one' && count($this->_objects))
            throw new Art_Object_Collection_Exception('association can have only one object');
        parent::offsetSet($index, $value);
    }

    protected function _transformObject(Art_Object $value) {
        return Art_Object_Associated::transform($this, $value);
    }
}
?>
