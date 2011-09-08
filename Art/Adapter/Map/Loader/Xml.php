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
            /*
             * - ref internal must be max=1
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
                        'identity' => isset($class['identity']) ? explode(',', (string) $class['identity']) : array(),
                        'databaseIdField' => isset($class['databaseIdField']) ? (string) $class['databaseIdField'] : $defaults->idField,
                        'attributeCount' => array('id' => false)
                    ),
                    'inherit' => false,
                    'attributes' => array(),
                    'associations' => array()
                );
                // inheritance
                $parent = false;
                if (isset($class['inherit'])) {
                    $parent = (string)$class['inherit'];
                    $ret['classes'][$name]['inherit'] = $parent;
                    $ret['classes'][$name]['attributes']['polymorphism'] = array(
                        'databaseField' => $defaults->attributeType->polymorphism->databaseField,
                        'databaseType' => $defaults->attributeType->polymorphism->databaseType,
                        'type' => $defaults->attributeType->polymorphism->type,
                        'length' => $defaults->attributeType->polymorphism->length
                    );
                     $ret['classes'][$name]['definition']['attributeCount']['polymorphism']=true;
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
                    $ret['classes'][$name]['definition']['attributeCount'][$attributeName] = false;
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
                    $to = (string) $association['to'];
                    $ret['classes'][$name]['associations'][$associationName] = array(
                        'to' => $to,
                        'reference' => $ref,
                        'name' => isset($association['name']) ? (string) $association['name'] : false,
                        'min' => isset($association['min']) ? (int) $association['min'] : $defaults->associations->min,
                        'max' => isset($association['max']) ? (int) $association['max'] : $defaults->associations->max,
                        'composition' => isset($association['composition']) ? (bool) $association['composition'] : false,
                        'databaseTable' => isset($association['databaseTable']) ? (string) $association['databaseTable'] : \Art\Map::getDatabaseAssociationTable(isset($association['name']) ? (string) $association['name'] : false, $to, $name),
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