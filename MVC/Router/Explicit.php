<?php
/**
 * @author      Jean-Baptiste Verrey<jeanbaptiste.verrey@gmail.com>
 * @copyright   2011 Jean-Baptiste Verrey
 * @license     see license.txt
 * @since       0.1
 */
namespace CRUDsader\MVC\Router {
    /**
     * route like 
     * ${server.baseRewrite}/$language/$moduleControllerName/$function$suffix?$params
     * ${server.baseRewrite}/$language/$function$suffix?$params
     * ${server.baseRewrite}/$function$suffix?$params
     * @package CRUDsader\MVC\Router
     */
    class Explicit extends \CRUDsader\MVC\Router {

        /**
         * calculate a route
         * @param string $uri
         * @return bool wether it was found or not 
         */
        function route($uri) {
            if(PHP_SAPI=='cli'){
                $this->_route = $uri;
            }else{
                $st = strlen($this->_configuration->route->suffix);
                $real = $st ? substr($uri, strlen($this->_configuration->baseRewrite), -$st) : substr($uri, strlen($this->_configuration->baseRewrite));
                $this->_route = $real;
            }
            $routes = $this->_configuration->routes;
            $ex = explode($this->_configuration->route->separator, $this->_route );
            $this->_params=!empty($_REQUEST)?$_REQUEST:array();
            // find controller
            if (isset($routes->{$ex[0]})) {
                $language = false;
                $route = $routes->{$ex[0]};
                $action = isset($ex[1]) ? $ex[1] : false;
            } else if (isset($ex[1]) && isset($routes->{$ex[1]})) {
                $language = $ex[0];
                $route = $routes->{$ex[1]};
                $action = isset($ex[2]) ? $ex[2] : false;
            } else {
                $language = false;
                $route = false;
                $action = false;
            }
            if (!$route) {
                $this->_module = !empty($this->_configuration->default->module)?$this->_configuration->default->module:false;
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
                $this->_action = $action ? $action : $this->_configuration->default->action;
            return true;
        }

        public function url($url) {
            return 'http://' . $this->_configuration->server . $this->_configuration->baseRewrite . $url;
        }
    }
}