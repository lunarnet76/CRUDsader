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
         * @var \Art\Adapter\Map\Loader 
         */
        protected $_adapterLoader = NULL;
        /**
         * @var array
         */
        protected $_map = NULL;

        /**
         * constructor, load the map schema
         */
        public function init() {
            $this->_adapterLoader = \Art\Adapter::factory(array('map' => 'loader'));
            $this->_configuration = \Art\Configuration::getInstance()->map;
            $this->_adapterLoader->setConfiguration($this->_configuration);
            $this->_map = $this->_adapterLoader->getSchema($this->_configuration->defaults);
        }

        /**
         * validate the schema
         * @return bool 
         */
        public function validateSchema() {
            return $this->_adapterLoader->validate();
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
        
        public function classHasAssociation($className,$associationName){
            return isset($this->_map['classes'][$className]['associations'][$associationName]);
        }
        
        public function classGetAssociation($className,$associationName){
            return $this->_map['classes'][$className]['associations'][$associationName];
        }
    }
}