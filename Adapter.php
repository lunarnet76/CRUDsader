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