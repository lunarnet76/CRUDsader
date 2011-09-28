<?php
/**
 * LICENSE:     see CRUDsader/license.txt
 *
 * @author      Jean-Baptiste Verrey <jeanbaptiste.verrey@gmail.com>
 * @copyright   2011 Jean-Baptiste Verrey
 * @license     http://www.CRUDsader.com/license/1.txt
 * @version     $Id$
 * @link        http://www.CRUDsader.com/manual/
 * @since       1.0
 */
namespace CRUDsader {
    /**
     * Internalization and Localization
     * @package     CRUDsader
     */
    class I18n extends Singleton implements Interfaces\Adaptable,  Interfaces\Configurable {
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
        public function init() {
            $this->_adapters['translation'] = Adapter::factory(array('i18n' => 'translation'));
            $this->_configuration=\CRUDsader\Configuration::getInstance()->i18n;
            date_default_timezone_set($this->_configuration->timezone);
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