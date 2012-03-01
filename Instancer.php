<?php
/**
 * @author     Jean-Baptiste Verrey<jeanbaptiste.verrey@gmail.com>
 * @copyright  2011 Jean-Baptiste Verrey
 * @license    see CRUDsader/license.txt
 */
namespace CRUDsader {
    /**
     * @package CRUDsader
     * @test Instancer_Test
     */
    class Instancer implements Interfaces\Configurable {
        /**
         * the configuration class name
         * @staticvar string
         */
        public static $CONFIGURATION_AUTOLOAD = '\\CRUDsader\\Configuration';

        /**
         * @access protected
         * @var array 
         */
        protected $_singletonInstances = array();

        /**
         * @access protected
         * @var string
         */
        protected $_configurationIndex = 'instances';

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
         * @test test_getInstance
         */
        public static function getInstance() {
            if (NULL === self::$_instance)
                self::$_instance = new static();
            return self::$_instance;
        }

        /**
         * will set configuration automatically if \CRUDsader\Instancer::$CONFIGURATION_AUTOLOAD = true (true by default)
         * @param boolean $autoload 
         */
        protected function __construct() {
            if (self::$CONFIGURATION_AUTOLOAD) {
                $this->setConfiguration(new Block(array('configuration' => array('class' => self::$CONFIGURATION_AUTOLOAD, 'singleton' => true))));
                $this->setConfiguration($this->instance('configuration')->{$this->_configurationIndex});
            }
        }

        /**
         * return an instance of an object given it's service name
         * @param string $serviceName
         * @param array $args
         * @return mix
         * @throws InstancerException if service not found
         * @test test_instance
         */
        public function instance($serviceName, $args = null) {
            if (!isset($this->_configuration->$serviceName)){
                throw new InstancerException('cannot instantiate "' . $serviceName . '"');
	    }
            // singleton
            if (isset($this->_configuration->$serviceName->singleton) && $this->_configuration->$serviceName->singleton) {
                if (!isset($this->_singletonInstances[$serviceName])) {
                    $rc = new \ReflectionClass($this->_configuration->$serviceName->class);
                    $this->_singletonInstances[$serviceName] = empty($args) ? $rc->newInstance() : $rc->newInstanceArgs($args);
                }
                $ret = $this->_singletonInstances[$serviceName];
                // normal
            } else {
                $rc = new \ReflectionClass($this->_configuration->$serviceName->class);
                $ret = $this->_singletonInstances[$serviceName] = empty($args) ? $rc->newInstance() : $rc->newInstanceArgs($args);
            }
            return $ret;
        }

        // INSTANCIATING
        /**
         * return an instance without passing any arg
         * @param string $serviceName
         * @return mix 
         * @test test_instance
         */
        public function __get($serviceName) {
            return $this->instance($serviceName, null);
        }

        /**
         * return an instance and pass args
         * @param string $serviceName
         * @param mix $args
         * @return mix 
         * @test test_instance
         */
        public function __call($serviceName, $args) {
            return $this->instance($serviceName, $args);
        }

        /**
         * 
         * @param string $serviceName
         * @param string $function
         * @param array $arguments
         * @return mix 
         * @test test_callStatic
         */
        public static function call($serviceName, $function, $arguments) {
            return call_user_func_array(array(self::getInstance()->_configuration->$serviceName->class, $function),$arguments);
        }

        /**
         * @param \CRUDsader\Block $block 
         * @test test_setConfiguration
         */
        public function setConfiguration(\CRUDsader\Block $block = null) {
            $this->_configuration = $block;
        }

        /**
         * @return \CRUDsader\Block 
         * @test test_setConfiguration
         */
        public function getConfiguration() {
            return $this->_configuration;
        }
    }
    class InstancerException extends \Exception {
        
    }
}
