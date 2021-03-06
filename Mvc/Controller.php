<?php
/**
 * @author      Jean-Baptiste Verrey<jeanbaptiste.verrey@gmail.com>
 * @copyright   2011 Jean-Baptiste Verrey
 * @license     see license.txt
 * @since       0.1
 */
namespace CRUDsader\Mvc {
        /**
         * Mvc controller must inherit from this
         * @package CRUDsader\Mvc
         */
        abstract class Controller extends \CRUDsader\MetaClass {
                protected $_views = array();
                protected $_metas = array();
                protected $_headers = array();
                protected $_title = '';
                protected $_preLoads = array();
                protected $_noRender = false;
                protected $_isRendered = false;
                protected $_template;

                /**
                 * the instanced dependencies
                 * @var array
                 */
                protected $_hasDependencies = array('router', 'frontController');

                /**
                 * identify the class
                 * @var string
                 */
                protected $_classIndex = 'mvc';

                public function baseHref() {
                        return $this->_dependencies['router']->url(null);
                }

                // ** CONSTRUCTOR **/
                public function __construct() {
                        parent::__construct();
                        $this->init();
                        $this->_views['base'] = array('controller' => $this->_dependencies['router']->getController(), 'action' => $this->_dependencies['router']->getAction());
                }

                public function init() {
                        
                }

                /** ACTIONS * */
                public function defaultAction() {
                        
                }

                /** HELPERS * */
                public function __isset($var) {
                        return isset($this->_dependencies['router']->$var) || $this->_dependencies['frontController']->hasPlugin($var);
                }

                public function __get($var) {
                        switch (true) {
                                case $this->_dependencies['frontController']->hasPlugin($var) === true:
                                        return $this->_dependencies['frontController']->getPlugin($var);
                                        break;
                                case isset($this->_dependencies['router']->$var):
                                        return $this->_dependencies['router']->$var;
                                default:
                                        return $this->$var;
                        }
                }

                public function toast($message, $type = 'message') {
                        $session = \CRUDsader\Session::useNamespace('toast');
                        $session->add(array('message' => $message, 'type' => $type));
                }

                public function toasts() {
                        $session = \CRUDsader\Session::useNamespace('toast');
                        $ret = $session->toArray();
                        $session->reset();
                        return $ret;
                }
                /* public function __set($var,$value){
                  $this->$var = $value;
                  } */

                public function __call($name, $arguments) {
                        return call_user_func_array(array($this->_dependencies['frontController'], $name), $arguments);
                }

                public function getRequestUri() {
                        return (string) substr($_SERVER['REQUEST_URI'], strlen($this->_configuration->baseRewrite));
                }

                public function getCurrentURL() {
                        return $this->_dependencies['router']->url(null) . '?' . http_build_query($_GET);
                }

                public function getBaseHref() {
                        return 'http://' . $this->_configuration->server . $this->_configuration->baseRewrite;
                }

                /** INFOS * */
                public function getControllerURL() {
                        return $this->_dependencies['frontController']->getURL() . $this->_dependencies['router']->getModule() . '/' . $this->_dependencies['router']->getController() . '/' . $this->_dependencies['router']->getAction() . $this->_dependencies['router']->getParams();
                }

                // helpers
                public function redirect($options = array()) {
                        if ($options === false) {
                                $url = $this->getControllerURL() . '?' . http_build_query($_GET);
                        }else
                                $url = $this->url($options);
                        if (\CRUDsader\Instancer::getInstance()->debug->getConfiguration()->redirection) {
                                echo '<a href="' . $url . '">' . $url . '</a>';
                                echo \CRUDsader\Instancer::getInstance()->debug->profileDatabase();
                        }else
                                header('Location: ' . $url);
                        exit(0);
                }

                // accessor
                public function setMeta($type, $value) {
                        $this->_metas[$type] = $value;
                }

                public function getMeta($type) {
                        return $this->_metas[$type];
                }

