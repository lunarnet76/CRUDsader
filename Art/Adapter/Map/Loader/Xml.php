<?php
/**
 * LICENSE: see Art/license.txt
 *
 * @author      Jean-Baptiste Verrey<jeanbaptiste.verrey@gmail.com>
 * @copyright   2011 Jean-Baptiste Verrey
 * @license     http://www.Art.com/license/1.txt
 * @version     $Id$
 * @link        http://www.Art.com/manual/
 * @since       1.0
 */
namespace Art\Adapter\Map\Loader {
    /**
     * load the mapping schema from a XML file
     * @abstract
     * @package    Art\Adapter\Map
     */
    class Xml extends \Art\Adapter\Map\Loader {
        protected $_file;
        protected $_dom;

        /**
         * @param Block $configuration
         */
        public function init() {
            $this->_file = $this->_configuration->file;
            if (!file_exists($this->_file))
                throw new LoaderException('XML Map File "' . $this->_file . '" does not exist');
            $this->_dom = simplexml_load_file($this->_file);
            if (!$this->_dom)
                throw new LoaderException('XML Map File "' . $this->_file . '" could not be loaded');
        }

        /**
         * @todo to finish
         * @return bool
         */
        public function validate() {
            $library = new \DOMDocument("1.0");
            $library->validateOnParse = true;
            libxml_clear_errors();
            if (!$library->load($this->_file))
                return libxml_get_errors();
            if (!$library->validate()) {
                return false;
            }
            $ret = array('attributeTypes' => array(), 'classes' => array());
            // attributeTypes
            $attributeTypes = $this->_dom->attributeTypes->attributeType;
            foreach ($attributeTypes as $attributeType) {
                $alias = (string) $attributeType['alias'];
                $ret['attributeTypes'][$alias] = array(
                );
            }
            // classes
            $classes = $this->_dom->classes->class;
            foreach ($classes as $class) {
                $name = (string) $class['name'];
                // definition
                $ret['classes'][$name] = array(
                    'definition' => array(
                        'databaseTable' => isset($class['databaseTable']) ? (string) $class['databaseTable'] : $name,
                        'association' => isset($class['association']) ? (bool) $class['association'] : false,
                        'identity' => isset($class['identity']) ? explode(',', (string) $class['identity']) : false
                    ),
                    'inherit' => false,
                    'attributes' => array(),
                    'associations' => array()
                );
                // inheritance
                $parent = false;
                if (isset($class->inherit[0])) {
                    $parent = (string) $class->inherit[0]['from'];
                    if (!isset($ret['classes'][$parent]))
                        throw new LoaderException('Class "' . $parent . '" must be defined before one class can inherit from it');
                }
                // attributes
                $attributes = $class->attribute;
                foreach ($attributes as $attribute) {
                    $attributeName = (string) $attribute['name'];
                    if (isset($attribute['type']) && !isset($ret['attributeTypes'][(string) $attribute['type']]))
                        throw new LoaderException('Attribute "' . $attributeName . '" type "' . $attribute['type'] . '" is not a valid alias of AttributeType');
                }
            }
            return true;
        }

