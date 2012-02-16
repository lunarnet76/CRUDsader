<?php
/**
 * @author     Jean-Baptiste Verrey<jeanbaptiste.verrey@gmail.com>
 * @copyright  2011 Jean-Baptiste Verrey
 * @license    see CRUDsader/license.txt
 */
namespace CRUDsader\I18n {
    abstract class Translation extends \CRUDsader\MetaClass implements \CRUDsader\Interfaces\Arrayable{
        /**
         * identify the class
         * @var string
         */
        protected $_classIndex = 'i18n.translation';
        
        /**
         * @abstract
         * @param string $index
         * @return string;
         */
        abstract public function translate($index);
        /**
         * @param array $indexes
         * @param string $glue
         * @return string;
         */
        public function translateArray(array $indexes,$glue){
            $ret=array();
            foreach($indexes as $index)
                $ret[]=$this->translate($index);
            return implode($glue, $ret);
        }
    }
}
