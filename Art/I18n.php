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
         * return the translation of the index
         * @param string $index
         * @return string
         */
        public function translate($index) {
            return $this->_adapters['translation']->translate($index);
        }
    }
}