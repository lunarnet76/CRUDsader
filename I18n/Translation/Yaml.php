<?php
/**
 * @author      Jean-Baptiste Verrey<jeanbaptiste.verrey@gmail.com>
 * @copyright   2011 Jean-Baptiste Verrey
 * @license     see license.txt
 * @since       0.1
 */
namespace CRUDsader\I18n\Translation {
    /**
     * 
     * @package CRUDsader\I18n\Translation
     */
    class Yaml extends \CRUDsader\I18n\Translation {
        /**
         * identify the class
         * @var string
         */
        protected $_classIndex = 'i18n.translation';

        /**
         * the list of dependencies
         * @var array
         */
        protected $_hasDependencies = array('arrayLoader');
        
        protected $_translations;
        

        public function __construct() {
            parent::__construct();
            $this->_translations = $this->_dependencies['arrayLoader']->load(array('file' => $this->_configuration->file, 'section' => 'default'));
        }

        public function toArray() {
            return $this->_translations;
        }

        public function translate($index) {
            $ret = $this->_getPath($index);
            return $ret ? $ret : '{' . $index . '}';
        }

        protected function _getPath($path, &$where=null, $pathIsExploded=false, $partIndex=0) {
            if ($pathIsExploded === false) {
                $path = explode('.', $path);
                $partOfPath = $path[0];
            } else {
                if (!isset($path[$partIndex]))
                    return false;
                $partOfPath = $path[$partIndex];
            }
            if ($where === null)
                $where = $this->_translations;
            return isset($where[$partOfPath]) ? (
                    is_array($where[$partOfPath]) ?
                            $this->_getPath($path, $where[$partOfPath], true, $partIndex + 1) : $where[$partOfPath]
                    ) : false;
        }
    }
}