                public function linkMetas() {
                        foreach ($this->_metas as $type => $v) {
                                echo '<meta name="' . $type . '" content="' . addslashes($v) . '">';
                        }
                }

                public function setHeader($name, $content) {
                        $this->_headers[$name] = $content;
                }

                public function getHeader($name, $content) {
                        return $this->_headers[$name];
                }

                public function setTitle($value) {
                        $this->_title = $value;
                }

                public function setNoRender($bool) {
                        $this->_noRender = $bool;
                }

                public function setTemplate($name) {
                        $this->_template = $name;
                }

                public function preRender() {
                        
                }

                public function postRender() {
                        
                }
                
                public function postDispatch(){
                        
                }

                // ** RENDERING VIEW **/
                public function render() {
                        if ($this->_noRender)
                                return;
                        if (!$this->_isRendered)
                                $this->_isRendered = true;

                        foreach ($this->_views as $infos) {
                                $path = $this->getViewPath($infos);
                                require(strtolower($path));
                        }
                }

                public function getViewPath($infos) {
                        $router = $this->_dependencies['router'];
                        $suffix = $this->_configuration->view->suffix;
                        $applicationPath = $this->_dependencies['frontController']->getApplicationPath();
                        $path = strtolower($applicationPath . $this->_dependencies['router']->getModule() . '/view/' . ($infos['controller'] ? $infos['controller'] . '/' : '') . $infos['action'] . '.' . $suffix);
                        switch (true) {
                                case!file_exists($path):
                                        $path = $this->_dependencies['frontController']->getApplicationPath() . $this->_dependencies['router']->getModule() . '/view/default.' . $suffix;
                        }
                        return $path;
                }

                public function getTemplateViewPath() {
                        $file = $this->_dependencies['frontController']->getApplicationPath() . $this->_dependencies['router']->getModule() . '/view/template/' . $this->_template . '.' . $this->_configuration->view->suffix;
                        $path = file_exists($file) ? $file : $this->_dependencies['frontController']->getApplicationPath() . 'view/template/' . $this->_template . '.' . $this->_configuration->view->suffix;
                        return $path;
                }

                public function renderTemplate() {
                        foreach ($this->_headers as $name => $value)
                                header($name . ':' . $value);
                        if ($this->_noRender)
                                return '';
                        if (!isset($this->_template))
                                $this->_template = $this->_configuration->view->template;
                        $this->preRender();
                        if ($this->_template) {
                                $path = $this->getTemplateViewPath();
                                require($path);
                        }else
                                $this->render();
                        $this->postRender();
                }

                /**
                 * @todo remove?
                 * @param <type> $action
                 */
                public function setRender($action, $controller = false) {
                        $this->_action = $action;
                        if ($controller)
                                $this->_controller = $controller;
                        $this->_views['base'] = array('controller' => $controller, 'action' => $action);
                }
                /* public function renderPart($action, $controller=false) {
                  $infos = array('action' => $action, 'controller' => $controller);
                  $path = file_exists($this->_dependencies['frontController']->getModuleDirectory() . $this->_dependencies['frontController']->getModule() . '/view/' . ($infos['controller'] ? str_replace('_', '/', $infos['controller']) . '/' : '') . $infos['action'] . $suffix) ? $this->_dependencies['frontController']->getModuleDirectory() . $this->_dependencies['frontController']->getModule() . '/view/' . ($infos['controller'] ? str_replace('_', '/', $infos['controller']) . '/' : '') . $infos['action'] . $suffix : 'module/' . $this->_dependencies['frontController']->getModule() . '/view/default' . $suffix;
                  ob_start();
                  require($path);
                  return ob_get_clean();
                  } */

                public function addView($action, $controller = false) {
                        $this->_views[] = array('controller' => $controller ? $controller : false, 'action' => $action);
                }
        }
        class ControllerException extends \CRUDsader\Exception {
                
        }
}