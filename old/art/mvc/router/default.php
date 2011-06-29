<?php

/**
 * default router routes to :
 * module
 *  module/controller
 *  module/controller/action
 *  controller
 *  controller/action
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
class Art_Mvc_Router_Default extends Art_Mvc_Router_Abstract {
    public function route() {
        // identify URI
        $pos=strpos($_SERVER['REQUEST_URI'],'?');
        $this->_uri = $uri = $pos!==false?substr($_SERVER['REQUEST_URI'],0,$pos):$_SERVER['REQUEST_URI']; // /core2/techdata/controller1/controller2/action/with/1/2/3/4
     
        if (strrpos($uri, '/') === false)
            $uri.='/';
        // separate request from params
        $positionParams = strpos($uri, $this->_configuration->router->separator->parameter);
        $lengthPositionParams = strlen($this->_configuration->router->separator->parameter);
        $lengthPath = strlen($this->_configuration->path) + 1;
     
        if ($positionParams !== false) {
            $controllerUri = substr($uri, $lengthPath,$positionParams-$lengthPath); //  techdata/controller1/controller2/action/with/1/2/3/4
            $this->_params=$params = substr($uri, $positionParams + $lengthPositionParams + 1);
            // identify PARAMS
            $params =$this->parseParams($params);
            foreach($params as $name=>$value)
                $_REQUEST[$name]=$_POST[$name]=$value;
        }else
            $controllerUri=substr($uri, $lengthPath);
          
        $lengthControllerUri = strlen($controllerUri);
        if (strrpos($controllerUri, $this->_configuration->router->separator->controller) === $lengthControllerUri - 1)
            $controllerUri = substr($controllerUri, 0, $lengthControllerUri - 1);
        // route path
        $path = str_replace($this->_configuration->router->separator->controller, '/', $controllerUri);
        
        $this->_module = $this->_configuration->default->module;
        $this->_controller = $this->_configuration->default->controller;
        $this->_action = $this->_configuration->default->action;

        $folder=$this->_configuration->folder;

        if (!empty($path)) {
            // explode in controller/actions
            $explode = explode($this->_configuration->router->separator->controller, $path);
           
            // identify MODULE
            if (isset($this->_configuration->module->{$explode[0]}) && isset($this->_configuration->module->{$explode[0]}->activate) && $this->_configuration->module->{$explode[0]}->activate) {
                $this->_module = $explode[0];
                $sl = strlen($this->_module);
                $path = substr($path, $sl + (isset($path[$sl]) && $path[$sl] == $this->_configuration->router->separator->controller ? 1 : 0));
            }
            
            // identify  CONTROLLER + ACTION
            if (!empty($path)) {
                $controller = str_replace($this->_configuration->router->separator->controller, '_', $path);
                if (is_file($folder.$this->_module.'/controller/' . str_replace('_','/',$controller).'.php')) {
                    $this->_controller = $controller;
                } else if (false !== $pos = strrpos($controller, '_')) {
                    $file=$folder.$this->_module.'/controller/' . str_replace('_','/',substr($controller, 0, $pos).'.php');
                    if (is_file($file)) {
                        $this->_action = substr($controller, $pos + 1);
                        $this->_controller = substr($controller, 0, $pos);
                    } else {
                        throw new Art_Mvc_Router_Exception('URL not found, invalid path "' . 'Controller_' . $controller.'" supposedly linking to "'.$file.'"');
                    }
                }else
                    throw new Art_Mvc_Router_Exception('URL not found, path does not lead to anything ' . 'Controller_' . $controller);
                $this->_controller =str_replace('_','/',$this->_controller);
            }
        }
    }
}
?>