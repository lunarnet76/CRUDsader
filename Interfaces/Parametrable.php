<?php
/**
 * @author      Jean-Baptiste Verrey<jeanbaptiste.verrey@gmail.com>
 * @copyright   2011 Jean-Baptiste Verrey
 * @license     see license.txt
 * @since       0.1
 */
namespace CRUDsader\Interfaces {
     /**
      * object can be parametred ?
      * @package CRUDsader\Interfaces
      */
    interface Parametrable {
        /**
         * @param string $name
         */
        public function setParameter($name=false,$value=null);
        
        /**
         * @param string $name
         */
        public function unsetParameter($name=false);
        
        /**
         * @param string $name
         * @return bool
         */
        public function hasParameter($name=false);

        /**
         * @param string $name
         * @return mix
         */
        public function getParameter($name=false);
        
        /**
         * @return array
         */
        public function getParameters();
    }
}