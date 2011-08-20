<?php

/**
 *
 * LICENSE: see Art/license.txt
 *
 * @author      Jean-Baptiste Verrey <jeanbaptiste.verrey@gmail.com>
 * @copyright   2011 Jean-Baptiste Verrey
 * @license     http://www.Art.com/license/1.txt
 * @version     $Id$
 * @link        http://www.Art.com/manual/
 * @since       1.0
 */

namespace Art {

    /**
     * Map the ORM schema to classes
     * @package     Art
     */
    class Map extends Singleton {
        const BASE_ASSOCIATION_CLASS='__ASSOC__';
        /**
         * @var \Art\Block 
         */
        protected $_configuration = NULL;
        /**
         * @var array 
         */
        protected $_adapters = array();
        /**
         * @var array
         */
        protected $_map = NULL;

        /**
         * constructor, load the map schema
         */
        public function init() {
            $this->_configuration = \Art\Configuration::getInstance()->map;
            $this->_adapter['loader'] = \Art\Adapter::factory(array('map' => 'loader'));
            $this->_map = $this->_adapter['loader']->getSchema($this->_configuration->defaults);
        }

        /**
         * validate the schema
         * @return bool 
         */
        public function validate() {
            return $this->_adapter['loader']->validate();
        }

        public function extract() {
            $this->_adapter['extractor'] = \Art\Adapter::factory(array('map' => 'extractor'));
            $this->_adapter['extractor']->setConfiguration($this->_configuration->defaults);
            return $this->_adapter['extractor']->create($this->_map);
        }

        /**
         *  @return bool
         */
        public function classExists($className) {
            return isset($this->_map['classes'][$className]);
        }

        /**
         * @param string $className
         * @return string 
         */
        public function classGetDatabaseTable($className) {
            return $this->_map['classes'][$className]['definition']['databaseTable'];
        }
        
        public function classGetDatabaseTableField($className,$attributeName) {
            return $attributeName=='id'?$this->_map['classes'][$className]['definition']['databaseIdField']:$this->_map['classes'][$className]['attributes'][$attributeName]['databaseField'];
        }
        
        public function classGetAttributeCount($className){
            return $className==self::BASE_ASSOCIATION_CLASS?2:count($this->_map['classes'][$className]['definition']['attributeCount']);
        }

        public function classHasAssociation($className, $associationName) {
            return isset($this->_map['classes'][$className]['associations'][$associationName]);
        }

        public function classGetAssociation($className, $associationName) {
            return $this->_map['classes'][$className]['associations'][$associationName];
        }
        
        public function classGetInfos($className){
            return $this->_map['classes'][$className];
        }
        
        

        public function classGetJoin($className, $associationName, $fromAlias, $joinedAlias, $associationClassAlias=false) {
            if (!isset($this->_map['classes'][$className]['associations'][$associationName]))
                throw new MapException('join error : class "' . $className . '" has no association "' . $associationName . '"');
            $association = $this->_map['classes'][$className]['associations'][$associationName];
            $joins = array();
            
            switch ($association['reference']) {
                case 'external':
                    $joins['table'] = array(
                        'fromAlias' => $fromAlias,
                        'fromColumn' => $this->_map['classes'][$className]['definition']['databaseIdField'],
                        'toAlias' => $joinedAlias,
                        'toColumn' => $association['name']?$associationName:$className,
                        'toTable' => $this->_map['classes'][$association['to']]['definition']['databaseTable'],
                        'toClass' => $association['to'],
                        'type' => 'left'
                    );
                    break;
                case 'internal':
                    $joins['table'] = array(
                        'fromAlias' => $fromAlias,
                        'fromColumn' => $association['name']?$associationName:$association['to'],
                        'toAlias' => $joinedAlias,
                        'toColumn' => $this->_map['classes'][$association['to']]['definition']['databaseIdField'],
                        'toTable' => $this->_map['classes'][$association['to']]['definition']['databaseTable'],
                        'toClass' => $association['to'],
                        'type' => 'left'
                    );
                    break;
                case 'class':
                    $joins['association'] = array(
                        'fromAlias' => $fromAlias,
                        'fromColumn' => $this->_map['classes'][$className]['definition']['databaseIdField'],
                        'toAlias' => $associationClassAlias,
                        'toColumn' => $className,
                        'toTable' => $association['databaseTable'],
                        'toClass' => $association['name']?$association['name']:self::BASE_ASSOCIATION_CLASS,
                        'type' => 'left'
                    );
                    $joins['table'] = array(
                        'fromAlias' => $associationClassAlias,
                        'fromColumn' => $association['to'],
                        'toAlias' => $joinedAlias,
                        'toColumn' => $this->_map['classes'][$association['to']]['definition']['databaseIdField'],
                        'toTable' => $this->_map['classes'][$association['to']]['definition']['databaseTable'],
                        'toClass' => $association['to'],
                        'type' => 'left'
                    );
                    break;
            }
            return $joins;
        }

        public static function getDatabaseAssociationTable($associationName, $classTo, $classFrom) {
            return $associationName? : ($classFrom > $classTo ? $classTo . '2' . $classFrom : $classFrom . '2' . $classTo);
        }

    }

    class MapException extends \Art\Exception {
        
    }

}