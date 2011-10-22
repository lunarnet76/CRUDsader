<?php
/**
 * @author      Jean-Baptiste Verrey<jeanbaptiste.verrey@gmail.com>
 * @copyright   2011 Jean-Baptiste Verrey
 * @license     see license.txt
 * @since       0.1
 */
namespace CRUDsader\Mvc\Controller {
    /**
     * MVC Front controller
     * @package CRUDsader\Mvc\Controller
     */
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
            $this->_adapters['routerHistoric'] = \CRUDsader\Adapter::factory(array('mvc' => 'routerHistoric'));
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
            return $protocole . $this->_configuration->server . $this->_configuration->baseRewrite;
        }

        /**
         * specify a route or route following the URI
         * @param string|bool $route
         * @return string modulename 
         */
        public function route($route=false) {
            $this->_adapters['router'] = \CRUDsader\Adapter::factory(array('mvc' => 'router'));
            $this->_adapters['router']->setConfiguration($this->_configuration);
            if ($route === false) {
                $sp = strpos($_SERVER['REQUEST_URI'], '?');
                $route = $sp !== false ? substr($_SERVER['REQUEST_URI'], 0, $sp) : $_SERVER['REQUEST_URI'];
            }
            $route = $this->_adapters['router']->route($route);
            if (!$route)
                throw new FrontException('cannot find the route');
            $module = $this->_adapters['router']->getModule();
            if ($module && !isset($this->_configuration->modules->$module))
                throw new FrontException('module "' . $module . '" does not exist or is not in the configuration');
            // init plugins
            $module = $this->_adapters['router']->getModule() ? $this->_adapters['router']->getModule() : false;
            $plugins = $module ? (isset($this->_configuration->plugin->$module) ? $this->_configuration->plugin->$module : array()) : $this->_configuration->plugin;
            foreach ($plugins as $pluginName => $pluginOptions) {
                $class = 'Plugin\\' . $pluginName;
                $plugin = $this->_modulePlugins[$pluginName] = call_user_func_array(array($class, 'getInstance'), array());
                if ($pluginOptions instanceof \CRUDsader\Block)
                    $plugin->setConfiguration($pluginOptions);
                $plugin->postRoute($this->_adapters['router']);
            }
            return $this->_adapters['router']->getModule();
        }

        public function skipRouterHistoric($bool=true) {
            $this->_skipRouterHistoric = $bool;
        }

        public function dispatch() {
            // plugins
            foreach ($this->_modulePlugins as $plugin)
                $plugin->preDispatch();
            $this->_instanceController = call_user_func_array(array('Controller\\' . ucFirst($this->_adapters['router']->getController()), 'getInstance'), array($this, $this->_adapters['router']->toArray()));
            $this->_instanceController->setConfiguration($this->_configuration);
            $this->_instanceController->setRouter($this->_adapters['router']);
            if (method_exists($this->_instanceController, $this->_adapters['router']->getAction() . 'Action'))
                $this->_instanceController->{$this->_adapters['router']->getAction() . 'Action'}();
            else if (method_exists($this->_instanceController, '__callAction'))
                $this->_instanceController->__callAction($this->_adapters['router']->getAction());
            else
                throw new FrontException('URL not found, no function ' . $this->_adapters['router']->getAction());
            $this->_instanceController->renderTemplate();
            if (!$this->_skipRouterHistoric)
                $this->_adapters['routerHistoric']->registerRoute($this->_adapters['router']);
            foreach ($this->_modulePlugins as $plugin)
                $plugin->postDispatch();
        }

        public function url($options=array()) {
            return $this->_adapters['router']->url($options);
        }

        public function getInstanceController() {
            return $this->_instanceController;
        }
    }
    class FrontException extends \CRUDsader\Exception {
        
    }
}