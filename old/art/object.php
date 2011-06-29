<?php

class Art_Object {

    protected $_isProxy=false;
    protected $_instanceId = NULL;
    protected $_id = NULL;
    protected $_isPersisted = false;
    protected $_class = NULL;
    protected $_polymorphism = false;
    protected $_model = NULL;
    protected $_parent = NULL;
    protected $_associations = array();
    protected static $_writeMode = false;
    protected static $_instanceIdCount = 0;
    protected static $_lastObjectWithException = null;

    public static function setWriteMode($bool) {
        self::$_writeMode = $bool;
    }

    public static function getLastObjectWithException() {
        return self::$_lastObjectWithException;
    }

    // CONSTRUCTOR

    /**
     * @param string $className
     */
    public function __construct($className) {
        $mapper = Art_Mapper::getInstance();
        $this->_class = $className;
        if (!$mapper->classExists($this->_class))
            throw new Art_Object_Exception('class <b>' . $this->_class . '</b> does not exist');
        $this->_instanceId = ++self::$_instanceIdCount;
        $class = Art_Class::isLoadable('Model_' . ucfirst($className)) ? 'Model_' . ucfirst($className) : 'Art_Object_Model';
        $this->_model = new $class($this, $mapper->classGetAttributes($this->_class));
        if (!$this->_model instanceof Art_Object_Model)
            throw new Art_Object_Exception('class <b>' . __CLASS__ . '</b> must inherit from Art_Object_Model');
    }

    public function isPersisted() {
        return $this->_isPersisted;
    }

    public function randomize() {
        $this->_model->randomize();
        if ($this->hasParent())
            $this->getParent()->randomize();
        $associations = Art_Mapper::getInstance()->classGetAssociations($this->_class);
        foreach ($associations as $name => $infos)
            $this->getAssociation($name)->randomize();
    }

    public function getModel() {
        return $this->_model;
    }

    public function offsetGet($index) {
        return $this->__get($index);
    }

    // ACCESSORS
    public function __get($attributeName) {
        switch (true) {
            case $attributeName == 'polymorphism':
                return $this->_polymorphism;
                break;
            case $this->_model->hasAttribute($attributeName):
                return $this->_model->getAttribute($attributeName);
            case ($this->hasParent() || self::$_writeMode) && $attributeName == 'parent':
            case ($this->hasParent() || self::$_writeMode) && Art_Mapper::getInstance()->classGetParentClass($this->_class) == $attributeName:
                return $this->getParent();
            case $this->hasAssociation($attributeName):
                return $this->getAssociation($attributeName);
        }
        if ($this->hasParent())
            return $this->getParent()->__get($attributeName);
        throw new Art_Object_Exception('attribute, parent or association "' . $attributeName . '" does not exist');
    }

    public function setId($id){
        if(self::$_writeMode){
            $this->_id=$id;
            $this->_isPersisted=true;
        }
    }

    public function populate($oql=false) {
        if ($oql !== false && !is_array($oql)) {
            $oql = Art_Mapper::getInstance()->getAllowedClassForPopulateNewObject($oql);
        }
        if ($this->hasParent())
            $this->getParent()->populate($oql);
        $associations = Art_Mapper::getInstance()->classGetAssociations($this->_class);
        foreach ($associations as $name => $infos)
            if ($oql === false || isset($oql[$infos['to']]))
                $this->getAssociation($name);
            // ??? uselesS? ?? 
           // else if(isset($this->_associations[$name]))
             //       unset($this->_associations[$name]);
    }

    public function __set($attributeName, $value) {
        if ($attributeName == 'id' && self::$_writeMode) {
            $this->_id = $value;
            $this->_isPersisted = true;
            return;
        }
        if ($attributeName == 'polymorphism')
            return $this->_polymorphism = $value;
        if ($this->_model->hasAttribute($attributeName))
            return $this->_model->setAttribute($attributeName, $value, !self::$_writeMode);
        if ($this->hasParent())
            return $this->getParent()->__set($attributeName, $value);
        throw new Art_Object_Exception('attribute "' . $attributeName . '" does not exist');
    }

