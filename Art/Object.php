<?php
/**
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
     * Object class
     * @package     Art
     */
    class Object {
        protected $_class = NULL;
        protected $_map = NULL;

        public function __construct($class) {
            $this->_map = Map::getInstance();
            if (!$this->_map->classExists($class))
                throw new Object\Exception('class "' . $class . '" does not exist');
            $this->_class = $class;
        }
        
        public function __get($var){
            return new Object\Attribute($var, 'string');
        }

        /**
         * forbid cloning
         * @final
         * @access private
         */
        final private function __clone() {
            
        }
    }
    class ObjectException extends \Exception {
        
    }
}