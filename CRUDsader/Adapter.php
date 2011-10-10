<?php
/**
 * @author      Jean-Baptiste Verrey<jeanbaptiste.verrey@gmail.com>
 * @copyright   2011 Jean-Baptiste Verrey
 * @license     see license.txt
 * @since       0.1
 */
namespace CRUDsader {
    /**
     * @abstract
     * @package CRUDsader
     */
    abstract class Adapter implements Interfaces\Configurable{
        /**
         * @var Block
         */
        protected $_configuration=null;
        
        /**
         * only way to get an adapter
         * @param string|array $type array(namespace=>adapter)
         * @return self 
         * @test test_factory
         */
        public static function factory($type) {
            $instanceConfiguration=Configuration::getInstance();
            if(!isset($instanceConfiguration->adapter))
                    throw new AdapterException('Adapter factory must be configured');
            $configuration = $instanceConfiguration->adapter;
            $namespace=false;
            if (is_array($type)) {
                $namespace = key($type);
                $type = current($type);
                if (!isset($configuration->{$namespace}->{$type}))
                    throw new AdapterException('adapter "' . $namespace . '.' . $type . '" does not exist');
                $parameter = $configuration->{$namespace}->{$type};
                $class = $configuration->classNameSpace . '\\' . ucfirst($namespace) . '\\' . ucfirst($type) . '\\' . ucfirst($parameter instanceof Block ? $parameter->key() : $parameter);
            }else {
                if (!isset($configuration->$type))
                    throw new AdapterException('adapter "' . $type . '" does not exist');
                $parameter = $configuration->{$type};
                $class = $configuration->classNameSpace . '\\' . ucfirst($type) . '\\' . ucfirst($parameter instanceof Block ? $parameter->key() : $parameter);
            }
            $instance = new $class($parameter instanceof Block ? $parameter->current() : null);
            if (!$instance instanceof self)
                throw new AdapterException('adapter class "' . $class . '" does not inherit from CRUDsader\Adapter');
            return $instance;
        }
        
        /**
         * instances can be initialized only by the factory
         * @final
         */
        final protected function __construct(Block $configuration=null){
            $this->_configuration = $configuration;
            $this->init();
        }
        
        /**
         * constructor for children
         */
        public function init(){}
        
        /**
         * @param Block $configuration
         */
         public function setConfiguration(Block $configuration=null) {
            $this->_configuration = $configuration;
        }

        /**
         * @return Block
         */
        public function getConfiguration() {
            return $this->_configuration;
        }
    }
    class AdapterException extends \CRUDsader\Exception  {
        
    }
}