    public function getClass() {
        return $this->_class;
    }

    public function hasId() {
        return isset($this->_id);
    }

    public function getId() {
        return $this->_id;
    }

    public function hasParent() {
        if($this->_isProxy)return false;
        return Art_Mapper::getInstance()->classHasParent($this->_class);
    }

    public function hasParentSet() {
        return isset($this->_parent);
    }

    public function getParent() {
        if (!isset($this->_parent)){
            $this->_parent = new Art_Object_Parent(Art_Mapper::getInstance()->classGetParentClass($this->_class), $this);
            if($this->_isProxy){
                $this->_parent->_id=$this->_id;
                $this->_parent->_isPersisted=true;
            }
        }
        return $this->_parent;
    }

    public function getTableName() {
        return Art_Mapper::getInstance()->classGetTable($this->_class);
    }

    public function hasAssociation($associationName) {
        return Art_Mapper::getInstance()->classHasAssociation($this->_class, $associationName);
    }

    public function getAssociation($associationName) {
        if (!isset($this->_associations[$associationName]))
            $this->_associations[$associationName] = Art_Object_Collection::instance($this, $associationName);
        return $this->_associations[$associationName];
    }

    public function checkHasAssociationSet($associationName) {
        if (!isset($this->_associations[$associationName]))
            throw new Art_Object_Exception('association "' . $associationName . '" has not been initizialed');
    }

    // METHODS
    public function getForm($baseClassName=false, $number=false, $isParent=false, $isRequired=false) {
        $required = $isParent || $baseClassName === false || $isRequired;
        if (!$isParent)
            $baseClassName.= ( $baseClassName ? '_' : '') . $this->_class;
        // form
        $form = new Art_Data_Form($baseClassName . $this->_id . $number);
        $form->setLabel($baseClassName);
        if ($required)
            $form->setRequired(true);
        // attributes
        $this->_getAttributesForForm($form);
        // parent
        if ($this->hasParent()) {
            // special
            if ($this->isEmpty()) {
                $form = $this->getParent()->getForm($this->_class, false, true);
                $form->useSubmitButton(true);
            }else
                $form->add($this->getParent()->getForm($baseClassName, false, true));
        }
        // associations
        foreach ($this->_associations as $name => $association) {
            if ($this instanceof Art_Object_Parent && $this->_child->hasAssociation($name)
                )continue;
            $subForm = $association->getForm($baseClassName);
            $subForm->setDomCss('first');
            $form->add($subForm, false, $association->isMandatory());
        }
        return $form;
    }

    protected function _getAttributesForForm(Art_Data_Form $form) {
        $attributes = Art_Mapper::getInstance()->classGetAttributes($this->_class);
        foreach ($attributes as $name => $infos) {
            if ($name == 'polymorphism'

                )continue;
            $data = $this->_model->getData($name);
            $form->add($data, $name, $infos['mandatory']);
            $data->setLabel($this->_class . '_' . $name);
        }
    }

    public function __call($name, $arguments) {
        if (method_exists($this->_model, $name))
            return call_user_func_array(array($this->_model, $name), $arguments);

        if ($this->hasParent() && $this->hasParentSet())
            return call_user_func_array(array($this->getParent(), $name), $arguments);
        throw new Art_Object_Exception('method "' . $name . '" does not exist in model');
    }

    public function delete(Art_Work_Unit $unitOfWork=null) {
        if (!isset($unitOfWork)) {
            $baseClass = true;
            $unitOfWork = new Art_Work_Unit($this->_class);
        }
        // base table
        if ($this->hasId())
            $unitOfWork->delete($this->getTableName(), 'id=' . $this->_id);
        if ($this->hasParent())
            $this->getParent()->delete($unitOfWork);
        foreach ($this->_associations as $name => $association)
            $association->delete($unitOfWork);
        if (isset($baseClass)) {
            $unitOfWork->execute();
        }
    }

