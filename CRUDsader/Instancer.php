<?php
/**
 * @author     Jean-Baptiste Verrey<jeanbaptiste.verrey@gmail.com>
 * @copyright  2011 Jean-Baptiste Verrey
 * @license    see CRUDsader/license.txt
 */
namespace CRUDsader {
    class Instancer implements Interfaces\Configurable{
        
        protected $_singletonInstances=array();
        protected $_setInstances=array();
        protected $_configuration;
        
        public static function getInstance() {
            static $instance = NULL;
            if (NULL === $instance) {
                $instance = new static();
            }
            return $instance;
        }
        
        protected function __construct(){
            $this->setConfiguration(Configuration::getInstance()->instances);
        }
        
        public function setConfiguration(\CRUDsader\Block $block=null){
            $this->_configuration=$block;
        }
        
        public function getConfiguration(){
            return $this->_configuration;
        }
        
        public function __isset($serviceName) {
            return isset($this->_serviceInstances[$serviceName]);
        }
        
        public function __call($serviceName,$args) {
            if(!isset($this->_configuration->$serviceName))
                    throw new InstancerException('cannot instantiate "'.$serviceName.'"');
            // singleton
            if(isset($this->_configuration->$serviceName->singleton) && $this->_configuration->$serviceName->singleton){
                if(!isset($this->_singletonInstances[$serviceName])){
                    $rc = new \ReflectionClass($this->_configuration->$serviceName->class);
                    $this->_singletonInstances[$serviceName]=empty($args)?$rc->newInstance():$rc->newInstanceArgs($args);
                }
                $ret=$this->_singletonInstances[$serviceName];
            }else{
                // normal
                if(isset($this->_setInstances[$serviceName])){
                    $ret=$this->_setInstances[$serviceName];
                    unset($this->_setInstances[$serviceName]);
                }else{
                    $rc = new \ReflectionClass($this->_configuration->$serviceName->class);
                    $ret=$this->_singletonInstances[$serviceName]=empty($args)?$rc->newInstance():$rc->newInstanceArgs($args);
                }
            }
            return $ret;
        }
        
        public function __get($serviceName){
            return $this->__call($serviceName,array());
        }

        public function __set($serviceName, $instance) { 
            if (!is_object($instance))
                throw new InstancerException('instance must be an object');
           $this->_setInstances[$serviceName] = $instance;
        }
    }
    class InstancerException extends \Exception{}
}
