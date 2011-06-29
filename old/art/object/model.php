<?php
class Art_Object_Model {
    protected $_object;
    protected $_attributes = array();
    protected $_attributesIsDefined = array();
    protected $_attributesDefaults = array();
    protected $_attributesDefinitions = array();
    protected $_isModified = false;

    public function __construct(Art_Object $object, array $attributesDefinitions) {
        $this->_object = $object;
        $this->_attributesDefinitions = $attributesDefinitions;
    }

    public static function transform(Art_Object_Model $model, Art_Object $object) {
        $model->_object = $object;
    }

    protected function _($attributeName) {
        return $this->getData($attributeName)->getValueFromDatabase();
    }

    public function getDefinition() {
        return $this->_attributesDefinitions;
    }

    public function isEmpty() {
        foreach ($this->_attributes as $a)
            if (!$a->isEmpty())
                return false;
        return true;
    }

    public function isModified() {
        return $this->_isModified;
    }

    public function equals(Art_Object_Model $model) {
        if ($this->isEmpty() && $model->isEmpty()
        )
            return true;
        $ckChecked = $kChecked = false;
        $identical = array('composition-key' => true, 'key' => true);
        foreach ($this->_attributesDefinitions as $k => $v) {
            if (($v['composition-key'] || $v['key'])) {
                if ($v['composition-key'])
                    $ckChecked = true;
                if ($v['key'])
                    $kChecked = true;
                if (isset($this->_attributes[$k]) && isset($model->_attributes[$k]) && $this->_attributes[$k]->getValue() != $model->_attributes[$k]->getValue()) {
                    if ($v['composition-key'])
                        $identical['composition-key'] = false;
                    if ($v['key'])
                        $identical['key'] = false;
                }
            }
        }

        $ret = false;
        if ($identical['composition-key'] && $ckChecked)
            $ret = 'composition-key';
        if ($identical['key'] && $kChecked)
            $ret = 'key';
        return $ret ? $ret : false;
    }

    public function randomize() {
        foreach ($this->_attributesDefinitions as $k => $v)
            if ($k != 'polymorphism')
                $this->getData($k)->generate();
        $this->_isModified = true;
    }

    /**
     * @param string $attributeName
     * @return bool
     */
    public function hasAttribute($attributeName) {
        return isset($this->_attributesDefinitions[$attributeName]);
    }

    /**
     * @param string $attributeName
     * @return mix
     */
    public function getAttribute($attributeName, $fromDatabase=true) {
        return isset($this->_attributesIsDefined[$attributeName]) ? ($fromDatabase ? $this->_attributes[$attributeName]->getValueFromDatabase() : $this->_attributes[$attributeName]->getValueForDatabase()) : (isset($this->_attributesDefaults[$attributeName]) ? $this->_attributesDefaults[$attributeName] : new Art_Database_Expression('NULL'));
    }

    public function isAttributeDefined($attributeName) {
        return isset($this->_attributesIsDefined[$attributeName]);
    }

    /**
     * @param string $attributeName
     * @param mix $value
     */
    public function setAttribute($attributeName, $value, $forDatabase=true) {
        if (!$forDatabase)
            return $this->getData($attributeName)->setValueFromDatabase($value);
        $data = $this->getData($attributeName);
        if ($forDatabase)
            $data->setValueForDatabase($value);
        else
            $data->setValueFromDatabase($value);
        if ($data->error())
            throw new Art_Object_Model_Exception('Attribute "' . $attributeName . '" cannot take value "' . $value . '", error : ' . $data->getError());
    }

    public function getData($attributeName, $isDefined=true) {
        if (!isset($this->_attributes[$attributeName])) {
            $infos = $this->_attributesDefinitions[$attributeName];
            $this->_attributes[$attributeName] = new Art_Data($infos['data'], $infos['data-options'], $infos['data-specify'], $this, $attributeName);
            $this->_attributes[$attributeName]->setRequired($infos['mandatory']);
            if ($isDefined)
                $this->_attributesIsDefined[$attributeName] = true;
        }
        return $this->_attributes[$attributeName];
    }

    public function notify($var, $isEmpty, $isRequired) {
        $this->_isModified = $this->_isModified ? true : !$isEmpty;
    }

    public function toArray() {
        $ret = array();
        foreach ($this->_attributes as $attributeName => $value)
            $ret[$attributeName] = array('value' => $value->getValueFromDatabase(), 'mandatory' => $value->isRequired());
        return $ret;
    }

    public function __toString() {
        return current($this->_attributes) ? current($this->_attributes)->__toString() : ($this->_object->hasParent() ? $this->_object->getParent()->getModel()->__toString() : 'void');
    }
}
?>