    protected function _checkIfSameObjectExistsInDatabase() {
        $database = Art_Database::getInstance();
        $where = $this->_checkIfSameObjectExistsInDatabaseWhere();
        if (count($where)) {
            $sql = new Art_Database_Select($this->getTableName(), 'o', array('id'));
            $sqlWhereCK = count($where['composition-key']) ? implode(' AND ', $where['composition-key']) : false;
            $sqlWhereK = count($where['key']) ? implode(' AND ', $where['key']) : false;
            if ($sqlWhereCK && $sqlWhereK)
                $sql->where('(' . $sqlWhereCK . ') OR (' . $sqlWhereK . ')');
            else if ($sqlWhereCK)
                $sql->where($sqlWhereCK);
            else if ($sqlWhereK)
                $sql->where($sqlWhereK);
            else
                return false; //throw new Art_Object_Exception('no keys '.$this->_class);
             
         $query = $database->query($sql, 'select');
            
            if ($query->count()) {
                $query->rewind();
                $row = $query->current();
                if (!$this->_isPersisted || $row['id'] != $this->_id)
                    return $this->_checkIfSameObjectExistsInDatabaseException($row);
            }
        }
        return false;
    }

    protected function _checkIfSameObjectExistsInDatabaseException($row) {
        self::$_lastObjectWithException = $this;
        throw new Art_Object_Exception($this->_class . '_already_exists');
    }

    public function getInstanceId(){
        return $this->_instanceId;
    }

    public function equals(Art_Object $object) {
        $ret = $this->_model->equals($object->_model);
        if ($ret === true && $this->hasParentSet() && $object->hasParentSet())
            $ret = $this->_parent->equals($object->_parent);
        return $ret;
    }

    protected function _getAlias() {
        return $this->_class;
    }

    protected function _checkIfSameObjectExistsInDatabaseWhere() {
        $database = Art_Database::getInstance();
        $where = array('composition-key' => array(), 'key' => array());
        $attributes = Art_Mapper::getInstance()->classGetAttributes($this->_class);
        foreach ($attributes as $attributeName => $attributeInfos) {
            if ($attributeInfos['composition-key'])
                $where['composition-key'][] = $database->quoteIdentifier($attributeInfos['database-field']) . '=' . $database->quote($this->_model->getAttribute($attributeName, false));
            if ($attributeInfos['key'])
                $where['key'][] = $database->quoteIdentifier($attributeInfos['database-field']) . '=' . $database->quote($this->_model->getAttribute($attributeName, false));
        }
        return $where;
    }

    public function getData($attributeName) {
        if ($this->_model->hasAttribute($attributeName)) {
            $clone = clone $this->_model->getData($attributeName, false);
            $clone->setRequired(false);
            return $clone;
        } else if ($this->hasParent())
            return $this->getParent()->getData($attributeName);
        throw new Art_Object_Exception('model does not have data for attribute "' . $attributeName . '"');
    }

    public function save(Art_Work_Unit $unitOfWork=null) {
        if (!isset($unitOfWork)) {
            $baseClass = true;
            $unitOfWork = new Art_Work_Unit($this->_class);
        }
        try {
            if ($this->_isModifiedEnoughToBeSaved()) {
                $check = $this->_checkIfSameObjectExistsInDatabase();
                if ($check) {
                    $this->_isPersisted = true;
                    $this->_id = $check;
                }
                $table = $this->getTableName();
                if (!isset($this->_id))
                    $this->_id = $this->_getOID();
                $params = $this->_getParamsForSave();
                if ($this->_isPersisted) {
                    if (count($params))
                        $unitOfWork->update($table, $params, 'id=' . $this->_id);
                }else {
                    if (!$this->_model->isEmpty() || $this instanceof Art_Object_Association || $this instanceof Art_Object_Parent || $this->hasParent()) {
                        $params['id'] = $this->_id;
                        $unitOfWork->insert($table, $params);
                        $this->_isPersisted = true;
                    }
                }
            }
            if ($this->hasParent() && isset($this->_parent))
                $this->getParent()->save($unitOfWork);
            foreach ($this->_associations as $name => $association) {
                $association->save($unitOfWork);
            }
            if (isset($baseClass)) {
                 //pre($unitOfWork);
                $unitOfWork->execute();
            }
        } catch (Art_Work_Unit_Exception $e) {
            if (!$this->_isPersisted)
                $this->_id = NULL;
            throw $e;
        }
    }

