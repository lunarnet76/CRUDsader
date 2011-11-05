<?php
/**
 * @author      Jean-Baptiste Verrey<jeanbaptiste.verrey@gmail.com>
 * @copyright   2011 Jean-Baptiste Verrey
 * @license     see license.txt
 * @since       0.1
 */
namespace CRUDsader{
    /**
     * create an array from a source
     * @package CRUDsader
     */
    abstract class ArrayLoader extends MetaClass{
        /**
         * @var string
         */
        protected $_configurationIndex = 'arrayLoader';
        /**
         * @abstract
         * @param array $options
         * @return array
         */
        abstract public function load(array $options);
    }
}
