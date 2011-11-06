<?php
/**
 * @author     Jean-Baptiste Verrey<jeanbaptiste.verrey@gmail.com>
 * @copyright  2011 Jean-Baptiste Verrey
 * @license    see CRUDsader/license.txt
 */
namespace CRUDsader {
    abstract class MetaClass implements Interfaces\Configurable, Interfaces\Dependent, Interfaces\Arrayable {
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
         * list fields to include in array
         * @var string
         */
        protected $_toArray = array();

        /**
         * the list of dependencies
         * @var array
         */
        protected $_hasDependencies = array();

        public function __construct() {
            $this->_instancer = \CRUDsader\Instancer::getInstance();
            foreach ($this->_hasDependencies as $dependencyName)
                $this->setDependency($dependencyName, ($this->_classIndex ? $this->_classIndex . '.' : '') . $dependencyName);
            if($this->_classIndex)
                $this->setConfiguration($this->_instancer->configuration->{$this->_classIndex});
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
        
        /**
         * return this as an array
         * @param bool $full
         */
        public function toArray($full=false){
            $ret=array();
            foreach($this->_toArray as $field){
                $ret[$field]=isset($this->$field)?$this->$field:null;
            }
            return $ret;
        }
    }
    class MetaClassException extends \CRUDsader\Exception {
        
    }
}
