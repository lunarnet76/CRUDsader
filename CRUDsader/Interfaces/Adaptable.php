<?php
/**
 * @author      Jean-Baptiste Verrey<jeanbaptiste.verrey@gmail.com>
 * @copyright   2011 Jean-Baptiste Verrey
 * @license     see license.txt
 * @since       0.1
 */
namespace CRUDsader\Interfaces {
    /**
     * object has Adapter ?
     * @package CRUDsader\Interfaces 
     */
    interface Adaptable {
        /**
         * @param string $name
         * @return bool
         */
        public function hasAdapter($name=false);

        /**
         * @param string $name
         * @return \CRUDsader\Adapter
         */
        public function getAdapter($name=false);
        
        /**
         * @return array
         */
        public function getAdapters();
    }
}