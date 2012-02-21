<?php
/**
 * @author     Jean-Baptiste Verrey<jeanbaptiste.verrey@gmail.com>
 * @copyright  2011 Jean-Baptiste Verrey
 * @license    see CRUDsader/license.txt
 */
namespace CRUDsader {
    /**
     * by inheriting this class the class has the possibility to automatically load a configuration and its dependencies, and display only wanted fields when using toArray
     * @abstract 
     * @test MetaClass_Test
     */
    abstract class MetaClass implements Interfaces\Configurable, Interfaces\Dependent, Interfaces\Arrayable {
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

        /**
         *@test test_autoconfiguration 
         */
        public function __construct() {
            foreach ($this->_hasDependencies as $dependencyName)
                $this->setDependency($dependencyName, ($this->_classIndex ? $this->_classIndex . '.' : '') . $dependencyName);
            if ($this->_classIndex)
                $this->setConfiguration(\CRUDsader\Instancer::getInstance()->configuration->{$this->_classIndex});
        }

        /**
         * @param \CRUDsader\Block $block 
         * @test test_configuration
         */
        public function setConfiguration(\CRUDsader\Block $block = null) {
            $this->_configuration = $block;
        }

        /**
         * @return \CRUDsader\Block 
         * @test test_configuration
         */
        public function getConfiguration() {
            return $this->_configuration;
        }

        /**
         * @param string $index
         * @param string $instancerIndex 
         * @test_dependencies
         */
        public function setDependency($index, $instancerIndex) {
            $this->_dependencies[$index] = \CRUDsader\Instancer::getInstance()->$instancerIndex;
        }

        /**
         * @param string $index
         * @return object 
         * @test_dependencies
         */
        public function getDependency($index) {
            return $this->_dependencies[$index];
        }

        /**
         * @param string $index
         * @return boolean 
         * @test_dependencies
         */
        public function hasDependency($index) {
            return isset($this->_dependencies[$index]);
        }

        /**
         * return object as an array
         * @param bool $maxInfo wether to show a quick view or the whole object
         * @test test_toArray
         */
        public function toArray($maxInfo = false) {
            $ret = array();
            foreach ($this->_toArray as $field) {
                $ret[$field] = isset($this->$field) ? $this->$field : null;
            }
            return $ret;
        }
    }
    class MetaClassException extends \CRUDsader\Exception {
        
    }
}
