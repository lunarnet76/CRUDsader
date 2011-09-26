<?php
/**
 * LICENSE: see CRUDsader/license.txt
 *
 * @author      Jean-Baptiste Verrey<jeanbaptiste.verrey@gmail.com>
 * @copyright   2011 Jean-Baptiste Verrey
 * @license     http://www.CRUDsader.com/license/1.txt
 * @version     $Id$
 * @link        http://www.CRUDsader.com/manual/
 * @since       1.0
 */
namespace CRUDsader\Adapter\Map {
    /**
     * extract the map to a datasource (e.g. create all the tables of a database)
     * @abstract
     * @package    CRUDsader\Adapter\Map
     */
    abstract class Extractor extends \CRUDsader\Adapter{
        /**
         * create the tables mapped
         * @abstract
         * @return true|array array of errors
         */
        abstract public function create(array $map);
    }
    class ExtractorException extends \CRUDsader\Exception {
        
    }
}