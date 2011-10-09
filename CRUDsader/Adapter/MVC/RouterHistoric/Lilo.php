<?php
/**
 * @author      Jean-Baptiste Verrey<jeanbaptiste.verrey@gmail.com>
 * @copyright   2011 Jean-Baptiste Verrey
 * @license     see license.txt
 * @since       0.1
 */
namespace CRUDsader\Adapter\Mvc\RouterHistoric {
    /**
     * Last In Last Out 
     * @package CRUDsader\Adapter\Mvc\RouterHistoric
     */
    class Lilo extends \CRUDsader\Adapter{
        protected $_session;
        protected $_controllerToSkip = array();
      
        public function init() {
            $this->_session = \CRUDsader\Session::useNamespace('CRUDsader\\Mvc\\Navigation\\Historic');
            if (!isset($this->_session->iterator))
                $this->_session->iterator = 10;
        }

        public function skipRoute($controller) {
            $this->_controllerToSkip[$controller] = true;
        }

        public function registerRoute(\CRUDsader\Adapter\MVC\Router $router) {
            if ((!isset($this->_session->{$this->_session->iterator}) || $this->_session->{$this->_session->iterator}->route != $router->getRoute()) && !isset($this->_controllerToSkip[$router->getController()])) {
                $index = ++$this->_session->iterator;
                if ($index == 10)
                    $this->_session->iterator = 0;
                $this->_session->{$index} = array(
                    'route' => $router->getRoute(),
                    'module' => $router->getModule(),
                    'controller' => $router->getController(),
                    'action' => $router->getAction(),
                    'params' => $router->getParams()
                );
            }
        }

        public function getLast() {
            return isset($this->_session->{$this->_session->iterator}) ? $this->_session->{$this->_session->iterator} : false;
        }

        public function toArray() {
            return $this->_session->toArray();
        }

        public function reset() {
            $this->_session->reset();
            $this->_session->iterator = 0;
        }
    }
}