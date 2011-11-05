<?php
/**
 * @author      Jean-Baptiste Verrey<jeanbaptiste.verrey@gmail.com>
 * @copyright   2011 Jean-Baptiste Verrey
 * @license     see license.txt
 * @since       0.1
 */
namespace CRUDsader\Object {
    /**
     * return a unique Object identifier
     * @package CRUDsader\Object
     */
    abstract class Identifier extends \CRUDsader\MetaClass{
        /**
         * return a unique OID
         */
        abstract public function getOID($classInfos);
    }
}
