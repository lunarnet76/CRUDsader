<?php
/**
 * @author      Jean-Baptiste Verrey<jeanbaptiste.verrey@gmail.com>
 * @copyright   2011 Jean-Baptiste Verrey
 * @license     see license.txt
 * @since       0.1
 */
namespace CRUDsader\Mvc {
    /**
     * @abstract
     * @package CRUDsader\Mvc
     */
    abstract class Plugin extends \CRUDsader\MetaClass{
       

        public function postRoute(\CRUDsader\Mvc\Router $router) {
            
        }

        public function preDispatch() {
            
        }

        public function postDispatch() {
            
        }
    }
}