<?php
/**
 * @author      Jean-Baptiste Verrey<jeanbaptiste.verrey@gmail.com>
 * @copyright   2011 Jean-Baptiste Verrey
 * @license     see license.txt
 * @since       0.1
 */
namespace CRUDsader\Map {
    /**
     * extract the map to a datasource (e.g. create all the tables of a database)
     * @abstract
     * @package    CRUDsader\Map
     */
    abstract class Extractor extends \CRUDsader\MetaClass{
        /**
         * create the tables mapped
         * @abstract
         * @return true|array array of errors
         */
        abstract public function extract(array $map);
    }
    class ExtractorException extends \CRUDsader\Exception {
        
    }
}