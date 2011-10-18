<?php
/**
 * @author      Jean-Baptiste Verrey<jeanbaptiste.verrey@gmail.com>
 * @copyright   2011 Jean-Baptiste Verrey
 * @license     see license.txt
 * @since       0.1
 */
namespace CRUDsader\Adapter\I18n\Translation {
    /**
     * do basically nothing
     * @package CRUDsader\Adapter\I18n\Translation
     */
    class Yaml extends \CRUDsader\Adapter {

        public function init() {
            $adapterActual = \CRUDsader\Configuration::getInstance()->adapter->arrayLoader;
            \CRUDsader\Configuration::getInstance()->adapter->arrayLoader = 'yaml';
            $al = \CRUDsader\Adapter::factory('arrayLoader');
            $this->_translations= $al->load(array('file' => $this->_configuration->file));
            \CRUDsader\Configuration::getInstance()->adapter->arrayLoader = $adapterActual;
            $this->_translations=$this->_translations['default'];
        }

        public function translate($index, $glue=',') {
            if(is_array($index))throw new Exception();
            else{
                $ret=$this->_getPath($index);
                return $ret?$ret:'{'.$index.'}';
            }
        }
        
        protected function _getPath($path,&$where=null,$pathIsExploded=false,$partIndex=0){
            if($pathIsExploded===false){
                $path=explode('.',$path);
                $partOfPath=$path[0];
            }  else {
                if(!isset($path[$partIndex]))
                    return false;
                $partOfPath=$path[$partIndex];
            }
            if($where===null)
                $where=$this->_translations;
            return isset($where[$partOfPath])?(
                    is_array($where[$partOfPath])?
                        $this->_getPath($path,$where[$partOfPath],true,$partIndex+1)
                        :$where[$partOfPath]
                ):false;
        }
    }
}
