<?php
/**
 * @author      Jean-Baptiste Verrey<jeanbaptiste.verrey@gmail.com>
 * @copyright   2011 Jean-Baptiste Verrey
 * @license     see license.txt
 * @since       0.1
 */
namespace CRUDsader\Map\Loader {
    /**
     * load the mapping schema from a XML file
     * @abstract
     * @package    CRUDsader\Map
     */
    class Xml extends \CRUDsader\Map\Loader {
        protected $_file;
        protected $_dom;
        
         /**
         * identify the class
         * @var string
         */
        protected $_classIndex = 'map.loader';

        
        public function __construct(){
            parent::__construct();
            $this->_file = $this->_configuration->file;
            if (!file_exists($this->_file))
                throw new LoaderException('XML Map File "' . $this->_file . '" does not exist');
            $this->_dom = simplexml_load_file($this->_file);
            if (!$this->_dom)
                throw new LoaderException('XML Map File "' . $this->_file . '" could not be loaded');
        }
        
        /**
         * return true if resource is validated or array of error otherwise
         * @abstract
         * @param \CRUDsader\Block $defaults
         * @return true|array array of errors
         */
        public function validate(\CRUDsader\Block $defaults=null) {
            return true;
        }

        /**
         * return the mapping schema as an array
         * @param \CRUDsader\Block $defaults
         * @return array 
         */
        public function getSchema(\CRUDsader\Block $defaults=null) {
            $ret = array('attributeTypes' => array(), 'classes' => array());
            // attributeTypes
            $attributeTypes = $this->_dom->attributeTypes->attributeType;
            foreach ($attributeTypes as $attributeType) {
                $alias = (string) $attributeType['alias'];
                $ret['attributeTypes'][$alias] = array(
                    'length' => (int) $attributeType['length'],
                    'class' => (isset($attributeType['class']) ? ucfirst((string) $attributeType['class']) : $defaults->attributeType->class),
                    'phpClass' => (isset($attributeType['phpClass']) ? ucfirst((string) $attributeType['phpClass']) : $defaults->attributeType->phpClass),
                    'databaseType' => isset($attributeType['databaseType']) ? (string) $attributeType['databaseType'] : $defaults->attributeType->databaseType,
                    'options' => isset($attributeType['options']) ? json_decode(str_replace('\'', '"', (string) $attributeType['options'])) : $defaults->attributeType->options->toArray(),
                );
                $ret['attributeTypes'][$alias]['options']['length'] = (int) $attributeType['length'];
            }
            $ret['attributeTypes']['default'] = $ret['attributeTypes']['default'];
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
                        'attributeCount' => array('id' => false),
                        'phpClass' => isset($class['phpClass']) ? (string) $class['phpClass'] : false,
                    ),
                    'inherit' => false,
                    'attributes' => array(),
                    'attributesReversed' => array(),
                    'associations' => array()
                );
                // inheritance
                $parent = false;
                if (isset($class['inherit'])) {
                    $ret['classes'][$name]['inherit'] = (string) $class['inherit'];
                }
                // attributes
                $attributes = $class->attribute;
                foreach ($attributes as $attribute) {
                    $attributeName = (string) $attribute['name'];
                    $ret['classes'][$name]['attributes'][$attributeName] = array(
                        'required' => isset($attribute['required']) ? ((string) $attribute['required']) == 'true' : $defaults->attribute->required,
                        'default' => isset($attribute['default']) ? (string) $attribute['default'] : null,
                        'databaseField' => isset($attribute['databaseField']) ? (string) $attribute['databaseField'] : $attributeName,
                        'type' => isset($attribute['type']) ? (string) $attribute['type'] : 'default',
                        'searchable' => isset($attribute['searchable']) ? (string) $attribute['searchable'] : $defaults->attribute->searchable,
                        'calculated' => isset($attribute['calculated']) ? (string) $attribute['calculated'] : false,
                        'input' => isset($attribute['input']) ? ((string) $attribute['input']) == 'true' : $defaults->attribute->input,
                        'html' => isset($attribute['html']) ? ((string) $attribute['html']) == 'true' : $defaults->attribute->html,
                        'json' => isset($attribute['json']) ? ((string) $attribute['json']) == 'true' : $defaults->attribute->json
                    );
                    $ret['classes'][$name]['definition']['attributeCount'][$attributeName] = false;
                    $ret['classes'][$name]['attributesReversed'][$ret['classes'][$name]['attributes'][$attributeName]['databaseField']] = $attributeName;
                }
                $ret['classes'][$name]['definition']['abstract'] = empty($ret['classes'][$name]['attributes']);
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
                        'composition' => isset($association['composition']) ? ((string) $association['composition']) == 'true' : false,
                        'databaseTable' => isset($association['databaseTable']) ? (string) $association['databaseTable'] : \CRUDsader\Map::getDatabaseAssociationTable(isset($association['name']) ? (string) $association['name'] : false, $to, $name),
                        'databaseIdField' => isset($association['databaseIdField']) ? (string) $association['databaseIdField'] : $defaults->associations->databaseIdField,
                        'internalField' => isset($association['internalField']) ? (string) $association['internalField'] : ($ref=='table'?$name:$to),
                        'externalField' => isset($association['externalField']) ? (string) $association['externalField'] : ($ref=='table'?$to:$name)
                    );
                    if ($ret['classes'][$name]['associations'][$associationName]['internalField'] == $ret['classes'][$name]['associations'][$associationName]['externalField'])
                        $ret['classes'][$name]['associations'][$associationName]['externalField'] .='2';
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
            // to avoid cache problems
            unset($this->_dom);
            return $ret;
        }
    }
    class LoaderException extends \CRUDsader\Exception {
        
    }
}