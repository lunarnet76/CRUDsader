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
         * @todo to finish, add check of unicity of usage of association classes
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
                        'identity' => isset($class['identity']) ? explode(',', (string) $class['identity']) : false,
                        'databaseIdField' => isset($class['databaseIdField']) ? (string) $class['databaseIdField'] : false
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
                    $ret['classes'][$name]['attributes'][$attributeName] = true;
                }
                // identity
                if ($ret['classes'][$name]['definition']['identity'])
                    foreach ($ret['classes'][$name]['definition']['identity'] as $attributeName) {
                        if (!isset($ret['classes'][$name]['attributes'][$attributeName]))
                            throw new LoaderException('Attribute "' . $attributeName . '" defined in the identity field does not exist');
                    }
            }
            /*
             * - ref internal must be max=1
             * - association class cannot have a databasetable
             */
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
                    'options' => isset($attributeType['options']) ? json_decode(str_replace('\'', '"', (string) $attributeType['options'])) : $defaults->attributeType->options->toArray(),
                );
            }
            $ret['attributeTypes']['default'] = $ret['attributeTypes'][$defaults->attribute->type];
            // classes
            $classes = $this->_dom->classes->class;
            foreach ($classes as $class) {
                $name = (string) $class['name'];
                // definition
                $ret['classes'][$name] = array(
                    'definition' => array(
                        'databaseTable' => isset($class['databaseTable']) ? (string) $class['databaseTable'] : $name,
                        'association' => isset($class['association']) ? (bool) $class['association'] : false,
                        'identity' => isset($class['identity']) ? explode(',', (string) $class['identity']) : array(),
                        'databaseIdField' => isset($class['databaseIdField']) ? (string) $class['databaseIdField'] : $defaults->idField
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
                        'type' => isset($attribute['type']) ? (string) $attribute['type'] : $defaults->attribute->type,
                        'searchable' => isset($attribute['type']) ? (string) $attribute['type'] : $defaults->attribute->searchable,
                        'calculated' => isset($attribute['type']) ? (string) $attribute['type'] : false
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
                    $ref = isset($association['reference']) ? (string) $association['reference'] : $defaults->associations->reference;
                    $ret['classes'][$name]['associations'][$associationName] = array(
                        'to' => (string) $association['to'],
                        'name' => isset($association['name'])?(string)$association['name']:false,
                        'composition' => isset($association['composition']) ? (bool) $association['composition'] : false,
                        'min' => isset($association['min']) ? (int) $association['min'] : $defaults->associations->min,
                        'max' => isset($association['max']) ? (int) $association['max'] : $defaults->associations->max,
                        'reference' => $ref,
                        'databaseTable' => isset($association['databaseTable']) ? (string) $association['databaseTable'] : \Art\Map::getDatabaseAssociationTable(isset($association['name']) ? (string) $association['name'] : false, (string) $association['to'], $name)
                    );
                    switch ($ref) {
                        case 'internal':
                        case 'external':
                            unset($ret['classes'][$name]['associations'][$associationName]['databaseTable']);
                            unset($ret['classes'][$name]['associations'][$associationName]['class']);
                            break;
                        default:
                            unset($ret['classes'][$name]['associations'][$associationName]['composition']);
                    }
                }
            }
            return $ret;
        }

    }

    class LoaderException extends \Art\Exception {
        
    }

}