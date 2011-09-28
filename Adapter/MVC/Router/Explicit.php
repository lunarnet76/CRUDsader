<?php
namespace CRUDsader\Adapter\MVC\Router {
    class Explicit extends \CRUDsader\Adapter\MVC\Router {

        function route($uri) {
            $real = substr($uri, strlen($this->_configuration->baseRewrite),-strlen($this->_configuration->route->suffix));
            $routes = $this->_configuration->routes;
            $this->_route=$real;
            if (!isset($routes->$real)){
                // take default route
                $this->_module = $this->_configuration->default->module;
                $this->_controller = $this->_configuration->default->controller;
                return true;
            }
            if (isset($routes->$real->module))
                $this->_module = $routes->$real->module;
            if (isset($routes->$real->controller))
                $this->_controller = $routes->$real->controller;
            if (isset($routes->$real->action))
                $this->_action = $routes->$real->action;
            return true;
        }
    }
}