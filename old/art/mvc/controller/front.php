<?php

/**
 * Front controller for MVC
 *
 * LICENSE: see Art/license.txt
 *
 * @author Jean-Baptiste Verrey <jeanbaptiste.verrey@gmail.com>
 * @copyright  2010 Jean-Baptiste Verrey
 * @license    http://www.Art.com/license/2.txt
 * @version    $Id$
 * @link       http://www.Art.com/manual/
 * @since      2.0
 */

/**
 * @category   MVC
 * @package    Art2
 */
class Art_Mvc_Controller_Front {
    /*
     * @var Art_Block
     */
    protected $_configuration = NULL;
    /*
     * @var Art_Router_Abstract
     */
    protected $_router = NULL;
    /*
     * @var array
     */
    protected $_modulePlugins = array();
    /*
     * @var bool
     */
    protected $_dontRegisterRoute = false;
    /**
     * @var Art_Mvc_Controller_Abstract instance
     */
    protected $_instanceController;
    /**
     * @var Art_Mvc_Controller_Abstract instance
     */
    protected $_instanceHistoric;
    /**
     * @staticvar singletoned instance
     */
    protected static $_instance;

    /**
     * @static
     * @return self
     */
    public static function getInstance() {
        if (!isset(self::$_instance))
            self::$_instance = new self();
        return self::$_instance;
    }

    /**
     * singletoned instance
     * @access protected
     */
    protected function __construct() {
        $this->_configuration = Art_Configuration::getInstance()->mvc;
    }

    public function getRouter() {
        return $this->_router;
    }

    public function getFolder() {
        return $this->_configuration->folder;
    }

    public function getURL($protocole='http://') {
        return $protocole . $this->_configuration->server . $this->_configuration->path . '/';
    }

    public function route() {
        $routerClass = 'Art_Mvc_Router_' . $this->_configuration->router->name;
        $this->_router = new $routerClass($this->_configuration);
        $this->_router->route();

        $path = $this->_configuration->folder . $this->getRouter()->getModule() . '/';
        Art_Data::registerPath('module',$path.'data/');
        Art_Class::registerNameSpace('Controller', $path);
        Art_Class::registerNameSpace('Model', $path);
        Art_Class::registerNameSpace('Data', $path);
        Art_Class::registerNameSpace('View', $path);
        Art_Class::registerNameSpace('Art_Data', $path);
        Art_Class::registerNameSpace('Plugin', $path);
        Art_Configuration::getInstance()->form->viewPath = $path . 'form/';
        // init plugins
        if(isset($this->_configuration->module->{$this->_router->getModule()}->plugin)){
            $plugins = $this->_configuration->module->{$this->_router->getModule()}->plugin->toArray();
            foreach ($plugins as $pluginName => $pluginOption) {
                $pluginOption = json_decode('{' . str_replace('\'', '"', $pluginOption) . '}');
                $class = 'Plugin_' . $pluginName;
                $plugin = $this->_modulePlugins[$pluginName] = new $class($pluginOption);
                $plugin->postRoute($this->_router);
            }
        }
        $this->_instanceHistoric = Art_Mvc_Historic::getInstance();
    }

    public function moduleHasPlugin($pluginName) {
        return isset($this->_modulePlugins[$pluginName]);
    }

    public function moduleGetPlugin($pluginName) {
        return $this->_modulePlugins[$pluginName];
    }

    public function dontRegisterRoute() {
        $this->_dontRegisterRoute = true;
    }

    public function getRouteHistoric() {
        return $this->_instanceHistoric;
    }

    public function dispatch() {
        // plugins
        foreach ($this->_modulePlugins as $plugin)
            $plugin->preDispatch();
        $className = 'Controller_' . str_replace('/', '_', $this->_router->getController());
        $this->_instanceController = new $className($this, $this->_configuration);
        $this->_instanceController->init();
        if (!method_exists($this->_instanceController, $this->_router->getAction() . 'Action') && !method_exists($this->_instanceController, '__call'))
            throw new Art_Mvc_Controller_Front_Exception('URL not found, no function ' . $this->_router->getAction());
        $this->_instanceController->{$this->_router->getAction() . 'Action'} ();
        $this->_instanceController->renderTemplate();
        if ($this->_configuration->historic && !$this->_dontRegisterRoute) 
            $this->_instanceHistoric->register($this->_router);
        foreach ($this->_modulePlugins as $plugin)
            $plugin->postDispatch();
    }

    public function url(array $options=array()) {
        if(isset($options['url']))return $options['url'];
        $defaults = array(
            'protocol' => 'http://',
            'server' => $this->_configuration->server,
            'path' => $this->_configuration->path . '/',
            'module' => $this->getRouter()->getModule() . '/',
            'controller' => $this->getRouter()->getController() . '/',
            'action' => $this->getRouter()->getAction()
        );
        $url = '';
        foreach ($defaults as $name => $default)
            $url.=isset($options[$name]) ? $options[$name] . ($name != 'action' ? '/' : '') : $default;
        $params = !empty($options['params']) ? $this->getRouter()->parseParamsInverse($options['params']) : $this->_configuration->router->separator->params . $this->getRouter()->getParams();

        $url.= ! empty($params) ? $this->_configuration->router->separator->parameter . $params : '';
        return $url;
    }

    public function getInstanceController() {
        return $this->_instanceController;
    }

    public function __get($var) {
        return $this->_router->{'get' . ucfirst($var)} ();
    }

}
?>