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
namespace Art\Adapter\Map\Extractor {
    /**
     * create the mapping tables
     * @abstract
     * @package    Art\Adapter\Map
     */
    class Database extends \Art\Adapter\Map\Extractor {
         public function create(array $map){
             pre($map);
             foreach($map as $className=>$classInfos){
                 
             }
         }
    }
    class DatabaseException extends \Art\Exception {
        
    }
}