    public function isEmpty() {
        return $this->_model->isEmpty();
    }

    protected function _isModifiedEnoughToBeSaved() {
        return $this->_isProxy || $this->_model->isModified() || $this->_model->isEmpty();
    }

    protected function _getParamsForSave() {
        if($this->_isProxy)return array();
        $params = array();
        $allAttributes = Art_Mapper::getInstance()->classGetAttributes($this->_class);
        foreach ($allAttributes as $name => $infos) {
            if (($infos['key'] || $infos['composition-key'] || $infos['mandatory']) && !$this->_model->isAttributeDefined($name) && !($this instanceof Art_Object_Associated && $this->hasAssociationClass() && $this->getAssociationClass() instanceof Art_Object_Collection_Association_Class))
                throw new Art_Object_Exception('attribute "' . $this->_class . '.' . $name . '" (' . get_class($this) . ') must be defined before saving');
            if ($this->_model->isAttributeDefined($name))
                $params[$infos['database-field']] = $this->_model->getAttribute($name, false);
        }
        return $params;
    }

    protected function _getOID() {
        return Art_Adapter_Factory::getInstance('identifier')->getOid($this->_class);
    }

    public function getPolymorphism() {
        return $this->_polymorphism ? $this->_polymorphism : $this->_class;
    }

    // HELPER
    public function toArray() {
        $tmp2 = $tmp = array();
        foreach ($this->_associations as $name => $associations)
            $tmp2[$name] = $associations->toArray();
        $ret = array(
            'class' => $this->_class . ' (' . get_class($this) . ') '.($this->_isProxy?'proxy':''),
            'id' => $this->_id,
            'instanceId' => $this->_instanceId . ' (' . ($this->_isPersisted ? 'is Persisted' : 'is NOT Persisted') . ')'
        );
        $ret['attributes'] = $this->_model->toArray();
        if ($this->hasParent())
            $ret['parent'] = isset($this->_parent) ? $this->getParent()->toArray() : 'non initialised';
        if (count($tmp2))
            $ret['associations'] = $tmp2;
        return $ret;
    }

    public function toHTML($base=false, $prefix=false) {
        $html = '';
        if (!$base)
            $html.='<div class="object">';
        else
            $html.='<div class="subobject">';
        $base.= ( $base ? '_' : '') . $this->_class;
        if (!$this instanceof Art_Object_Associated && !$this instanceof Art_Object_Parent)
            $html.='<div class="title">' . Art_I18n::getInstance ()->get($prefix . $base) . '</div>';
        $attributes = Art_Mapper::getInstance()->classGetAttributes($this->_class);
        foreach ($attributes as $name => $infos)
            if ($name != 'polymorphism')
                $html.='<div class="row"><div class="label">' . Art_I18n::getInstance ()->get($prefix . $this->_class . '_' . $name) . '</div><div class="value">' . $this->$name . '</div></div>';
        if ($this->hasParent() && isset($this->_parent))
            $html.=$this->getParent()->toHTML($this->_class, $prefix);
        foreach ($this->_associations as $name => $infos) {
            $this->checkHasAssociationSet($name); // useless???
            $html.='<div class="title">' . Art_I18n::getInstance ()->get($prefix . $this->_class . '_' . $name) . '</div>';
            $collection = $this->getAssociation($name);
            $first = true;
            foreach ($collection as $object) {
                if ($first)
                    $first = false;
                else
                    $html.='<div class="row">&nbsp;</div>';
                $html.=$object->toHTML($base, $prefix);
                // if($object instanceof Art_Object_Associated && $object->hasAssociationClass())
                //       $html.=$object->getAssociationClass()->toHTML($base,$prefix);
            }
        }
        $html.='</div>';
        return $html;
    }

    public function __toString() {
        return $this->_model->__toString();
    }

}

?>