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
            $this->_adapter['Loader'] = \Art\Adapter::factory(array('map' => 'loader'));
            $this->_map = $this->_adapter['Loader']->getSchema($this->_configuration->defaults);
        }

        /**
         * validate the schema
         * @return bool 
         */
        public function validate() {
            return $this->_adapter['loader']->validate();
        }
        
        public function extract(){
            $this->_adapter['extractor']=\Art\Adapter::factory(array('map' => 'extractor'));
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

        public function classHasAssociation($className, $associationName) {
            return isset($this->_map['classes'][$className]['associations'][$associationName]);
        }

        public function classGetAssociation($className, $associationName) {
            return $this->_map['classes'][$className]['associations'][$associationName];
        }

        public function classGetJoin($className, $associationName, $fromAlias, $joinedAlias, $associationClassAlias) {
            $association = $this->_map['classes'][$className]['associations'][$associationName];
            if (!isset($this->_map['classes'][$className]['associations'][$associationName]))
                throw new MapException('error in JOIN : class "' . $className . '" has no association "' . $associationName . '"');
            $joins = array();
            switch ($association['scenario']) {
                case '1':
                    // association table
                    $joins['association'] = array(
                        'fromAlias' => $fromAlias,
                        'fromColumn' => 'id',
                        'toAlias' => $associationClassAlias,
                        'toColumn' => $className,
                        'toTable' => $association['databaseTable'],
                        'type' => 'left'
                    );
                    // association table
                    $joins['table'] = array(
                        'fromAlias' => $associationClassAlias,
                        'fromColumn' => $association['to'],
                        'toAlias' => $joinedAlias,
                        'toColumn' => 'id',
                        'toTable' => $this->_map['classes'][$association['to']]['definition']['databaseTable'],
                        'type' => 'left'
                    );
                    break; /*
                  if ($association['class']) {
                  if (empty($associationClassAlias))
                  $associationClassAlias = ++self::$_associationTableAlias;
                  // association table

                  $sql->join(array(
                  'fromAlias' => $associationClassAlias,
                  'fromColumn' => $joinedClass,
                  'toAlias' => $joinedAlias,
                  'toColumn' => 'id',
                  'toTable' => $association['toTable'],
                  'type' => 'left'
                  ));
                  }
                  else if ($association['reference'] == 'internal')
                  $sql->join(array(
                  'fromAlias' => $on,
                  'fromColumn' => $joinedClass,
                  'toAlias' => $alias,
                  'toColumn' => 'id',
                  'toTable' => $association['table'],
                  'type' => 'left'
                  ));
                  else // external
                  $sql->join(array(
                  'fromAlias' => $alias,
                  'fromColumn' => 'id',
                  'toAlias' => $on,
                  'toColumn' => $joinedClass,
                  'toTable' => $association['table'],
                  'type' => 'left'
                  ));
                  break; */
            }
            return $joins;
        }
    }
    class MapException extends \Art\Exception{
        
    }
}