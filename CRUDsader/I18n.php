<?php
/**
 * @author      Jean-Baptiste Verrey<jeanbaptiste.verrey@gmail.com>
 * @copyright   2011 Jean-Baptiste Verrey
 * @license     see license.txt
 * @since       0.1
 */
namespace CRUDsader {
    /**
     * Internalization and Localization
     * @package     CRUDsader
     */
    class I18n implements Interfaces\Adaptable,  Interfaces\Configurable {
        /**
         * @var \CRUDsader\Block
         */
        protected $_configuration ;
        /**
         * @var array
         */
        protected $_adapters = array();

        /**
         * constructor
         */
        public function __construct() {
            $this->setConfiguration(\CRUDsader\Instancer::getInstance()->configuration->i18n);
        }
        
        /**
         * @param Block $configuration
         */
         public function setConfiguration(Block $configuration=null) {
            $this->_configuration = $configuration;
            date_default_timezone_set($configuration->timezone);
            $this->_adapters['translation'] = \CRUDsader\Instancer::getInstance()->{'i18n.translation'};
            $this->_adapters['translation']->setConfiguration($configuration->translation);
        }

        /**
         * @return Block
         */
        public function getConfiguration() {
            return $this->_configuration;
        }

        /**
         * @param string $name
         * @return \CRUDsader\Adapter
         */
        public function getAdapter($name=false) {
            return $this->_adapters[$name];
        }

        /**
         * @param string $name
         * @return bool
         */
        public function hasAdapter($name=false) {
            return isset($this->_adapters[$name]);
        }
        
        /**
         * @return array
         */
        public function getAdapters(){
            return $this->_adapters;
        }
      

        public function __call($name, $arguments) {
            switch($name){
                case 'translate':
                    return call_user_func_array(array($this->_adapters['translation'],$name), $arguments);
                    break;
                default:
                    throw new I18nException('call to undefined function "'.$name.'"');
            }
        }
    }
    class I18nException extends \CRUDsader\Exception{
        
    }
}