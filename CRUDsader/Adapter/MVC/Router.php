<?php
namespace CRUDsader\Adapter\MVC {
    abstract class Router extends \CRUDsader\Adapter{
        /*
         * @var string
         */
        protected $_route = NULL;
        /*
         * @var string
         */
        protected $_module = NULL;
        /*
         * @var string
         */
        protected $_controller = NULL;
        /*
         * @var string
         */
        protected $_action = NULL;
        /*
         * @var array
         */
        protected $_params = array();
        
        public function toArray(){
            return array(
                'route'=>$this->getRoute(),
                'module'=>$this->getModule(),
                'controller'=>$this->getController(),
                'action'=>$this->getAction(),
                'params'=>$this->getParams()
            );
        }

        public function getModule() {
            return isset($this->_module)?$this->_module:$this->_configuration->default->module;
        }

        public function getController() {
            return isset($this->_controller)?$this->_controller:$this->_configuration->default->controller;
        }

        public function getAction() {
            return isset($this->_action)?$this->_action:$this->_configuration->default->action;
        }

        public function getParams() {
            return http_build_query($this->_params);
        }

        public function setModule($module) {
            $this->_module = $module;
        }

        public function setController($controller) {
            $this->_controller = $controller;
        }

        public function setAction($action) {
            $this->_action = $action;
        }

        public function setParams(array $params) {
            $this->_params = $params;
        }
        
        public function getRoute(){
            return $this->_route;
        }

        /**
         * @param $uri string
         * @return bool|array false or array with module class function and params
         */
        abstract public function route($uri);
        
        abstract public function url($options=array());
    }
}
