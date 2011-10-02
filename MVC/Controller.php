<?php
namespace CRUDsader\MVC {
    abstract class Controller extends \CRUDsader\Singleton implements \CRUDsader\Interfaces\Configurable {
        protected $_frontController = NULL;
        protected $_views = array();
        protected $_metas = array();
        protected $_headers = array();
        protected $_title = '';
        protected $_preLoads = array();
        protected $_noRender = false;
        protected $_template;

        // ** CONSTRUCTOR **/
        public function init() {
            $this->_frontController = \CRUDsader\MVC\Controller\Front::getInstance();
        }

        public function setRouter(\CRUDsader\Adapter\MVC\Router $router) {
            $this->_router = $router;
            $this->_views['base'] = array('controller' => $router->getController(), 'action' => $router->getAction());
        }
        /** CONFIGURATION * */

        /**
         * @param \CRUDsader\Block $configuration
         */
        public function setConfiguration(\CRUDsader\Block $configuration=null) {
            $this->_configuration = $configuration;
        }

        /**
         * @return \CRUDsader\Block
         */
        public function getConfiguration() {
            return $this->_configuration;
        }

        /** ACTIONS * */
        public function defaultAction() {
            
        }

        /** HELPERS * */
        public function __get($var) {
            switch (true) {
                case $this->_frontController->moduleHasPlugin($var) === true:
                    return $this->_frontController->moduleGetPlugin($var);
                    break;
            }
        }

        public function __call($name,$arguments) {
            return call_user_func_array(array($this->_frontController, $name), $arguments);
        }

        public function image($options=array()) {
            if (!is_array($options))
                $options = array('file' => $options);
            $options['module'] = $this->_router->getModule();
            $options['baseRef'] = $this->getURL();
            return new \CRUDsader\MVC\Image($options);
        }

        /** INFOS * */
        public function getControllerURL() {
            return $this->_frontController->getURL() . $this->_router->getModule() . '/' . $this->_router->getController() . '/' . $this->_router->getAction() . $this->_router->getParams();
        }

        // helpers
        public function redirect(array $options=array()) {
            if (\CRUDsader\Debug::isActivated())
                echo '<a href="' . $this->url($options) . '">' . $this->url($options) . '</a>';
            else
                header('Location: ' . $this->link($options));
            exit;
        }

        // accessor
        public function setMeta($type, $value) {
            $this->_metas[$type] = $value;
        }

        public function getMeta($type) {
            return $this->_metas[$type];
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

        public function linkFiles(array $linkFiles) {
            $output = '';
            if (!empty($linkFiles['js']))
                foreach ($linkFiles['js'] as $file) {
                    if (is_array($file)) {
                        $folder = $file[1];
                        $file = $file[0];
                    }else
                        $folder = false;
                    $output.='<script type="text/javascript" src="' . $this->_frontController->getURL() . ($folder == 'module' ? $this->_frontController->getApplicationPath() . $this->_router->getModule() . '/js/' : 'lib/' . $folder) . $file . '.js"></script>';
                }
            if (!empty($linkFiles['css']))
                foreach ($linkFiles['css'] as $file) {
                    if (is_array($file)) {
                        $folder = $file[1];
                        $file = $file[0];
                    }else
                        $folder = false;
                    $output.='<link rel="stylesheet" type="text/css" href="' . $this->_frontController->getURL() . 'file/' .($folder == 'module' ? $this->_frontController->getApplicationPath() . $this->_router->getModule() . '/css/' : $folder) . $file . '.css"/>';
                }
            return $output;
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

        // ** RENDERING VIEW **/
        public function render() {
            if ($this->_noRender)
                return;
            if (!$this->_isRendered)
                $this->_isRendered = true;
            $router = $this->_router;
            $suffix = $this->_configuration->view->suffix;
            $applicationPath = $this->_frontController->getApplicationPath();
            foreach ($this->_views as $infos) {
                switch (true) {
                    case file_exists($applicationPath . $router->getModule() . '/View/' . ($infos['controller'] ? str_replace('\\', '/', $infos['controller']) . '/' : '') . $infos['action'] . '.' . $suffix):
                        $path = $applicationPath . $router->getModule() . '/View/' . ($infos['controller'] ? str_replace('\\', '/', $infos['controller']) . '/' : '') . $infos['action'] . '.' . $suffix;
                        break;
                    default:
                        $path = $this->_frontController->getApplicationPath() . $router->getModule() . '/view/default.' . $suffix;
                }
                require($path);
            }
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
                $file = $this->_frontController->getApplicationPath() . $this->_router->getModule() . '/view/template/' . $this->_template . '.' . $this->_configuration->view->suffix;
                $path = file_exists($file) ? $file : $this->_frontController->getApplicationPath() . $this->_router->getModule() . '/view/template/' . $this->_template . '.' . $this->_configuration->view->suffix;
                require($path);
            }else
                $this->render();
            $this->postRender();
        }

        /**
         * @todo remove?
         * @param <type> $action
         */
        public function setRender($action, $controller=false) {
            $this->_action = $action;
            if ($controller)
                $this->_controller = $controller;
            $this->_views['base'] = array('controller' => $controller, 'action' => $action);
        }

        public function renderPart($action, $controller=false) {
            $infos = array('action' => $action, 'controller' => $controller);
            $path = file_exists($this->_frontController->getModuleDirectory() . $this->_frontController->getModule() . '/view/' . ($infos['controller'] ? str_replace('_', '/', $infos['controller']) . '/' : '') . $infos['action'] . $suffix) ? $this->_frontController->getModuleDirectory() . $this->_frontController->getModule() . '/view/' . ($infos['controller'] ? str_replace('_', '/', $infos['controller']) . '/' : '') . $infos['action'] . $suffix : 'module/' . $this->_frontController->getModule() . '/view/default' . $suffix;
            ob_start();
            require($path);
            return ob_get_clean();
        }

        public function addView($action, $controller=false) {
            $this->_views[] = array('controller' => $controller ? $controller : false, 'action' => $action);
        }
    }
    class ControllerException extends \CRUDsader\Exception {
        
    }
}