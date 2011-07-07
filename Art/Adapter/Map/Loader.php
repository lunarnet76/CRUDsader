<?php
/**
 * LICENSE: see Art/license.txt
 *
 * @author      Jean-Baptiste Verrey<jeanbaptiste.verrey@gmail.com>
 * @copyright   2011 Jean-Baptiste Verrey
 * @license     http://www.Art.com/license/1.txt
 * @version     $Id$
 * @link        http://www.Art.com/manual/
 * @since       1.0
 */
namespace Art\Adapter\Map {
    /**
     * load the mapping schema from a ressource, all the options are be in the configuration
     * @abstract
     * @package    Art\Adapter\Map
     */
    abstract class Loader extends \Art\Adapter{
        /**
         * return true if resource is validated or array of error otherwise
         * @abstract
         * @return true|array array of errors
         */
        abstract public function validate();
        /**
         * return the mapping schema as an array
         * @param \Art\Block $defaults
         * @return array 
         */
        abstract public function getSchema(\Art\Block $block=null);
    }
    class LoaderException extends \Art\Exception {
        
    }
}