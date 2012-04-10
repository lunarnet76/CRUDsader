<?php
/**
 * @author      Jean-Baptiste Verrey<jeanbaptiste.verrey@gmail.com>
 * @copyright   2011 Jean-Baptiste Verrey
 * @license     see license.txt
 * @since       0.1
 */
namespace CRUDsader\MVC\Controller {
	/**
	 * MVC Front controller
	 * @package CRUDsader\MVC\Controller
	 */
	class Front extends \CRUDsader\MetaClass {
		/*
		 * @var string
		 */
		protected $_configurationIndex = 'mvc';

		/*
		 * @var array
		 */
		protected $_plugins = array();
		/*
		 * @var bool
		 */
		protected $_skipRouterHistoric = false;

		/**
		 * @var MVC\Controller instance
		 */
		protected $_instanceController;

		/**
		 * constructor
		 */
		public function __construct()
		{
			parent::__construct();
			$this->setDependency('routerHistoric', 'mvc.routerHistoric');
			$this->setDependency('router', 'mvc.router');
			$this->setConfiguration(\CRUDsader\Instancer::getInstance()->configuration->mvc);
		}

		/**
		 * @param \CRUDsader\Block $block 
		 */
		public function setConfiguration(\CRUDsader\Block $block = null)
		{
			$this->_configuration = $block;
			$this->_dependencies['router']->setConfiguration($block);
		}

		public function hasPlugin($pluginName)
		{
			return isset($this->_dependencies['plugin' . $pluginName]);
		}

		public function getPlugin($pluginName)
		{
			return $this->_dependencies['plugin' . $pluginName];
		}

		public function getApplicationPath()
		{
			return $this->_configuration->applicationPath;
		}

		public function getURL($protocole = 'http://')
		{
			return $protocole . $this->_configuration->server . $this->_configuration->baseRewrite;
		}
		
		public function getLastUrl(){
			$routeInfo = ($this->_dependencies['routerHistoric']->getLast());
			return $this->url($routeInfo->route);
		}

		/**
		 * specify a route or route following the URI
		 * @param string|bool $route
		 * @return string modulename 
		 */
		public function route($route = false)
		{
			if ($route === false) {
				$sp = strpos($_SERVER['REQUEST_URI'], '?');
				$route = $sp !== false ? substr($_SERVER['REQUEST_URI'], 0, $sp) : $_SERVER['REQUEST_URI'];
			}
			$_SERVER['PHP_SELF'] = $route;
			
			$route = $this->_dependencies['router']->route($route);
			if (!$route)
				throw new FrontException('cannot find the route');
			$module = $this->_dependencies['router']->getModule();

			

			if ($module && !isset($this->_configuration->modules->$module))
				throw new FrontException('module "' . $module . '" does not exist or is not in the configuration');
			// init plugins
			$plugins = $module ? (isset($this->_configuration->plugins->$module) ? $this->_configuration->plugins->$module : array()) : $this->_configuration->plugins;
			$instancer = \CRUDsader\Instancer::getInstance();
			foreach ($plugins as $pluginName => $pluginOptions) {
				$cfg = $instancer->getConfiguration();
				$cfg->{'mvc.plugin.' . $pluginName} = array('class' => 'Plugin\\' . $pluginName, 'singleton' => true);
				$instancer->setConfiguration($cfg);
				$this->_plugins[$pluginName] =  $this->_dependencies['plugin' . $pluginName] = $instancer->{'mvc.plugin.' . $pluginName};
				if ($pluginOptions instanceof \CRUDsader\Block)
					$this->_dependencies['plugin' . $pluginName]->setConfiguration($pluginOptions);
				$this->_dependencies['plugin' . $pluginName]->postRoute($this->_dependencies['router']);
			}
			return $module;
		}

		/**
		 * dont register route in the historic
		 * @param type $bool 
		 */
		public function skipRouterHistoric($bool = true)
		{
			$this->_skipRouterHistoric = $bool;
		}

		/**
		 * call the action controller
		 */
		public function dispatch()
		{
			// plugins
			foreach ($this->_plugins as $plugin){
				$plugin->preDispatch();
			}
			$class = 'Controller\\' . ucFirst($this->_dependencies['router']->getController());
			$this->_dependencies['actionController'] = new $class();
			if (method_exists($this->_dependencies['actionController'], $this->_dependencies['router']->getAction() . 'Action'))
				call_user_func_array(array($this->_dependencies['actionController'], $this->_dependencies['router']->getAction() . 'Action'), $this->_dependencies['router']->getArrayParams());
			else if (method_exists($this->_dependencies['actionController'], '__callAction'))
				$this->_dependencies['actionController']->__callAction($this->_dependencies['router']->getAction(), $this->_dependencies['router']->getArrayParams());
			else
				throw new FrontException('URL not found, no function ' . $this->_dependencies['router']->getAction());
			$this->_dependencies['actionController']->renderTemplate();
			if (!$this->_skipRouterHistoric)
				$this->_dependencies['routerHistoric']->registerRoute($this->_dependencies['router']);
			foreach ($this->_plugins as $plugin)
				$plugin->postDispatch();
		}
		public function url($options = array())
		{
			return $this->_dependencies['router']->url($options);
		}

		public function getActionController()
		{
			return $this->_dependencies['actionController'];
		}
	}
	class FrontException extends \CRUDsader\Exception {
		
	}
}