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
     * @category    ORM
     * @package     Art
     * @abstract
     */
    class Map extends Singleton {
        
        
        protected $_map = array();

        public function classExists($className) {
            return true;
        }

        public function classGetTable($className) {
            return 'T' . $className;
        }

        public function classHasAssociation($className, $associationName) {
            return isset($this->_map[$className]['associations'][$associationName]);
        }

        public function classGetAssociation($className, $associationName) {
            return $this->_map[$className]['associations'][$associationName];
        }
    }
}