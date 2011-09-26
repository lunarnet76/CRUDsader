<?php
/**
 * LICENSE: see CRUDsader/license.txt
 *
 * @author     Jean-Baptiste Verrey<jeanbaptiste.verrey@gmail.com>
 * @copyright   2011 Jean-Baptiste Verrey
 * @license     http://www.CRUDsader.com/license/2.txt
 * @version     $Id$
 * @link        http://www.CRUDsader.com/manual/
 * @since       1.0
 */
namespace CRUDsader {
    /**
     * @category   Adapter
     * @package    CRUDsader
     * @abstract
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
         */
        public static function factory($type) {
            $configuration = Configuration::getInstance()->adapter;
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
                $class = $configuration->classNameSpace . '\\' . $type . '\\' . ucfirst($parameter instanceof Block ? $parameter->key() : $parameter);
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
         * constructor for child
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
    class AdapterException extends Exception {
        
    }
}