<?php
/**
 * @author      Jean-Baptiste Verrey<jeanbaptiste.verrey@gmail.com>
 * @copyright   2011 Jean-Baptiste Verrey
 * @license     see license.txt
 * @since       0.1
 */
namespace CRUDsader\Mvc\Router {
        /**
         * route like 
         * ${server.baseRewrite}/$language/$moduleControllerName/$function$suffix?$params
         * ${server.baseRewrite}/$language/$function$suffix?$params
         * ${server.baseRewrite}/$function$suffix?$params
         * @package CRUDsader\Mvc\Router
         */
        class Implicit extends \CRUDsader\Mvc\Router {

                /**
                 * calculate a route
                 * @param string $uri
                 * @return bool wether it was found or not 
                 */
                function route($uri) {
                        $this->_route = $this->getRealRoute($uri);
                        $routes = $this->_configuration->routes;

                        $ex = explode($this->_configuration->route->separator, $this->_route);

                        $this->_params = !empty($_REQUEST) ? $_REQUEST : array();
                        $route = new \stdClass();
                        if (isset($ex[0]))
                                $route->controller = $ex[0];
                        if (isset($ex[1]))
                                $route->action = $ex[1];

                        if (!$route) {
                                $this->_module = !empty($this->_configuration->default->module) ? $this->_configuration->default->module : false;
                                $this->_controller = $this->_configuration->default->controller;
                        } else {
                                if (isset($route->module))
                                        $this->_module = $route->module;
                                else
                                        $this->_module = $this->_configuration->default->module;
                                if (isset($route->controller))
                                        $this->_controller = $route->controller;
                                else
                                        $this->_controller = $this->_configuration->default->controller;
                        }
                        if (isset($route->action))
                                $this->_action = $route->action;
                        else
                                $this->_action = $this->_configuration->default->action;
                        return true;
                }

                public function url($url) {
                        return 'http://' . $this->_configuration->server . $this->_configuration->baseRewrite . $url;
                }
        }
}