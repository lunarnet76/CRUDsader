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
                    'class' => '\\Art\\Object\\Attribute\\Wrapper\\'.(isset($attributeType['class']) ? ucfirst((string) $attributeType['class']) : $defaults->attributeType->class),
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
                        'identity' => isset($class['identity']) ? explode(',', (string) $class['identity']) : array(),
                        'databaseIdField' => isset($class['databaseIdField']) ? (string) $class['databaseIdField'] : $defaults->idField,
                        'attributeCount' => array('id' => false)
                    ),
                    'inherit' => false,
                    'attributes' => array(),
                    'attributesReversed' => array(),
                    'associations' => array()
                );
                // inheritance
                $parent = false;
                if (isset($class['inherit'])) {
                    $ret['classes'][$name]['inherit'] = (string)$class['inherit'];
                }
                // attributes
                $attributes = $class->attribute;
                foreach ($attributes as $attribute) {
                    $attributeName = (string) $attribute['name'];
                    $ret['classes'][$name]['attributes'][$attributeName] = array(
                        'required' => isset($attribute['required']) ? ((string) $attribute['required'])=='true' : false,
                        'default' => isset($attribute['default']) ? (string) $attribute['default'] : false,
                        'databaseField' => isset($attribute['databaseField']) ? (string) $attribute['databaseField'] : $attributeName,
                        'type' => isset($attribute['type']) ? (string) $attribute['type'] : $defaults->attribute->type,
                        'searchable' => isset($attribute['searchable']) ? (string) $attribute['searchable'] : $defaults->attribute->searchable,
                        'calculated' => isset($attribute['calculated']) ? (string) $attribute['calculated'] : false
                    );
                    $ret['classes'][$name]['definition']['attributeCount'][$attributeName] = false;
                    $ret['classes'][$name]['attributesReversed'][$ret['classes'][$name]['attributes'][$attributeName]['databaseField']] = $attributeName;
                }
                // associations
                $associations = $class->associated;
                foreach ($associations as $association) {
                    $associationName = isset($association['name']) ? (string) $association['name'] : (string) $association['to'];
                    $ref = isset($association['reference']) ? (string) $association['reference'] : $defaults->associations->reference;
                    $to = (string) $association['to'];
                    $ret['classes'][$name]['associations'][$associationName] = array(
                        'to' => $to,
                        'reference' => $ref,
                        'name' => isset($association['name']) ? (string) $association['name'] : false,
                        'min' => isset($association['min']) ? (int) $association['min'] : $defaults->associations->min,
                        'max' => isset($association['max']) ? (int) $association['max'] : $defaults->associations->max,
                        'composition' => isset($association['composition']) ? ((string) $association['composition'])=='true' : false,
                        'databaseTable' => isset($association['databaseTable']) ? (string) $association['databaseTable'] : \Art\Map::getDatabaseAssociationTable(isset($association['name']) ? (string) $association['name'] : false, $to, $name),
                        'databaseIdField' => isset($association['databaseIdField']) ? (string) $association['databaseIdField'] : $defaults->associations->databaseIdField,
                        'internalField' => isset($association['internalField']) ? (string) $association['internalField'] : $name,
                        'externalField' => isset($association['externalField']) ? (string) $association['externalField'] : $to
                    );
                }
            }
            foreach ($ret['classes'] as $name => $infos) {
                foreach ($infos['associations'] as $associationName => $association) {
                    switch ($association['reference']) {
                        case 'internal':
                            $ret['classes'][$name]['definition']['attributeCount'][$association['internalField']] = true;
                            break;
                        case 'external':
                            $ret['classes'][$association['to']]['definition']['attributeCount'][$association['externalField']] = true;
                            break;
                    }
                }
            }
            return $ret;
        }
    }
    class LoaderException extends \Art\Exception {
        
    }
}