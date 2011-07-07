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
     * extract the map to a datasource (e.g. create all the tables of a database)
     * @abstract
     * @package    Art\Adapter\Map
     */
    abstract class Extractor extends \Art\Adapter{
        /**
         * create the tables mapped
         * @abstract
         * @return true|array array of errors
         */
        abstract public function create(array $map);
    }
    class ExtractorException extends \Art\Exception {
        
    }
}