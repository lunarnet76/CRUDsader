<?php
namespace CRUDsader\MVC\Controller {
    class Front extends \CRUDsader\Singleton implements \CRUDsader\Interfaces\Adaptable {
        /*
         * @var Art_Block
         */
        protected $_configuration = NULL;

        /*
         * @var array
         */
        protected $_modulePlugins = array();
        /*
         * @var bool
         */
        protected $_skipRouterHistoric = false;

        /**
         * @var Art_Mvc_Controller_Abstract instance
         */
        protected $_instanceController;

        /**
         * constructor
         */
        public function init() {
            $this->_configuration = \CRUDsader\Configuration::getInstance()->mvc;
            $this->_adapters['routerHistoric'] = \CRUDsader\Adapter::factory(array('mvc'=>'routerHistoric'));
        }
        /**
         * list of all adapters
         * @var array 
         */
        protected $_adapters = array();

        /**
         * @param string $name
         * @return bool
         */
        public function hasAdapter($name=false) {
            return isset($this->_adapters[$name]);
        }

        /**
         * @param string $name
         * @return \CRUDsader\Adapter
         */
        public function getAdapter($name=false) {
            return $this->_adapters[$name];
        }

        /**
         * @return array
         */
        public function getAdapters() {
            return $this->_adapters;
        }
        
        public function moduleHasPlugin($pluginName) {
            return isset($this->_modulePlugins[$pluginName]);
        }

        public function moduleGetPlugin($pluginName) {
            return $this->_modulePlugins[$pluginName];
        }

        public function getApplicationPath() {
            return $this->_configuration->applicationPath;
        }

        public function getURL($protocole='http://') {
            return $protocole .  $this->_configuration->server.$this->_configuration->baseRewrite;
        }
        
        public function getLastURL(){
            return $this->_adapters['routerHistoric']->getLast()->uri;
        }

        public function route($route=false) {
            $this->_adapters['router'] = \CRUDsader\Adapter::factory(array('mvc' => 'router'));
            $this->_adapters['router']->setConfiguration($this->_configuration);
            $route = $this->_adapters['router']->route($route ? $route : $_SERVER['REQUEST_URI']);
            if (!$route)
                throw new FrontException('cannot find the route');
            $module = $this->_adapters['router']->getModule();
            if ($module && !isset($this->_configuration->modules->$module))
                throw new FrontException('module "' . $module . '" does not exist or is not in the configuration');
            $path = $this->_configuration->applicationPath . ($module ? $module . '/' : '');
            \CRUDsader\Autoload::registerNameSpace('Controller', $path . 'controller/');
            \CRUDsader\Autoload::registerNameSpace('Plugin', $path . 'plugin/');
            \CRUDsader\Autoload::registerNameSpace('Model', $path . 'model/');
            \CRUDsader\Autoload::registerNameSpace('Input', $path . 'input/');
            \CRUDsader\Configuration::getInstance()->form->view->path=$path.'form/';
            // init plugins
            pre($this->_configuration->modules);
            $plugins=$this->_configuration->modules->{$this->_adapters['router']->getModule()};
            foreach ($plugins as $pluginName=>$pluginOptions) {
                $class = 'Plugin\\' . $pluginName;
                $plugin = $this->_modulePlugins[$pluginName] = call_user_func_array(array($class,'getInstance'),array());
                $plugin->setConfiguration($pluginOptions);
                $plugin->postRoute($this->_adapters['router']);
            }
        }

        public function skipRouterHistoric($bool=true) {
            $this->_skipRouterHistoric = $bool;
        }

        public function dispatch() {
            // plugins
            foreach ($this->_modulePlugins as $plugin)
                $plugin->preDispatch();
            $this->_instanceController = call_user_func_array(array('Controller\\' . ucFirst(ucfirst($this->_adapters['router']->getController())),'getInstance'),array($this, $this->_adapters['router']->toArray()));
            $this->_instanceController->setConfiguration($this->_configuration);
            $this->_instanceController->setRouter($this->_adapters['router']);
            if (!method_exists($this->_instanceController, $this->_adapters['router']->getAction() . 'Action') && !method_exists($this->_instanceController, '__call'))
                throw new Art_Mvc_Controller_Front_Exception('URL not found, no function ' . $this->_adapters['router']->getAction());
            $this->_instanceController->{$this->_adapters['router']->getAction() . 'Action'}();
            $this->_instanceController->renderTemplate();
            if (!$this->_skipRouterHistoric)
                $this->_adapters['routerHistoric']->registerRoute($this->_adapters['router']);
            foreach ($this->_modulePlugins as $plugin)
                $plugin->postDispatch();
        }

        public function url(array $options=array()) {
            if (isset($options['url']))
                return $options['url'];
            if (isset($options['route']))
                return 'http://'.$this->_configuration->server.$this->_configuration->baseRewrite.$options['route'].$this->_configuration->route->suffix;
            $defaults = array(
                'protocol' => 'http://',
                'server' => $this->_configuration->server,
                'path' => $this->_configuration->baseRewrite,
                'module' => $this->_adapters['router']->getModule() . '/',
                'controller' => $this->_adapters['router']->getController() . '/',
                'action' => $this->_adapters['router']->getAction()
            );
            $url = '';
            foreach ($defaults as $name => $default)
                $url.=isset($options[$name]) ? $options[$name] . ($name != 'action' ? '/' : '') : $default;
            $url.=!empty($options['params']) ? http_build_query($options['params']) : http_build_query($this->_adapters['router']->getParams());
            return $url;
        }

        public function getInstanceController() {
            return $this->_instanceController;
        }
    }
    class FrontException extends \CRUDsader\Exception {
        
    }
}