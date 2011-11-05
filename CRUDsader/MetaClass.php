<?php
/**
 * @author     Jean-Baptiste Verrey<jeanbaptiste.verrey@gmail.com>
 * @copyright  2011 Jean-Baptiste Verrey
 * @license    see CRUDsader/license.txt
 */
namespace CRUDsader {
    abstract class MetaClass implements Interfaces\Configurable, Interfaces\Dependent {
        /**
         * @var Instancer
         */
        protected $_instancer;

        /**
         * @var Block
         */
        protected $_configuration;

        /**
         * the instanced dependencies
         * @var array
         */
        protected $_dependencies = array();
        
        /**
         * identify the class
         * @var string
         */
        protected $_classIndex = false;

        /**
         * the list of dependencies
         * @var array
         */
        protected $_hasDependencies = array();

        public function __construct() {
            $this->_instancer = \CRUDsader\Instancer::getInstance();
            if($this->_classIndex){
                $this->setConfiguration($this->_instancer->configuration->{$this->_classIndex});
            }
            foreach ($this->_hasDependencies as $dependencyName)
                $this->setDependency($dependencyName, ($this->_classIndex ? $this->_classIndex . '.' : '') . $dependencyName);
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

        /**
         * @param string $index
         * @param string $instancerIndex 
         */
        public function setDependency($index, $instancerIndex) {
            $this->_dependencies[$index] = \CRUDsader\Instancer::getInstance()->$instancerIndex;
        }

        /**
         * @param string $index
         * @return object 
         */
        public function getDependency($index) {
            return $this->_dependencies[$index];
        }

        /**
         * @param string $index
         * @return boolean 
         */
        public function hasDependency($index) {
            return isset($this->_dependencies[$index]);
        }
    }
    class MetaClassException extends \CRUDsader\Exception {
        
    }
}