        /**
         * return the mapping schema as an array
         * @param \Art\Block $defaults
         * @return array 
         */
        public function getSchema(\Art\Block $defaults=null) {
            $ret = array('attributeTypes' => array(), 'classes' => array());
            // attributeTypes
            $attributeTypes = $this->_dom->attributeTypes->attributeType;
            foreach ($attributeTypes as $attributeType) {
                $alias = (string) $attributeType['alias'];
                $ret['attributeTypes'][$alias] = array(
                    'length' => (int) $attributeType['length'],
                    'class' => isset($attributeType['class']) ? (string) $attributeType['class'] : $defaults->attributeType->class,
                    'databaseType' => isset($attributeType['databaseType']) ? (string) $attributeType['databaseType'] : $defaults->attributeType->databaseType,
                    'options' => isset($attributeType['options']) ? json_decode(str_replace('\'', '"', (string) $attributeType['options'])) : $defaults->attributeType->options,
                );
            }
            // classes
            $classes = $this->_dom->classes->class;
            foreach ($classes as $class) {
                $name = (string) $class['name'];
                // definition
                $ret['classes'][$name] = array(
                    'definition' => array(
                        'databaseTable' => isset($class['databaseTable']) ? (string) $class['databaseTable'] : $name,
                        'association' => isset($class['association']) ? (bool) $class['association'] : false,
                        'identity' => isset($class['identity']) ? explode(',', (string) $class['identity']) : array()
                    ),
                    'inherit' => false,
                    'attributes' => array(),
                    'associations' => array()
                );
                // inheritance
                $parent = false;
                if (isset($class->inherit[0])) {
                    $parent = (string) $class->inherit[0]['from'];
                    $ret['classes']['inherit'] = array(
                        'from' => $parent,
                        'implementation' => isset($class->inherit[0]['implementation']) ? (string) $class->inherit[0]['implementation'] : $defaults->inheritance
                    );
                    $ret['classes'][$parent]['attributes']['polymorphism'] = array(
                        'databaseField' => $defaults->attributeType->polymorphism->databaseField,
                        'databaseType' => $defaults->attributeType->polymorphism->databaseType,
                        'type' => $defaults->attributeType->polymorphism->type,
                        'length' => $defaults->attributeType->polymorphism->length
                    );
                }
                // attributes
                $attributes = $class->attribute;
                foreach ($attributes as $attribute) {
                    $attributeName = (string) $attribute['name'];
                    $ret['classes'][$name]['attributes'][$attributeName] = array(
                        'mandatory' => isset($attribute['mandatory']) ? (string) $attribute['mandatory'] : false,
                        'default' => isset($attribute['default']) ? (string) $attribute['default'] : false,
                        'databaseField' => isset($attribute['databaseField']) ? (string) $attribute['databaseField'] : $attributeName,
                        'type' => isset($attribute['type']) ? (string) $attribute['type'] : $defaults->attribute->type
                    );
                }
                // identity
                foreach ($ret['classes'][$name]['definition']['identity'] as $attributeName) {
                    $ret['classes'][$name]['attributes'][$attributeName]['mandatory'] = true;
                }
                // associations
                $associations = $class->associated;
                foreach ($associations as $association) {
                    $associationName = isset($association['name']) ? (string) $association['name'] : (string) $association['to'];
                    $ret['classes'][$name]['associations'][$associationName] = array(
                        'to' => (string) $association['to'],
                        'name' => $associationName,
                        'class' => isset($association['class']) ? (string) $association['class'] : false,
                        'cardinality' => isset($association['cardinality']) ? (string) $association['cardinality'] : $defaults->associations->cardinality,
                        'composition' => isset($association['composition']) ? (bool) $association['composition'] : false,
                        'min' => isset($association['min']) ? (int) $association['min'] : $defaults->associations->min,
                        'max' => isset($association['max']) ? (int) $association['max'] : $defaults->associations->max,
                        'reference' => isset($association['reference']) ? (string) $association['reference'] : false,
                        'databaseTable' => isset($association['databaseTable']) ? (string) $association['databaseTable'] : $this->_getDatabaseAssociationTable(isset($association['name']) ? (string) $association['name'] :false,(string) $association['to'],$name)
                    );
                    if ($ret['classes'][$name]['associations'][$associationName]['cardinality'] == 'one-to-one') {
                        if ($ret['classes'][$name]['associations'][$associationName]['class'])
                            $scenario = 1;
                        else
                            $scenario=$ret['classes'][$name]['associations'][$associationName]['reference'] == 'external' ? 2 : 3;
                    }else if ($ret['classes'][$name]['associations'][$associationName]['cardinality']== 'one-to-many') {
                        $scenario = $ret['classes'][$name]['associations'][$associationName]['class'] ? 4 : 5;
                    } else {
                        $scenario = $ret['classes'][$name]['associations'][$associationName]['class'] ? 6 : 7;
                    }
                    $ret['classes'][$name]['associations'][$associationName]['scenario'] = $scenario;
                }
            }
            return $ret;
        }
        
        protected function _getDatabaseAssociationTable($associationName,$classTo,$classFrom){
            return $associationName?:($classFrom > $classTo ? $classTo . '2' . $classFrom : $classFrom . '2' . $classTo);
        }
    }
    class LoaderException extends \Art\Exception {
        
    }
}