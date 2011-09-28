<?php
namespace CRUDsader\MVC {
    abstract class Plugin extends \CRUDsader\Singleton implements \CRUDsader\Interfaces\Configurable{
        protected $_configuration;
        /**
         * @param Block $configuration
         */
         public function setConfiguration(\CRUDsader\Block $configuration=null) {
            $this->_configuration = $configuration;
        }

        /**
         * @return Block
         */
        public function getConfiguration() {
            return $this->_configuration;
        }

        public function init() {
            
        }

        public function postRoute(\CRUDsader\Adapter\MVC\Router $router) {
            
        }

        public function preDispatch() {
            
        }

        public function postDispatch() {
            
        }
    }
}