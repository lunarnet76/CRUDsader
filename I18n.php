<?php
/**
 * LICENSE:     see Art/license.txt
 *
 * @author      Jean-Baptiste Verrey <jeanbaptiste.verrey@gmail.com>
 * @copyright   2011 Jean-Baptiste Verrey
 * @license     http://www.Art.com/license/1.txt
 * @version     $Id$
 * @link        http://www.Art.com/manual/
 * @since       1.0
 */
namespace Art {
    /**
     * Internalization and Localization
     * @package     Art
     */
    class I18n extends Singleton implements Interfaces\Adaptable {
        /**
         * @var array
         */
        protected $_adapters = array();

        /**
         * constructor
         */
        public function init() {
            $this->_adapters['translation'] = Adapter::factory(array('i18n' => 'translation'));
        }

        /**
         * @param string $name
         * @return \Art\Adapter
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
    class I18nException extends \Art\Exception{
        
    }
}