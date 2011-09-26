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
     * utility to include classes depending on their namespace or that follows the PEAR naming convention : My_Class mapped to My/Class.php
     * @category    Class
     * @package     Art
     * @abstract
     */
    abstract class Singleton {
        /**
         * return singletoned instances
         * @staticvar string $instance
         * @return static
         */
        public static function getInstance() {
            static $instance = NULL;
            if (NULL === $instance) {
                $instance = new static();
            }
            return $instance;
        }
        
        /**
         * forbid calling constructor from outside
         * @final
         * @access private
         */
        final private function __construct() {
            $this->init();
        }

        /**
         * this replace the constructor
         */
        public function init() {
        }

        /**
         * forbid cloning
         * @final
         * @access private
         */
        final private function __clone() {
            
        }
    }
}