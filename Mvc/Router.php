<?php
/**
 * @author      Jean-Baptiste Verrey<jeanbaptiste.verrey@gmail.com>
 * @copyright   2011 Jean-Baptiste Verrey
 * @license     see license.txt
 * @since       0.1
 */
namespace CRUDsader\Mvc {
        /**
         * Controller router
         */
        abstract class Router extends \CRUDsader\MetaClass implements \CRUDsader\Interfaces\Arrayable {
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

                public function toArray() {
                        return array(
                            'route' => $this->getRoute(),
                            'module' => $this->getModule(),
                            'controller' => $this->getController(),
                            'action' => $this->getAction(),
                            'params' => $this->getParams()
                        );
                }

                public function getRealRoute($uri) {
                        if (PHP_SAPI == 'cli') {
                                return $uri;
                        } else {
                                $st = strlen($this->_configuration->route->suffix);
                                $real = $st ? substr($uri, strlen($this->_configuration->baseRewrite), -$st) : substr($uri, strlen($this->_configuration->baseRewrite));
                                return $real;
                        }
                }

                public function getModule() {
                        return isset($this->_module) ? $this->_module : $this->_configuration->default->module;
                }

                public function getController() {
                        return isset($this->_controller) ? $this->_controller : $this->_configuration->default->controller;
                }

                public function getAction() {
                        return isset($this->_action) ? $this->_action : $this->_configuration->default->action;
                }

                public function getArrayParams() {
                        return $this->_params;
                }

                public function __get($name) {
                        return $this->_params[$name];
                }

                public function __isset($name) {
                        return isset($this->_params[$name]);
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

                public function getRoute() {
                        return $this->_route;
                }

                /**
                 * @param $uri string
                 * @return bool|array false or array with module class function and params
                 */
                abstract public function route($uri);

                /**
                 * useful for redirect, convert to full uri
                 */
                abstract public function url($url);
        }
}
