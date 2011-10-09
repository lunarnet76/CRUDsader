<?php
/**
 * @author      Jean-Baptiste Verrey<jeanbaptiste.verrey@gmail.com>
 * @copyright   2011 Jean-Baptiste Verrey
 * @license     see license.txt
 * @since       0.1
 */
namespace CRUDsader\Adapter\Map {
    /**
     * load the mapping schema from a ressource, all the options are be in the configuration
     * @abstract
     * @package    CRUDsader\Adapter\Map
     */
    abstract class Loader extends \CRUDsader\Adapter{
        /**
         * return true if resource is validated or array of error otherwise
         * @abstract
         * @param \CRUDsader\Block $defaults
         * @return true|array array of errors
         */
        abstract public function validate(\CRUDsader\Block $block=null);
        /**
         * return the mapping schema as an array
         * @param \CRUDsader\Block $defaults
         * @return array 
         */
        abstract public function getSchema(\CRUDsader\Block $block=null);
    }
    class LoaderException extends \CRUDsader\Exception {
        
    }
}