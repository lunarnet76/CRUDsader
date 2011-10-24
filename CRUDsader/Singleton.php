<?php
/**
 * @author      Jean-Baptiste Verrey<jeanbaptiste.verrey@gmail.com>
 * @copyright   2011 Jean-Baptiste Verrey
 * @license     see license.txt
 * @since       0.1
 */
namespace CRUDsader {
    /**
     * utility to include classes depending on their namespace or that follows the PEAR naming convention : My_Class mapped to My/Class.php
     * @package     CRUDsader
     * @abstract
     */
    abstract class Singleton {
        /**
         * return singletoned instances
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