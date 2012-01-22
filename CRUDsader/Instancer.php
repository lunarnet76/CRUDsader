<?php
/**
 * @author     Jean-Baptiste Verrey<jeanbaptiste.verrey@gmail.com>
 * @copyright  2011 Jean-Baptiste Verrey
 * @license    see CRUDsader/license.txt
 */
namespace CRUDsader {
    /**
     * @package CRUDsader
     */
    class Instancer implements Interfaces\Configurable{
        /**
         * the configuration class name
         * @staticvar string
         */
        public static $CONFIGURATION_CLASS='\\CRUDsader\\Configuration';
        /**
         * wether the configuraiton class should be a singleton
         * @staticvar boolean 
         */
        public static $CONFIGURATION_SINGLETON=true;
        /**
         * if true then instancer will load directly this configuration when first instanced
         * @staticvar boolean 
         */
        public static $CONFIGURATION_AUTOLOAD=true;
        /**
         * @access protected
         * @var array 
         */
        protected $_singletonInstances=array();
        /**
         * @access protected
         * @var array 
         */
        protected $_setInstances=array();
        
        /**
         * @staticvar self 
         * @access protected
         */
        protected static $_instance;
        /**
         * @var Block
         */
        protected $_configuration;
        /**
         * pseudo singleton
         * @return self 
         */
        public static function getInstance() {
            if (NULL === self::$_instance)
                self::$_instance = new static();
            return self::$_instance;
        }
        
        /**
         * will set configuration automatically if using $autoload
         * @param string $configurationClass
         * @param boolean $autoload 
         */
        protected function __construct() {
            $block=new Block(array('configuration'=>array('class'=>self::$CONFIGURATION_CLASS,'singleton'=>self::$CONFIGURATION_SINGLETON)));
            $this->setConfiguration($block);
            if(self::$CONFIGURATION_AUTOLOAD)
                $this->setConfiguration($this->instance('configuration')->instances);
        }
        
        public function __isset($serviceName) {
            return isset($this->_serviceInstances[$serviceName]);
        }
        
        public function instance($serviceName,$args=null){
            if(!isset($this->_configuration->$serviceName))
                    throw new InstancerException('cannot instantiate "'.$serviceName.'"');
            if(!empty($args) && !is_array($args))
                        $args = array($args);
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
            return $this->instance($serviceName,null);
        }
        
        
        public function __call($serviceName,$args) {
             return $this->instance($serviceName,$args);
        }
        

        public function __set($serviceName, $instance) { 
            if (!is_object($instance))
                throw new InstancerException('instance must be an object');
           $this->_setInstances[$serviceName] = $instance;
        }
        
         /**
         * @param \CRUDsader\Block $block 
         */
        public function setConfiguration(\CRUDsader\Block $block=null) {
            $this->_configuration = $block;
        }

        /**
         * @return \CRUDsader\Block 
         */
        public function getConfiguration() {
            return $this->_configuration;
        }
    }
    class InstancerException extends \Exception{}
}
